<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\Setting;
use Illuminate\Http\Request;

class AllotteeController extends Controller
{
    public function index(Request $request)
    {
        $query = Allottee::active()->with(['property', 'activeTenantRelation']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('cnic', 'like', "%$s%")
                  ->orWhere('file_no', 'like', "%$s%")
                  ->orWhere('membership_no', 'like', "%$s%")
                  ->orWhere('cell', 'like', "%$s%")
                  ->orWhereHas('property', function($pq) use ($s) {
                      $pq->where('flat_no', 'like', "%$s%");
                  })
                  ->orWhereHas('tenants', function($tq) use ($s) {
                      $tq->where('is_active', true)
                         ->where(function($sub) use ($s) {
                             $sub->where('tenant_name', 'like', "%$s%")
                                 ->orWhere('tenant_cnic', 'like', "%$s%")
                                 ->orWhere('mobile_no', 'like', "%$s%");
                         });
                  });
            });
        }
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('city'))     $query->where('city', $request->city);
        if ($request->filled('bps'))      $query->where('bps', $request->bps);
        if ($request->filled('defaulter') && $request->defaulter === '1') {
            $threshold = (int) Setting::getValue('defaulter_months_threshold', 3);
            $query->where('overdue_months', '>=', $threshold);
        }

        $allottees = $query->orderByDesc('total_maintenance_charges')->paginate(25)->withQueryString();
        $cities = Allottee::active()->select('city')->distinct()->orderBy('city')->pluck('city');
        $bpsList = Allottee::active()->select('bps')->distinct()->whereNotNull('bps')->orderBy('bps')->pluck('bps');

        return view('allottees.index', compact('allottees', 'cities', 'bpsList'));
    }

    public function show(Allottee $allottee)
    {
        return view('allottees.show', compact('allottee'));
    }

    public function edit(Allottee $allottee)
    {
        return view('allottees.edit', compact('allottee'));
    }

    public function update(Request $request, Allottee $allottee)
    {
        if ($allottee->status === 'inactive') {
            return redirect()->route('allottees.show', $allottee)->with('error', 'Historical ownership records are read-only.');
        }

        $rules = [
            'name' => 'nullable|string|max:255',
            'father_spouse_name' => 'nullable|string|max:255',
            'cnic' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'cell' => 'nullable|string|max:255',
            'mailing_address' => 'nullable|string',
            'category' => 'nullable|string|max:10',
            'block_no' => 'nullable|string|max:50',
            'flat_no' => 'nullable|string|max:50',
            'bps' => 'nullable|string|max:50',
            'possession_date' => 'nullable|date',
            'has_parking' => 'nullable|boolean',
            'has_water' => 'nullable|boolean',
            'parking_charges' => 'nullable|numeric|min:0',
            'water_charges' => 'nullable|numeric|min:0',
            'occupancy_status' => 'required|in:owner_occupied,tenant_occupied',
        ];

        // Conditional tenant validation rules
        if ($request->occupancy_status === 'tenant_occupied') {
            $rules = array_merge($rules, [
                'tenant_name' => 'required|string|max:255',
                'tenant_cnic' => 'required|string|max:50',
                'spouse_name' => 'nullable|string|max:255',
                'mobile_no' => 'required|string|max:50',
                'alternate_contact_no' => 'nullable|string|max:50',
                'agreement_no' => 'required|string|max:100',
                'agreement_date' => 'nullable|date',
                'agreement_start_date' => 'required|date',
                'agreement_expiry_date' => 'required|date',
                'duration_of_stay' => 'nullable|string|max:50',
                'monthly_rent' => 'nullable|numeric|min:0',
                'security_deposit' => 'nullable|numeric|min:0',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:50',
                'tenant_email' => 'nullable|email|max:255',
                'permanent_address' => 'nullable|string',
                'current_address' => 'nullable|string',
                'tenant_remarks' => 'nullable|string',
            ]);
        }

        $validated = $request->validate($rules);

        // Default booleans if unchecked
        $validated['has_parking'] = $request->has('has_parking');
        $validated['has_water'] = $request->has('has_water');

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $allottee, $validated) {
            // 1. Update property master if it exists
            if ($allottee->property) {
                $allottee->property->update([
                    'block_no'         => $request->block_no,
                    'flat_no'          => $request->flat_no,
                    'floor'            => $request->floor,
                    'category'         => $request->category,
                    'has_parking'      => $validated['has_parking'],
                    'has_water'        => $validated['has_water'],
                    'parking_charges'  => $request->parking_charges ?? 0.00,
                    'water_charges'    => $request->water_charges ?? 0.00,
                ]);
            }

            // 2. Update owner record fields
            $allottee->update([
                'name'               => $request->name,
                'father_spouse_name' => $request->father_spouse_name,
                'cnic'               => $request->cnic,
                'cell'               => $request->cell,
                'email'              => $request->email,
                'mailing_address'    => $request->mailing_address,
                'bps'                => $request->bps,
                'possession_date'    => $request->possession_date,
                'occupancy_status'   => $request->occupancy_status,
            ]);

            // 3. Process Tenancy updates
            if ($request->occupancy_status === 'owner_occupied') {
                // Deactivate any currently active tenant record
                if ($allottee->property) {
                    $allottee->property->tenants()->where('is_active', true)->update(['is_active' => false]);
                }
            } else {
                $activeTenant = $allottee->property ? $allottee->property->activeTenant : null;

                $tenantData = [
                    'project_id'              => $allottee->project_id,
                    'allottee_id'             => $allottee->id,
                    'property_id'             => $allottee->property_id,
                    'tenant_name'             => $request->tenant_name,
                    'tenant_cnic'             => $request->tenant_cnic,
                    'spouse_name'             => $request->spouse_name,
                    'mobile_no'               => $request->mobile_no,
                    'alternate_contact_no'    => $request->alternate_contact_no,
                    'agreement_no'            => $request->agreement_no,
                    'agreement_date'          => $request->agreement_date,
                    'agreement_start_date'    => $request->agreement_start_date,
                    'agreement_expiry_date'   => $request->agreement_expiry_date,
                    'duration_of_stay'        => $request->duration_of_stay,
                    'occupancy_date'          => $request->agreement_start_date ?? now(),
                    'is_active'               => true,
                    'remarks'                 => $request->tenant_remarks,
                    'tenant_email'            => $request->tenant_email,
                    'permanent_address'       => $request->permanent_address,
                    'current_address'         => $request->current_address,
                    'monthly_rent'            => $request->monthly_rent,
                    'security_deposit'        => $request->security_deposit,
                    'emergency_contact_name'  => $request->emergency_contact_name,
                    'emergency_contact_phone' => $request->emergency_contact_phone,
                ];

                // If CNIC or name differs from active tenant, archive active tenant and create a new record
                if ($activeTenant) {
                    if ($activeTenant->tenant_cnic !== $request->tenant_cnic || $activeTenant->tenant_name !== $request->tenant_name) {
                        $activeTenant->update(['is_active' => false]);
                        \App\Models\TenantRecord::create($tenantData);
                    } else {
                        // Update active tenant record
                        $activeTenant->update($tenantData);
                    }
                } else {
                    \App\Models\TenantRecord::create($tenantData);
                }
            }
        });

        return redirect()->route('allottees.show', $allottee)->with('success', 'Allottee profile updated successfully.');
    }

    public function transfer(Request $request, Allottee $allottee)
    {
        $request->validate([
            'new_owner_name'           => 'required|string|max:255',
            'new_owner_father_spouse'  => 'nullable|string|max:255',
            'new_owner_cnic'           => 'required|string|max:50',
            'new_owner_cell'           => 'required|string|max:50',
            'new_owner_email'          => 'nullable|email|max:255',
            'transfer_type'            => 'required|in:transfer,sale,cancellation,reallotment',
            'transfer_date'            => 'required|date',
            'effective_date'           => 'required|date',
            'transfer_approval_date'   => 'nullable|date',
            'possession_handover_date' => 'nullable|date',
            'transfer_ref_no'          => 'required|string|max:100',
            'remarks'                  => 'nullable|string',
        ]);

        $newAllottee = \Illuminate\Support\Facades\DB::transaction(function() use ($request, $allottee) {
            // Save outstanding balance BEFORE modifying the previous owner's balance stats
            $outstandingBalance = max(0.00, (float)$allottee->total_maintenance_charges - (float)$allottee->amount_paid);

            // 1. Deactivate old owner (allottee)
            $allottee->update([
                'status'             => 'inactive',
                'ownership_end_date' => $request->effective_date,
                'transfer_type'      => $request->transfer_type,
                'transfer_ref_no'    => $request->transfer_ref_no,
                'remarks'            => $request->remarks,
            ]);

            // 2. Create new active owner record
            $newAllottee = Allottee::create([
                'project_id'                 => $allottee->project_id,
                'property_id'                => $allottee->property_id,
                'name'                       => $request->new_owner_name,
                'father_spouse_name'         => $request->new_owner_father_spouse,
                'cnic'                       => $request->new_owner_cnic,
                'cell'                       => $request->new_owner_cell,
                'email'                      => $request->new_owner_email,
                'file_no'                    => $allottee->file_no,
                'membership_no'              => $allottee->membership_no,
                'ownership_start_date'       => $request->effective_date,
                'status'                     => 'active',
                'occupancy_status'           => 'owner_occupied',
                'due_months'                 => 0,
                'amount_paid'                => 0.00,
                'total_maintenance_charges'  => 0.00,
                'maintenance_charges'        => 0.00,
                'watch_ward_charges'         => 0.00,
                'fine'                       => 0.00,
                'city'                       => $allottee->city,
            ]);

            // 3. Process balance transfers if enabled
            $shouldTransfer = (bool) Setting::getValue('transfer_outstanding_balance', 1);
            $balanceStatus = $shouldTransfer ? 'transferred' : 'retained';

            if ($shouldTransfer) {
                // Reassign all unpaid or partial bills to the new owner's ID
                \App\Models\Bill::where('allottee_id', $allottee->id)
                    ->whereNotIn('status', ['paid', 'settled'])
                    ->update([
                        'allottee_id' => $newAllottee->id,
                    ]);

                // Sync current dues stats to the new owner
                $newAllottee->update([
                    'due_months'                 => $allottee->due_months,
                    'maintenance_charges'        => $allottee->maintenance_charges,
                    'watch_ward_charges'         => $allottee->watch_ward_charges,
                    'fine'                       => $allottee->fine,
                    'total_maintenance_charges'  => $allottee->total_maintenance_charges,
                    'amount_paid'                => $allottee->amount_paid,
                ]);

                // Clear dues from the previous owner record so it shows zero pending balance
                $allottee->update([
                    'due_months'                 => 0,
                    'maintenance_charges'        => 0.00,
                    'watch_ward_charges'         => 0.00,
                    'fine'                       => 0.00,
                    'total_maintenance_charges'  => 0.00,
                    'amount_paid'                => 0.00,
                ]);
            }

            // 4. Create PropertyOwnershipHistory record
            \App\Models\PropertyOwnershipHistory::create([
                'allottee_id'                     => $allottee->id,
                'property_id'                     => $allottee->property_id,
                'previous_owner_id'               => $allottee->id,
                'new_owner_id'                    => $newAllottee->id,
                'previous_owner_name'             => $allottee->name,
                'previous_owner_cnic'             => $allottee->cnic,
                'previous_owner_cell'             => $allottee->cell,
                'new_owner_name'                  => $newAllottee->name,
                'new_owner_cnic'                  => $newAllottee->cnic,
                'new_owner_cell'                  => $newAllottee->cell,
                'transfer_type'                   => $request->transfer_type,
                'transfer_date'                   => $request->transfer_date,
                'effective_date'                  => $request->effective_date,
                'transfer_ref_no'                 => $request->transfer_ref_no,
                'transfer_approval_date'          => $request->transfer_approval_date,
                'possession_handover_date'        => $request->possession_handover_date,
                'outstanding_balance_at_transfer' => $outstandingBalance,
                'balance_transfer_status'         => $balanceStatus,
                'status'                          => 'completed',
                'remarks'                         => $request->remarks,
                'created_by'                      => auth()->id(),
            ]);

            // 5. Populate standard audit_logs table
            \Illuminate\Support\Facades\DB::table('audit_logs')->insert([
                'user_id'        => auth()->id(),
                'auditable_type' => Allottee::class,
                'auditable_id'   => $allottee->id,
                'action'         => 'ownership_transfer',
                'old_values'     => json_encode([
                    'id'      => $allottee->id,
                    'name'    => $allottee->name,
                    'cnic'    => $allottee->cnic,
                    'cell'    => $allottee->cell,
                    'status'  => 'active',
                    'balance' => $outstandingBalance,
                ]),
                'new_values'     => json_encode([
                    'id'               => $newAllottee->id,
                    'name'             => $newAllottee->name,
                    'cnic'             => $newAllottee->cnic,
                    'cell'             => $newAllottee->cell,
                    'status'           => 'active',
                    'balance_transfer' => $balanceStatus,
                ]),
                'reason'         => $request->remarks,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Run integrity validation check post-transfer
            $integrityService = app(\App\Services\SystemIntegrityService::class);
            $report = $integrityService->run();
            if ($report['overall_status'] === 'CRITICAL') {
                throw new \RuntimeException("Ownership transfer aborted due to critical database integrity check failures.");
            }

            return $newAllottee;
        });

        return redirect()->route('allottees.show', $newAllottee)->with('success', 'Ownership transferred successfully to ' . $newAllottee->name);
    }

    public function blockVisual()
    {
        // Get all active allottees with block, floor, flat info and payment status
        $allottees = Allottee::active()
            ->select('id','property_id','name','block_no','floor','flat_no','category','due_months','overdue_months',
            'total_maintenance_charges','amount_paid','status', 'handed_over', 'temporary_occupancy')
            ->with('property')
            ->get()
            ->map(function($a) {
                $a->payment_status_computed = $a->payment_status;
                return $a;
            });

        // Group by category → block → floor → flat
        $categorizedBlocks = [];
        foreach ($allottees as $a) {
            $cat = $a->category ?? 'Unknown';
            $block = $a->block_no ?? 'Unknown';
            $floor = $a->floor     ?? 'GF';
            $categorizedBlocks[$cat][$block][$floor][] = $a;
        }
        
        // Sort categories (e.g. B, E)
        ksort($categorizedBlocks);
        
        // Sort blocks naturally within each category, and floors top-to-bottom
        $floorOrder = [
            'Seventh floor' => 1,
            'Sixth Floor' => 2,
            'Fifth Floor' => 3,
            'Forth Floor' => 4, // Including typo 'Forth' as it exists in DB
            'Fourth Floor' => 4,
            'Third Floor' => 5,
            'Second Floor' => 6,
            'First Floor' => 7,
            'Ground Floor' => 8,
            '26' => 9
        ];

        foreach ($categorizedBlocks as &$blocks) {
            ksort($blocks, SORT_NATURAL);
            foreach ($blocks as &$floors) {
                uksort($floors, function($a, $b) use ($floorOrder) {
                    $orderA = $floorOrder[$a] ?? 99;
                    $orderB = $floorOrder[$b] ?? 99;
                    return $orderA <=> $orderB;
                });
            }
        }

        $totalDefaulters = $allottees->filter(fn($a) => $a->overdue_months >= 3)->count();
        $totalPaid       = $allottees->filter(fn($a) => $a->payment_status_computed === 'paid')->count();
        $totalUnpaid     = $allottees->filter(fn($a) => $a->payment_status_computed === 'unpaid')->count();

        return view('blocks.visual', compact('categorizedBlocks', 'allottees', 'totalDefaulters', 'totalPaid', 'totalUnpaid'));
    }
}

