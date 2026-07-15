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

        // Calculate maximum allowed month dynamically
        $currentBillingMonthSetting = Setting::getValue('current_billing_month', '2026-07');
        $allowFutureBilling = (bool) Setting::getValue('allow_future_billing', 0);
        $maxMonthsAhead = (int) Setting::getValue('max_billing_months_ahead', 1);

        if ($allowFutureBilling) {
            $maxAllowedDate = \Carbon\Carbon::createFromFormat('Y-m', $currentBillingMonthSetting)->addMonths($maxMonthsAhead);
            $maxAllowedMonth = $maxAllowedDate->format('Y-m');
        } else {
            $maxAllowedMonth = $currentBillingMonthSetting;
        }

        $monthlyBills = Bill::where('allottee_id', $allottee->id)
            ->where('bill_month', '<=', $maxAllowedMonth)
            ->orderByDesc('bill_month')
            ->get();

        $latestBill   = Bill::where('allottee_id', $allottee->id)
            ->where('bill_month', '<=', $maxAllowedMonth)
            ->orderByDesc('bill_month')
            ->first();

        $hasMonthlyBills = $monthlyBills->isNotEmpty();
        $activeProject = \App\Models\Project::active();
        $bankAccNo    = Setting::getValue('bank_account_no', 'PHA-001-NBP-001');
        $bankName     = Setting::getValue('bank_name', 'National Bank of Pakistan');
        $bankBranch   = Setting::getValue('bank_branch', 'Islamabad Main Branch');
        if ($activeProject) {
            $bankAccNo  = $activeProject->bank_account_no ?: $bankAccNo;
            $bankName   = $activeProject->bank_name ?: $bankName;
            $bankBranch = $activeProject->bank_branch ?: $bankBranch;
        }

        $qrSvg = '';
        $qrData = '';
        if ($latestBill && $latestBill->status !== 'paid' && $latestBill->status !== 'settled') {
            $billData = app(\App\Http\Controllers\BillController::class)->billData($allottee);
            $qrSvg    = $billData['qrSvg'] ?? '';
            $qrData   = $billData['qrData'] ?? '';
        }

        return view('portal.dashboard', compact(
            'allottee', 'monthlyBills', 'hasMonthlyBills', 'latestBill',
            'bankAccNo', 'bankName', 'bankBranch', 'qrSvg', 'qrData'
        ));
    }

    public function viewMonthlyBill($month)
    {
        $id = session('portal_allottee_id');
        if (!$id) return redirect()->route('portal.login');

        $allottee  = Allottee::findOrFail($id);

        // Calculate maximum allowed month dynamically
        $currentBillingMonthSetting = Setting::getValue('current_billing_month', '2026-07');
        $allowFutureBilling = (bool) Setting::getValue('allow_future_billing', 0);
        $maxMonthsAhead = (int) Setting::getValue('max_billing_months_ahead', 1);

        if ($allowFutureBilling) {
            $maxAllowedDate = \Carbon\Carbon::createFromFormat('Y-m', $currentBillingMonthSetting)->addMonths($maxMonthsAhead);
            $maxAllowedMonth = $maxAllowedDate->format('Y-m');
        } else {
            $maxAllowedMonth = $currentBillingMonthSetting;
        }

        if ($month > $maxAllowedMonth) {
            abort(403, 'Unauthorized action.');
        }

        $bill      = Bill::where('allottee_id', $allottee->id)->where('bill_month', $month)->firstOrFail();
        $billData  = app(\App\Http\Controllers\BillController::class)->billData($allottee);
        $qrSvg     = $billData['qrSvg'] ?? '';
        $qrData    = $billData['qrData'] ?? '';
        $activeProject = \App\Models\Project::active();
        $bankAccNo    = Setting::getValue('bank_account_no', 'PHA-001-NBP-001');
        $bankName     = Setting::getValue('bank_name', 'National Bank of Pakistan');
        $bankBranch   = Setting::getValue('bank_branch', 'Islamabad Main Branch');
        // NOTE: maintenance_rate_per_sqft from Settings is the authoritative source for the billing rate.
        $rate         = (float) Setting::getValue('maintenance_rate_per_sqft', 3.07);
        if ($activeProject) {
            $bankAccNo  = $activeProject->bank_account_no ?: $bankAccNo;
            $bankName   = $activeProject->bank_name ?: $bankName;
            $bankBranch = $activeProject->bank_branch ?: $bankBranch;
            // Project rate override removed to keep Settings as the single source of truth
        }

        return view('portal.bill_detail', compact('allottee', 'bill', 'bankAccNo', 'bankName', 'bankBranch', 'rate', 'qrSvg', 'qrData'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portal_allottee_id');
        return redirect()->route('portal.login')->with('success', 'You have been signed out.');
    }
}
