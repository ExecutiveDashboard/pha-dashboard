<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use Illuminate\Http\Request;

class AllotteePortalController extends Controller
{
    public function showLogin()
    {
        if (session('portal_allottee_id')) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'cnic' => 'required|string',
            'cell' => 'required|string',
        ]);

        $cnic = trim($request->cnic);
        $cell = trim($request->cell);

        // Match CNIC and cell (flexible match — remove dashes/spaces)
        $allottee = Allottee::where('cnic', $cnic)->first();
        if (!$allottee) {
            // Try without dashes
            $allottee = Allottee::whereRaw("REPLACE(cnic, '-', '') = ?", [str_replace('-', '', $cnic)])->first();
        }

        if (!$allottee) {
            return back()->withErrors(['cnic' => 'CNIC not found in our records.'])->withInput();
        }

        // Check cell number (flexible: last 10 digits)
        $inputCell = preg_replace('/\D/', '', $cell);
        $dbCell    = preg_replace('/\D/', '', $allottee->cell ?? '');

        if (!$dbCell || !str_ends_with($dbCell, substr($inputCell, -10))) {
            return back()->withErrors(['cell' => 'Mobile number does not match our records.'])->withInput();
        }

        session(['portal_allottee_id' => $allottee->id]);
        return redirect()->route('portal.dashboard');
    }

    public function dashboard()
    {
        $id = session('portal_allottee_id');
        if (!$id) return redirect()->route('portal.login');

        $allottee = Allottee::findOrFail($id);
        return view('portal.dashboard', compact('allottee'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portal_allottee_id');
        return redirect()->route('portal.login')->with('success', 'You have been signed out.');
    }
}
