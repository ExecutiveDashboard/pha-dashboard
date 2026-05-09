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
}
