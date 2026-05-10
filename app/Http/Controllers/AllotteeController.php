<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\Setting;
use Illuminate\Http\Request;

class AllotteeController extends Controller
{
    public function index(Request $request)
    {
        $query = Allottee::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('cnic', 'like', "%$s%")
                  ->orWhere('file_no', 'like', "%$s%")
                  ->orWhere('membership_no', 'like', "%$s%")
                  ->orWhere('cell', 'like', "%$s%");
            });
        }
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('city'))     $query->where('city', $request->city);
        if ($request->filled('bps'))      $query->where('bps', $request->bps);
        if ($request->filled('defaulter') && $request->defaulter === '1') {
            $threshold = (int) Setting::getValue('defaulter_months_threshold', 3);
            $query->where('due_months', '>=', $threshold);
        }

        $allottees = $query->orderByDesc('total_maintenance_charges')->paginate(25)->withQueryString();
        $cities = Allottee::select('city')->distinct()->orderBy('city')->pluck('city');
        $bpsList = Allottee::select('bps')->distinct()->whereNotNull('bps')->orderBy('bps')->pluck('bps');

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
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'cnic' => 'nullable|string|max:255',
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
        ]);

        // Default booleans if unchecked
        $validated['has_parking'] = $request->has('has_parking');
        $validated['has_water'] = $request->has('has_water');

        $allottee->update($validated);

        return redirect()->route('allottees.show', $allottee)->with('success', 'Allottee profile updated successfully.');
    }

    public function blockVisual()
    {
        // Get all allottees with block, floor, flat info and payment status
        $allottees = Allottee::select('id','name','block_no','floor','flat_no','category','due_months',
            'total_maintenance_charges','amount_paid','status')
            ->get()
            ->map(function($a) {
                $a->payment_status_computed = $a->payment_status;
                return $a;
            });

        // Group by block → floor → flat
        $blocks = [];
        foreach ($allottees as $a) {
            $block = $a->block_no ?? 'Unknown';
            $floor = $a->floor     ?? 'GF';
            $blocks[$block][$floor][] = $a;
        }
        ksort($blocks);

        $totalDefaulters = $allottees->filter(fn($a) => $a->due_months >= 3)->count();
        $totalPaid       = $allottees->filter(fn($a) => $a->payment_status_computed === 'paid')->count();
        $totalUnpaid     = $allottees->filter(fn($a) => $a->payment_status_computed === 'unpaid')->count();

        return view('blocks.visual', compact('blocks', 'allottees', 'totalDefaulters', 'totalPaid', 'totalUnpaid'));
    }
}

