<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\Bill;
use App\Models\Setting;
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
        $request->validate(['cnic' => 'required|string', 'cell' => 'required|string']);
        $cnic = trim($request->cnic);
        $cell = trim($request->cell);

        $allottee = Allottee::where('cnic', 'like', '%' . $cnic . '%')->first();
        if (!$allottee) {
            $allottee = Allottee::whereRaw("REPLACE(cnic, '-', '') LIKE ?", ['%' . str_replace('-', '', $cnic) . '%'])->first();
        }
        if (!$allottee) {
            return back()->withErrors(['cnic' => 'CNIC not found in our records.'])->withInput();
        }

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

        $allottee     = Allottee::findOrFail($id);
        $monthlyBills = Bill::where('allottee_id', $allottee->id)->orderByDesc('bill_month')->get();
        $hasMonthlyBills = $monthlyBills->isNotEmpty();
        $bankAccNo    = Setting::getValue('bank_account_no', 'PHA-001-NBP-001');
        $bankName     = Setting::getValue('bank_name', 'National Bank of Pakistan');
        $bankBranch   = Setting::getValue('bank_branch', 'Islamabad Main Branch');

        return view('portal.dashboard', compact(
            'allottee', 'monthlyBills', 'hasMonthlyBills',
            'bankAccNo', 'bankName', 'bankBranch'
        ));
    }

    public function viewMonthlyBill($month)
    {
        $id = session('portal_allottee_id');
        if (!$id) return redirect()->route('portal.login');

        $allottee  = Allottee::findOrFail($id);
        $bill      = Bill::where('allottee_id', $allottee->id)->where('bill_month', $month)->firstOrFail();
        $bankAccNo = Setting::getValue('bank_account_no', 'PHA-001-NBP-001');
        $bankName  = Setting::getValue('bank_name', 'National Bank of Pakistan');
        $bankBranch= Setting::getValue('bank_branch', 'Islamabad Main Branch');
        $rate      = (float) Setting::getValue('maintenance_rate_per_sqft', 3.07);

        return view('portal.bill_detail', compact('allottee', 'bill', 'bankAccNo', 'bankName', 'bankBranch', 'rate'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portal_allottee_id');
        return redirect()->route('portal.login')->with('success', 'You have been signed out.');
    }
}
