<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

        DB::transaction(function() use ($request, $allottee) {
            // Lock the allottee row to prevent concurrent modifications
            $lockedAllottee = Allottee::lockForUpdate()->findOrFail($allottee->id);

            // Log the payment transaction
            PaymentTransaction::create([
                'allottee_id'  => $lockedAllottee->id,
                'project_id'   => $lockedAllottee->project_id,
                'amount_paid'  => $request->amount_paid,
                'payment_mode' => $request->payment_mode,
                'payment_date' => $request->payment_date,
                'payment_ref'  => $request->payment_ref,
                'created_by'   => Auth::id(),
            ]);

            // Unified recalculation helper
            $lockedAllottee->recalculateBillingStatuses();
        });

        return back()->with('success', 'Payment recorded successfully for ' . $allottee->name);
    }
}
