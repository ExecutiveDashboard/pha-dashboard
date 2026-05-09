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

        $allottee->update([
            'amount_paid'  => $request->amount_paid,
            'payment_mode' => $request->payment_mode,
            'payment_date' => $request->payment_date,
            'payment_ref'  => $request->payment_ref,
        ]);

        return back()->with('success', 'Payment recorded successfully for ' . $allottee->name);
    }
}
