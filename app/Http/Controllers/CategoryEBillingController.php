<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\Bill;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CategoryEBillingController extends Controller
{
    /** GET /monthly-bills/category-e — show the Category E monthly bill management page */
    public function index(Request $request)
    {
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $project = Project::active();

        // Bills already generated for Category E for this month
        $bills = Bill::whereHas('allottee', function ($q) {
                $q->where('category', 'E');
            })
            ->with('allottee')
            ->where('bill_month', $selectedMonth)
            ->orderBy('status')
            ->paginate(30)
            ->withQueryString();

        $billCount = Bill::whereHas('allottee', function ($q) {
                $q->where('category', 'E');
            })->where('bill_month', $selectedMonth)->count();

        $paidCount = Bill::whereHas('allottee', function ($q) {
                $q->where('category', 'E');
            })->where('bill_month', $selectedMonth)->whereIn('status', ['paid', 'settled'])->count();

        $unpaidCount = Bill::whereHas('allottee', function ($q) {
                $q->where('category', 'E');
            })->where('bill_month', $selectedMonth)->whereIn('status', ['unpaid','partial'])->count();

        $totalAmount = Bill::whereHas('allottee', function ($q) {
                $q->where('category', 'E');
            })->where('bill_month', $selectedMonth)->sum('total_amount');

        $paidAmount = Bill::whereHas('allottee', function ($q) {
                $q->where('category', 'E');
            })->where('bill_month', $selectedMonth)->sum('paid_amount');

        return view('bills.monthly_e', compact(
            'selectedMonth', 'bills', 'project',
            'billCount', 'paidCount', 'unpaidCount', 'totalAmount', 'paidAmount'
        ));
    }

    /** POST /monthly-bills/category-e/generate — generate bills for Category E allottees for a month */
    public function generate(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);
        $month = $request->month;

        // Enforce Billing Month Cap Rule (strict cap at July 2026)
        $isAdmin = (auth()->check() && auth()->user()->role === 'admin') || (bool) Setting::getValue('billing_admin_override', 0);
        if (!$isAdmin && $month > '2026-07') {
            return back()->with('error', "Billing generation beyond July 2026 is restricted.");
        }

        // Billing Cycle Control Checks
        $currentBillingMonthSetting = Setting::getValue('current_billing_month', '2026-07');
        $allowFutureBilling = (bool) Setting::getValue('allow_future_billing', 0);
        $maxMonthsAhead = (int) Setting::getValue('max_billing_months_ahead', 1);
        $billingMonthLock = (bool) Setting::getValue('billing_month_lock', 0);
        $adminOverride = (bool) Setting::getValue('billing_admin_override', 0);

        if (!$adminOverride) {
            // Check lock on current month
            if ($billingMonthLock && $month === $currentBillingMonthSetting) {
                return back()->with('error', "Billing for {$month} is locked and cannot be generated or modified.");
            }

            // Check future month restrictions
            $currentCalDate = Carbon::now()->startOfMonth();
            $requestedDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

            if ($requestedDate->gt($currentCalDate)) {
                if (!$allowFutureBilling) {
                    return back()->with('error', "Future bill generation is disabled under Settings & Criteria.");
                }
                
                $diff = $currentCalDate->diffInMonths($requestedDate);
                if ($diff > $maxMonthsAhead) {
                    return back()->with('error', "Cannot generate bills more than {$maxMonthsAhead} month(s) ahead.");
                }
            }
        }

        $rate     = (float) Setting::getValue('maintenance_rate_per_sqft', 3.07);
        $wwAmt    = (float) Setting::getValue('watch_ward_amount', 10000);
        $delayPct = (float) Setting::getValue('delay_charge_percent', 10);

        $activeProject = \App\Models\Project::active();
        if ($activeProject) {
            $rate = $activeProject->maintenance_rate;
            $wwAmt = $activeProject->ww_amount;
            $delayPct = $activeProject->delay_percent;
        }

        // Project Scope Restriction: Enforce active project ID explicitly
        if ($activeProject) {
            $allottees = Allottee::where('project_id', $activeProject->id)
                ->where('category', 'E')
                ->get();
        } else {
            $allottees = Allottee::where('category', 'E')->get();
        }

        $generated = 0;
        $skipped   = 0;

        foreach ($allottees as $allottee) {
            // Skip if bill already exists for this month
            if (Bill::where('allottee_id', $allottee->id)->where('bill_month', $month)->exists()) {
                $skipped++;
                continue;
            }

            // 1. Roll Forward: Increment due months
            $allottee->due_months += 1;

            $parkingRate = $allottee->has_parking ? ($allottee->parking_charges > 0 ? $allottee->parking_charges : (float) Setting::getValue('parking_charges_rate', 500)) : 0;
            $waterRate = $allottee->has_water ? ($allottee->water_charges > 0 ? $allottee->water_charges : (float) Setting::getValue('water_charges_rate', 1000)) : 0;
            
            $monthlyBase = ($rate * $allottee->covered_area) + $parkingRate + $waterRate;
            $maintenance = round($monthlyBase * $allottee->due_months, 2);
            $allottee->maintenance_charges = $maintenance;

            // W&W: Calculate dynamically for completed months
            $wwStartDate = Carbon::create(2023, 7, 1);
            $wwEndDate = $allottee->possession_date ? clone $allottee->possession_date : Carbon::now();
            $wwMonths = 0;
            if ($wwEndDate->gt($wwStartDate)) {
                $wwMonths = $wwStartDate->diffInMonths($wwEndDate);
            }
            $ww = $wwMonths * $wwAmt;
            
            if (!$allottee->ww_charged && $ww > 0) {
                $allottee->ww_charged = true;
                $allottee->ww_charged_date = $allottee->possession_date ?? now();
            }

            // Sync allottee.watch_ward_charges to match the generated ww snapshot
            $allottee->watch_ward_charges = $ww;

            // Calculate Fine dynamically on pending amount
            $oldFine = $allottee->fine ?? 0;
            // The pending balance before generating this month's new fine
            $pendingBeforeFine = max(0, ($maintenance + $ww + $oldFine) - $allottee->amount_paid);
            
            // We only fine the ARREARS, not the brand new current month rent.
            $currentMonthRent = round($monthlyBase, 2);
            $amountSubjectToFine = max(0, $pendingBeforeFine - $currentMonthRent);

            $newFine = 0;
            if ($amountSubjectToFine > 0) {
                $newFine = round($amountSubjectToFine * ($delayPct / 100), 2);
            }
            
            $fine = $oldFine + $newFine;
            $allottee->fine = $fine;
            $allottee->total_maintenance_charges = $maintenance + $ww + $fine;

            // 3. Create the Unified Monthly Bill (Snapshot of Account State)
            $totalCharges = round($maintenance + $ww + $fine, 2);
            $amountPaid   = round((float)$allottee->amount_paid, 2);
            $paidAmount   = min($totalCharges, $amountPaid);
            $totalDue     = max(0, $totalCharges - $amountPaid);
            $status       = $totalDue > 1.00 ? ($amountPaid > 0 ? 'partial' : 'unpaid') : 'paid';

            $blkCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $allottee->block_no ?? 'X'));
            $fltCode = str_pad(preg_replace('/[^0-9]/', '', $allottee->flat_no ?? '0'), 3, '0', STR_PAD_LEFT);
            $monCode = str_replace('-', '', $month);
            $psid = 'PHAF-E' . $blkCode . $fltCode . '-' . $monCode;

            Bill::create([
                'project_id'         => $allottee->project_id,
                'allottee_id'        => $allottee->id,
                'bill_month'         => $month,
                'psid'               => $psid,
                'maintenance_amount' => $maintenance,
                'ww_amount'          => $ww,
                'fine_amount'        => $fine,
                'total_amount'       => $totalCharges,
                'paid_amount'        => $paidAmount,
                'status'             => $status,
            ]);

            // Save the allottee AFTER the bill is created so overdue_months calculates correctly in saving hook
            $allottee->save();
            
            $generated++;
        }

        return redirect()->route('monthly-bills-e.index', ['month' => $month])
            ->with('success', "Generated {$generated} bills for Category E for {$month}. Skipped {$skipped} (already existed).");
    }

    /** POST /monthly-bills/category-e/{bill}/pay — record payment for a monthly bill */
    public function recordPayment(Request $request, Bill $bill)
    {
        $request->validate([
            'paid_amount'  => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,online,cheque,psid,waived',
            'payment_date' => 'required|date',
            'payment_ref'  => 'nullable|string|max:100',
        ]);

        $paid  = (float) $request->paid_amount;
        $oldPaid = (float) $bill->paid_amount;
        $difference = $paid - $oldPaid;

        \Illuminate\Support\Facades\DB::transaction(function() use ($bill, $paid, $request, $difference) {
            // Lock target bill
            $lockedBill = Bill::lockForUpdate()->findOrFail($bill->id);

            $lockedBill->recordPaymentAmount($paid, $request->payment_mode, $request->payment_date, $request->payment_ref);

            // Propagate payment to prior unpaid bills if fully paid
            if ($lockedBill->status === 'paid') {
                Bill::withoutGlobalScopes()
                    ->where('allottee_id', $lockedBill->allottee_id)
                    ->where('bill_month', '<', $lockedBill->bill_month)
                    ->whereNotIn('status', ['paid', 'settled'])
                    ->lockForUpdate()
                    ->get()
                    ->each(function ($b) use ($request) {
                        $b->update([
                            'status'       => 'paid',
                            'paid_amount'  => $b->total_amount,
                            'is_locked'    => true,
                            'locked_at'    => now(),
                            'payment_mode' => $request->payment_mode,
                            'payment_date' => $request->payment_date,
                            'payment_ref'  => $request->payment_ref ?? 'Cumulative Settlement',
                        ]);
                    });
            }

            // Sync with Allottee's historical backlog
            if ($difference != 0) {
                // Lock Allottee
                $lockedAllottee = Allottee::lockForUpdate()->findOrFail($lockedBill->allottee_id);
                $lockedAllottee->increment('amount_paid', $difference);
                
                if ($paid > 0) {
                    $lockedAllottee->update([
                        'payment_date' => $request->payment_date,
                        'payment_mode' => $request->payment_mode,
                        'payment_ref'  => $request->payment_ref,
                    ]);
                }
            }
        });

        return back()->with('success', 'Payment recorded for ' . $bill->allottee->name . ' — ' . $bill->bill_month_label);
    }

    /** POST /monthly-bills/category-e/{bill}/settle — admin manual settlement */
    public function settle(Request $request, Bill $bill)
    {
        $request->validate([
            'settled_note' => 'required|string|max:500',
        ]);

        $oldPaid = (float) $bill->paid_amount;
        $total = (float) $bill->total_amount;
        $difference = $total - $oldPaid;

        \Illuminate\Support\Facades\DB::transaction(function() use ($bill, $total, $request, $difference) {
            // Lock target bill
            $lockedBill = Bill::lockForUpdate()->findOrFail($bill->id);

            $lockedBill->update([
                'status'       => 'settled',
                'paid_amount'  => $total,
                'settled_by'   => auth()->user() ? auth()->user()->name : 'System',
                'settled_note' => $request->settled_note,
                'is_locked'    => true,
                'locked_at'    => now(),
            ]);

            // Propagate settlement to prior unpaid bills
            Bill::withoutGlobalScopes()
                ->where('allottee_id', $lockedBill->allottee_id)
                ->where('bill_month', '<', $lockedBill->bill_month)
                ->whereNotIn('status', ['paid', 'settled'])
                ->lockForUpdate()
                ->get()
                ->each(function ($b) use ($lockedBill, $request) {
                    $b->update([
                        'status'       => 'settled',
                        'paid_amount'  => $b->total_amount,
                        'is_locked'    => true,
                        'locked_at'    => now(),
                        'payment_mode' => 'waived/settled',
                        'payment_date' => now(),
                        'payment_ref'  => 'Admin Settlement',
                        'settled_by'   => auth()->user() ? auth()->user()->name : 'System',
                        'settled_note' => 'Manually settled via latest cumulative bill settlement. Note: ' . $request->settled_note,
                    ]);
                });

            if ($difference > 0) {
                $lockedAllottee = Allottee::lockForUpdate()->findOrFail($lockedBill->allottee_id);
                $lockedAllottee->increment('amount_paid', $difference);
                $lockedAllottee->update([
                    'payment_date' => now(),
                    'payment_mode' => 'waived/settled',
                    'payment_ref'  => 'Admin Settlement',
                ]);
            }
        });

        return back()->with('success', 'Bill manually settled for ' . $bill->allottee->name);
    }


    /** GET /monthly-bills/category-e/{bill}/check-psid — simulate PSID payment check */
    public function checkPsid(Bill $bill)
    {
        $isPaid = $bill->status === 'paid' || $bill->status === 'settled';

        return response()->json([
            'psid'    => $bill->psid,
            'status'  => $isPaid ? 'PAID' : 'PENDING',
            'message' => $isPaid
                ? 'Payment confirmed via PSID. Amount: Rs. ' . number_format($bill->total_amount)
                : 'Payment not yet received. Please pay via 1Bill or Raast using PSID: ' . $bill->psid,
            'simulated' => true,
        ]);
    }
}
