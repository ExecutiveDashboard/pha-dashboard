<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\Bill;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonthlyBillController extends Controller
{
    /** GET /bills/monthly — show the monthly bill management page */
    public function index(Request $request)
    {
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $project = Project::active();

        // Bills already generated for this month
        $bills = Bill::with('allottee')
            ->where('bill_month', $selectedMonth)
            ->orderBy('status')
            ->paginate(30)
            ->withQueryString();

        $billCount   = Bill::where('bill_month', $selectedMonth)->count();
        $paidCount   = Bill::where('bill_month', $selectedMonth)->where('status', 'paid')->count();
        $unpaidCount = Bill::where('bill_month', $selectedMonth)->whereIn('status', ['unpaid','partial'])->count();
        $totalAmount = Bill::where('bill_month', $selectedMonth)->sum('total_amount');
        $paidAmount  = Bill::where('bill_month', $selectedMonth)->sum('paid_amount');

        return view('bills.monthly', compact(
            'selectedMonth', 'bills', 'project',
            'billCount', 'paidCount', 'unpaidCount', 'totalAmount', 'paidAmount'
        ));
    }

    /** POST /bills/monthly/generate — generate bills for all allottees for a month */
    public function generate(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);
        $month = $request->month;

        $rate     = (float) Setting::getValue('maintenance_rate_per_sqft', 3.07);
        $wwAmt    = (float) Setting::getValue('watch_ward_amount', 10000);
        $delayPct = (float) Setting::getValue('delay_charge_percent', 10);

        $allottees = Allottee::all();
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

            // 2. Teacher's Formula: Maintenance = Rate * Area * No. of Months
            // Now adding Extra Charges (Parking, Water) multiplied by due_months
            $parkingRate = $allottee->has_parking ? ($allottee->parking_charges > 0 ? $allottee->parking_charges : (float) Setting::getValue('parking_charges_rate', 500)) : 0;
            $waterRate = $allottee->has_water ? ($allottee->water_charges > 0 ? $allottee->water_charges : (float) Setting::getValue('water_charges_rate', 1000)) : 0;
            
            $monthlyBase = ($rate * $allottee->covered_area) + $parkingRate + $waterRate;
            $maintenance = round($monthlyBase * $allottee->due_months, 2);
            $allottee->maintenance_charges = $maintenance;

            // W&W: Mark as charged if applicable
            $ww = $allottee->watch_ward_charges;
            if (!$allottee->ww_charged && $ww > 0) {
                $allottee->ww_charged = true;
                $allottee->ww_charged_date = $allottee->possession_date ?? now();
            }

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
            $allottee->save();

            // 3. Create the Unified Monthly Bill (Snapshot of Account State)
            $totalDue = max(0, $allottee->total_maintenance_charges - $allottee->amount_paid);
            $psid = Bill::generatePsid($allottee, $month);

            Bill::create([
                'allottee_id'        => $allottee->id,
                'bill_month'         => $month,
                'psid'               => $psid,
                'maintenance_amount' => $maintenance,
                'ww_amount'          => $ww,
                'fine_amount'        => $fine,
                'total_amount'       => $totalDue,
                'paid_amount'        => 0,
                'status'             => $totalDue > 0 ? 'unpaid' : 'paid',
            ]);
            
            $generated++;
        }

        return redirect()->route('bills.monthly', ['month' => $month])
            ->with('success', "Generated {$generated} bills for {$month}. Skipped {$skipped} (already existed).");
    }

    /** POST /bills/monthly/{bill}/pay — record payment for a monthly bill */
    public function recordPayment(Request $request, Bill $bill)
    {
        $request->validate([
            'paid_amount'  => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,online,cheque,psid,waived',
            'payment_date' => 'required|date',
            'payment_ref'  => 'nullable|string|max:100',
        ]);

        $paid  = (float) $request->paid_amount;
        $total = (float) $bill->total_amount;

        $status = 'unpaid';
        if ($paid >= $total) $status = 'paid';
        elseif ($paid > 0)   $status = 'partial';

        $oldPaid = (float) $bill->paid_amount;
        $difference = $paid - $oldPaid;

        $bill->update([
            'paid_amount'  => $paid,
            'payment_mode' => $request->payment_mode,
            'payment_date' => $request->payment_date,
            'payment_ref'  => $request->payment_ref,
            'status'       => $status,
            'is_locked'    => ($status === 'paid'),
            'locked_at'    => ($status === 'paid') ? now() : null,
        ]);

        // Sync with Allottee's historical backlog
        if ($difference != 0) {
            $bill->allottee->increment('amount_paid', $difference);
            if ($paid > 0) {
                $bill->allottee->update([
                    'payment_date' => $request->payment_date,
                    'payment_mode' => $request->payment_mode,
                    'payment_ref'  => $request->payment_ref,
                ]);
            }
        }

        return back()->with('success', 'Payment recorded for ' . $bill->allottee->name . ' — ' . $bill->bill_month_label);
    }

    /** POST /bills/monthly/{bill}/settle — admin manual settlement */
    public function settle(Request $request, Bill $bill)
    {
        $request->validate([
            'settled_note' => 'required|string|max:500',
        ]);

        $oldPaid = (float) $bill->paid_amount;
        $total = (float) $bill->total_amount;
        $difference = $total - $oldPaid;

        $bill->update([
            'status'       => 'settled',
            'paid_amount'  => $total,
            'settled_by'   => auth()->user()->name,
            'settled_note' => $request->settled_note,
            'is_locked'    => true,
            'locked_at'    => now(),
        ]);

        if ($difference > 0) {
            $bill->allottee->increment('amount_paid', $difference);
            $bill->allottee->update([
                'payment_date' => now(),
                'payment_mode' => 'waived/settled',
                'payment_ref'  => 'Admin Settlement',
            ]);
        }

        return back()->with('success', 'Bill manually settled for ' . $bill->allottee->name);
    }

    /** GET /bills/monthly/{bill}/check-psid — simulate PSID payment check */
    public function checkPsid(Bill $bill)
    {
        // Simulated response — in production this would call 1Link/Raast API
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
