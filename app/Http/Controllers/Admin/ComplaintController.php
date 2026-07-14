<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\MaintenanceStaff;
use App\Models\ComplaintLog;
use App\Models\ComplaintAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    private function getBaseQuery()
    {
        $user = Auth::user();
        $query = Complaint::with(['allottee.property', 'category', 'assignedStaff']);

        if ($user->role === 'maintenance_staff') {
            $staff = MaintenanceStaff::where('user_id', $user->id)->first();
            if ($staff) {
                $query->where('assigned_staff_id', $staff->id);
            } else {
                // If staff user is not linked to any staff member, return nothing
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->getBaseQuery();

        // Searching
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('complaint_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('allottee', function($allotteeQuery) use ($search) {
                      $allotteeQuery->active()
                                    ->where(function($sub) use ($search) {
                                        $sub->where('name', 'like', "%{$search}%")
                                            ->orWhere('flat_no', 'like', "%{$search}%")
                                            ->orWhere('block_no', 'like', "%{$search}%");
                                    });
                  });
            });
        }

        // Filtering
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Status filter tab
        $tab = $request->get('tab', 'all');
        if ($tab === 'new') {
            $query->where('status', 'new');
        } elseif ($tab === 'assigned') {
            $query->where('status', 'assigned');
        } elseif ($tab === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($tab === 'pending') {
            $query->whereIn('status', ['waiting_for_material', 'pending_external_vendor']);
        } elseif ($tab === 'resolved') {
            $query->where('status', 'resolved');
        } elseif ($tab === 'closed') {
            $query->where('status', 'closed');
        }

        $complaints = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        
        $categories = \App\Models\ComplaintCategory::active()->orderBy('name')->get();
        $staffMembers = MaintenanceStaff::active()->orderBy('name')->get();

        return view('admin.complaints.index', compact('complaints', 'categories', 'staffMembers', 'tab'));
    }

    public function show(Complaint $complaint)
    {
        // Enforce role checks for maintenance staff
        $user = Auth::user();
        if ($user->role === 'maintenance_staff') {
            $staff = MaintenanceStaff::where('user_id', $user->id)->first();
            if (!$staff || $complaint->assigned_staff_id !== $staff->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $complaint->load(['allottee', 'category', 'assignedStaff', 'attachments', 'logs.user', 'logs.allottee']);
        
        $staffMembers = MaintenanceStaff::active()->orderBy('name')->get();

        return view('admin.complaints.show', compact('complaint', 'staffMembers'));
    }

    public function assign(Request $request, Complaint $complaint)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'assigned_staff_id' => 'required|exists:maintenance_staff,id',
            'remarks' => 'nullable|string'
        ]);

        $oldStatus = $complaint->status;
        $staff = MaintenanceStaff::findOrFail($request->assigned_staff_id);

        $complaint->update([
            'assigned_staff_id' => $staff->id,
            'status' => 'assigned'
        ]);

        // Log Action
        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'action' => 'assigned',
            'status_from' => $oldStatus,
            'status_to' => 'assigned',
            'remarks' => "Assigned to: {$staff->name} ({$staff->designation}). " . ($request->remarks ?? '')
        ]);

        // Simulate WhatsApp / SMS Notification log
        try {
            \Illuminate\Support\Facades\DB::table('notifications_log')->insert([
                'allottee_id' => $complaint->allottee_id,
                'project_id' => $complaint->project_id,
                'channel' => 'sms',
                'message' => "PHA CMS: Complaint {$complaint->complaint_number} has been assigned to {$staff->name} ({$staff->designation}) for resolution.",
                'status' => 'sent',
                'sent_by' => Auth::user()->name,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {}

        return redirect()->route('admin.complaints.show', $complaint)->with('success', "Complaint assigned to {$staff->name} successfully.");
    }

    public function updatePriority(Request $request, Complaint $complaint)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'priority' => 'required|in:low,medium,high,emergency'
        ]);

        $oldPriority = $complaint->priority;
        $complaint->update(['priority' => $request->priority]);

        // Log action
        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'action' => 'status_changed',
            'remarks' => "Priority changed from " . strtoupper($oldPriority) . " to " . strtoupper($request->priority)
        ]);

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'Complaint priority updated.');
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'maintenance_staff') {
            $staff = MaintenanceStaff::where('user_id', $user->id)->first();
            if (!$staff || $complaint->assigned_staff_id !== $staff->id) {
                abort(403, 'Unauthorized.');
            }
        } elseif (!in_array($user->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'status' => 'required|in:in_progress,waiting_for_material,pending_external_vendor,rejected',
            'remarks' => 'required|string|min:5'
        ]);

        $oldStatus = $complaint->status;
        $complaint->update(['status' => $request->status]);

        // Log action
        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'action' => 'status_changed',
            'status_from' => $oldStatus,
            'status_to' => $request->status,
            'remarks' => $request->remarks
        ]);

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'Complaint status updated.');
    }

    public function resolve(Request $request, Complaint $complaint)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'maintenance_staff') {
            $staff = MaintenanceStaff::where('user_id', $user->id)->first();
            if (!$staff || $complaint->assigned_staff_id !== $staff->id) {
                abort(403, 'Unauthorized.');
            }
        } elseif (!in_array($user->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'remarks' => 'required|string|min:5',
            'photo' => 'nullable|image|max:5120' // 5MB max
        ]);

        $oldStatus = $complaint->status;
        $complaint->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        // Log action
        $log = ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'action' => 'resolved',
            'status_from' => $oldStatus,
            'status_to' => 'resolved',
            'remarks' => 'Complaint marked as RESOLVED. Work Summary: ' . $request->remarks
        ]);

        // Handle completion photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('complaints/completion', 'public');
            ComplaintAttachment::create([
                'complaint_id' => $complaint->id,
                'user_id' => Auth::id(),
                'file_path' => $path,
                'file_type' => 'image',
                'upload_type' => 'completion'
            ]);
        }

        // Simulate WhatsApp / SMS Notification log
        try {
            \Illuminate\Support\Facades\DB::table('notifications_log')->insert([
                'allottee_id' => $complaint->allottee_id,
                'project_id' => $complaint->project_id,
                'channel' => 'sms',
                'message' => "PHA CMS: Your complaint {$complaint->complaint_number} has been marked as RESOLVED. Please sign into the portal to review and provide feedback.",
                'status' => 'sent',
                'sent_by' => Auth::user()->name,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {}

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'Complaint has been marked as resolved.');
    }

    public function close(Request $request, Complaint $complaint)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'remarks' => 'nullable|string'
        ]);

        $oldStatus = $complaint->status;
        $complaint->update([
            'status' => 'closed',
            'closed_at' => now()
        ]);

        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'action' => 'closed',
            'status_from' => $oldStatus,
            'status_to' => 'closed',
            'remarks' => 'Complaint officially CLOSED by administrator. ' . ($request->remarks ?? '')
        ]);

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'Complaint has been closed.');
    }

    public function addRemark(Request $request, Complaint $complaint)
    {
        $request->validate([
            'remarks' => 'required|string|min:2'
        ]);

        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'action' => 'remarked',
            'remarks' => $request->remarks
        ]);

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'Remark added to timeline.');
    }
}
