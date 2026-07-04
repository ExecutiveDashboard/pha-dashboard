<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Allottee $allottee)
    {
        $request->validate([
            'amount_paid'  => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,online,cheque',
            'payment_date' => 'required|date',
            'payment_ref'  => 'nullable|string|max:100',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $allottee) {
            // Lock the allottee row to prevent concurrent modifications
            $lockedAllottee = Allottee::lockForUpdate()->findOrFail($allottee->id);

            $lockedAllottee->amount_paid += $request->amount_paid;
            $lockedAllottee->payment_mode = $request->payment_mode;
            $lockedAllottee->payment_date = $request->payment_date;
            $lockedAllottee->payment_ref = $request->payment_ref;
            $lockedAllottee->save(); // Re-calculates overdue months automatically

            // Lock and retrieve the entire range of unpaid and partial bills ordered chronologically
            $remaining = (float) $request->amount_paid;
            $unpaid = \App\Models\Bill::withoutGlobalScopes()
                ->where('allottee_id', $lockedAllottee->id)
                ->whereNotIn('status', ['paid', 'settled'])
                ->orderBy('bill_month', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($unpaid as $b) {
                if ($remaining <= 0) break;
                $remaining = $b->recordPaymentAmount($remaining, $request->payment_mode, $request->payment_date, $request->payment_ref);
            }
        });

        return back()->with('success', 'Payment recorded successfully for ' . $allottee->name);
    }
}
