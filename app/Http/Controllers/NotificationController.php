<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\Project;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** GET /notifications — bulk messaging UI */
    public function index(Request $request)
    {
        $allottees = Allottee::select('id','name','cell','cnic','block_no','flat_no','category','due_months','overdue_months')
            ->orderBy('name')
            ->get();

        $project = Project::active();

        // Pre-built message templates
        $templates = [
            'maintenance_due' => "Dear [NAME], your PHA maintenance bill for [MONTH] is due. Total: Rs.[AMOUNT]. Pay via PSID: [PSID] at any bank branch or online via 1Bill/Raast. Contact: 051-XXXXXXX",
            'reminder'        => "REMINDER: Dear [NAME], your maintenance dues are pending. Please pay Rs.[AMOUNT] immediately to avoid further delay charges. PSID: [PSID]. PHAF I-16/3",
            'defaulter'       => "NOTICE: Dear [NAME], you have [MONTHS] overdue months of maintenance charges totalling Rs.[AMOUNT]. Please regularize immediately. Pakistan Housing Authority Foundation.",
            'receipt'         => "Dear [NAME], payment of Rs.[AMOUNT] received on [DATE] for PHAF maintenance. Receipt No: [REF]. Thank you. Pakistan Housing Authority Foundation.",
        ];

        // Simulated message log (last 10)
        $logs = \Illuminate\Support\Facades\DB::table('notifications_log')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->toArray();

        return view('notifications.index', compact('allottees', 'project', 'templates', 'logs'));
    }

    /** POST /notifications/send — simulate send (no real API) */
    public function send(Request $request)
    {
        $request->validate([
            'channel'     => 'required|in:whatsapp,sms,email',
            'allottee_ids'=> 'required|array|min:1',
            'message'     => 'required|string|min:10',
        ]);

        $count   = count($request->allottee_ids);
        $channel = ucfirst($request->channel);

        // Simulate logging (create table if needed via migration later, use try/catch)
        try {
            foreach ($request->allottee_ids as $id) {
                \Illuminate\Support\Facades\DB::table('notifications_log')->insert([
                    'allottee_id' => $id,
                    'channel'     => $request->channel,
                    'message'     => substr($request->message, 0, 255),
                    'status'      => 'sent',  // simulated
                    'sent_by'     => auth()->user()->name,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Table may not exist yet — that's OK for presentation
        }

        return response()->json([
            'success' => true,
            'message' => "✅ {$channel} messages sent to {$count} allottees successfully. (Simulated — API integration pending)",
            'count'   => $count,
            'channel' => $channel,
        ]);
    }

    /** POST /notifications/send-single — send to one allottee */
    public function sendSingle(Request $request)
    {
        $request->validate([
            'allottee_id' => 'required|exists:allottees,id',
            'channel'     => 'required|in:whatsapp,sms',
            'message'     => 'required|string',
        ]);

        $allottee = Allottee::findOrFail($request->allottee_id);

        return response()->json([
            'success' => true,
            'message' => "✅ {$request->channel} sent to {$allottee->name} ({$allottee->cell}). (Simulated)",
        ]);
    }
}
