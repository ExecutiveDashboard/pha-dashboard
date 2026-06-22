<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintLog;
use App\Models\Allottee;
use App\Models\Project;
use Illuminate\Http\Request;

class PortalComplaintController extends Controller
{
    private function getAllotteeId()
    {
        return session('portal_allottee_id');
    }

    public function index()
    {
        $allotteeId = $this->getAllotteeId();
        if (!$allotteeId) {
            return redirect()->route('portal.login');
        }

        $allottee = Allottee::findOrFail($allotteeId);
        $complaints = Complaint::where('allottee_id', $allotteeId)
            ->with(['category', 'assignedStaff'])
            ->orderByDesc('created_at')
            ->get();

        $categories = ComplaintCategory::active()->orderBy('name')->get();

        return view('portal.complaints.index', compact('allottee', 'complaints', 'categories'));
    }

    public function store(Request $request)
    {
        $allotteeId = $this->getAllotteeId();
        if (!$allotteeId) {
            return redirect()->route('portal.login');
        }

        $allottee = Allottee::findOrFail($allotteeId);

        $request->validate([
            'category_id' => 'required|exists:complaint_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'images.*' => 'nullable|image|max:5120' // 5MB max
        ]);

        $activeProject = Project::active() ?? Project::first();

        // Create complaint
        $complaint = Complaint::create([
            'project_id' => $allottee->project_id ?? $activeProject->id,
            'allottee_id' => $allottee->id,
            'category_id' => $request->category_id,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => 'medium', // Default priority, admin can adjust
            'status' => 'new'
        ]);

        // Create log
        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'allottee_id' => $allottee->id,
            'action' => 'created',
            'status_to' => 'new',
            'remarks' => 'Complaint lodged by allottee via portal.'
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('complaints/initial', 'public');
                ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'allottee_id' => $allottee->id,
                    'file_path' => $path,
                    'file_type' => 'image',
                    'upload_type' => 'initial'
                ]);
            }
        }

        // Log notification trigger
        try {
            \Illuminate\Support\Facades\DB::table('notifications_log')->insert([
                'allottee_id' => $allottee->id,
                'project_id' => $allottee->project_id ?? $activeProject->id,
                'channel' => 'sms',
                'message' => "PHA CMS: New complaint {$complaint->complaint_number} registered successfully for Block {$allottee->block_no}, Flat {$allottee->flat_no}.",
                'status' => 'sent',
                'sent_by' => 'Portal',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {}

        return redirect()->route('portal.complaints.index')->with('success', 'Your complaint has been successfully registered under Ticket #' . $complaint->complaint_number);
    }

    public function show($id)
    {
        $allotteeId = $this->getAllotteeId();
        if (!$allotteeId) {
            return redirect()->route('portal.login');
        }

        $allottee = Allottee::findOrFail($allotteeId);
        $complaint = Complaint::where('allottee_id', $allotteeId)
            ->where('id', $id)
            ->with(['category', 'assignedStaff', 'attachments', 'logs.user', 'logs.allottee'])
            ->firstOrFail();

        return view('portal.complaints.show', compact('allottee', 'complaint'));
    }

    public function feedback(Request $request, $id)
    {
        $allotteeId = $this->getAllotteeId();
        if (!$allotteeId) {
            return redirect()->route('portal.login');
        }

        $complaint = Complaint::where('allottee_id', $allotteeId)->where('id', $id)->firstOrFail();

        $request->validate([
            'satisfaction' => 'required|in:satisfied,unsatisfied',
            'remarks' => 'nullable|string'
        ]);

        $oldStatus = $complaint->status;
        $isSatisfied = ($request->satisfaction === 'satisfied');

        if ($isSatisfied) {
            $complaint->update([
                'status' => 'closed',
                'satisfaction_confirmed' => true,
                'feedback_remarks' => $request->remarks,
                'closed_at' => now()
            ]);

            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'allottee_id' => $allotteeId,
                'action' => 'feedback_submitted',
                'status_from' => $oldStatus,
                'status_to' => 'closed',
                'remarks' => 'Allottee confirmed resolution. Rating: Satisfied. Feedback: ' . ($request->remarks ?? 'N/A')
            ]);

            return redirect()->route('portal.complaints.index')->with('success', 'Thank you for your feedback. The ticket has been closed.');
        } else {
            // If unsatisfied, we reopen the complaint automatically
            $complaint->update([
                'status' => 'reopened',
                'satisfaction_confirmed' => false,
                'feedback_remarks' => $request->remarks
            ]);

            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'allottee_id' => $allotteeId,
                'action' => 'reopened',
                'status_from' => $oldStatus,
                'status_to' => 'reopened',
                'remarks' => 'Allottee marked resolution unsatisfied. Reopening ticket. Feedback: ' . ($request->remarks ?? 'N/A')
            ]);

            return redirect()->route('portal.complaints.index')->with('success', 'Complaint reopened. We will re-evaluate and assign technicians to resolve it.');
        }
    }

    public function reopen(Request $request, $id)
    {
        $allotteeId = $this->getAllotteeId();
        if (!$allotteeId) {
            return redirect()->route('portal.login');
        }

        $complaint = Complaint::where('allottee_id', $allotteeId)->where('id', $id)->firstOrFail();

        $request->validate([
            'remarks' => 'required|string|min:5'
        ]);

        $oldStatus = $complaint->status;

        $complaint->update([
            'status' => 'reopened'
        ]);

        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'allottee_id' => $allotteeId,
            'action' => 'reopened',
            'status_from' => $oldStatus,
            'status_to' => 'reopened',
            'remarks' => 'Ticket reopened by allottee. Reason: ' . $request->remarks
        ]);

        return redirect()->route('portal.complaints.show', $complaint->id)->with('success', 'Complaint has been reopened.');
    }

    public function addRemark(Request $request, $id)
    {
        $allotteeId = $this->getAllotteeId();
        if (!$allotteeId) {
            return redirect()->route('portal.login');
        }

        $complaint = Complaint::where('allottee_id', $allotteeId)->where('id', $id)->firstOrFail();

        $request->validate([
            'remarks' => 'required|string|min:2'
        ]);

        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'allottee_id' => $allotteeId,
            'action' => 'remarked',
            'remarks' => $request->remarks
        ]);

        return redirect()->route('portal.complaints.show', $complaint->id)->with('success', 'Remark added successfully.');
    }
}
