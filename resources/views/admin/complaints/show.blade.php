@extends('layouts.app')
@section('title', 'Complaint Details')
@section('page-title')
    Complaint: {{ $complaint->complaint_number }}
@endsection

@push('styles')
<style>
/* Activity Timeline Styling */
.timeline {
    position: relative;
    padding-left: 24px;
    list-style: none;
    margin-top: 15px;
}
.timeline::before {
    content: '';
    position: absolute;
    top: 5px;
    left: 9px;
    height: calc(100% - 15px);
    width: 2px;
    background: #cbd5e1;
}
.timeline-item {
    position: relative;
    margin-bottom: 24px;
}
.timeline-icon {
    position: absolute;
    left: -24px;
    top: 2px;
    background: #fff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    font-size: 11px;
}
.timeline-content {
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
}
.timeline-actor {
    font-weight: 700;
    font-size: 12.5px;
    color: #1e293b;
}
.timeline-time {
    font-size: 10.5px;
    color: #64748b;
    float: right;
}
.timeline-remarks {
    font-size: 12px;
    color: #475569;
    margin-top: 4px;
}
.timeline-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="row g-3">
    <!-- Left Column: Details & Actions -->
    <div class="col-lg-7">
        <!-- Details Card -->
        <div class="chart-card mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-file-earmark-text-fill me-2 text-success"></i>Complaint Overview</h5>
                <div>
                    <span class="badge {{ $complaint->status_badge_class }} px-2 py-1" style="font-size: 11.5px;">
                        {{ strtoupper(str_replace('_', ' ', $complaint->status)) }}
                    </span>
                    <span class="badge {{ $complaint->priority_badge_class }} px-2 py-1 ms-1" style="font-size: 11.5px;">
                        {{ strtoupper($complaint->priority) }} PRIORITY
                    </span>
                </div>
            </div>

            <table class="table table-bordered table-sm mb-4" style="font-size: 12.5px;">
                <tr>
                    <td class="bg-light fw-bold" style="width: 30%;">Complaint #</td>
                    <td class="fw-bold text-primary">{{ $complaint->complaint_number }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Date &amp; Time</td>
                    <td>{{ $complaint->created_at->format('d M Y, h:i A') }} ({{ $complaint->created_at->diffForHumans() }})</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Housing Project</td>
                    <td>{{ $complaint->project->name }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Allottee Name</td>
                    <td class="fw-bold">{{ $complaint->allottee->name }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Unit / Apartment</td>
                    <td>Block: <strong>{{ $complaint->allottee->block_no }}</strong> | Flat: <strong>{{ $complaint->allottee->flat_no }}</strong> | Floor: {{ $complaint->allottee->floor }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Contact Details</td>
                    <td>Cell: <strong>{{ $complaint->allottee->cell ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Category</td>
                    <td><span class="badge bg-light text-dark border">{{ $complaint->category->name }}</span></td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Subject</td>
                    <td class="fw-bold">{{ $complaint->subject }}</td>
                </tr>
            </table>

            <h6 class="fw-bold mt-3 text-dark">Detailed Description</h6>
            <div class="p-3 border rounded bg-light mb-3" style="font-size: 13px; line-height: 1.6; color: #334155;">
                {!! nl2br(e($complaint->description)) !!}
            </div>

            @php
                $initialAttachments = $complaint->attachments->where('upload_type', 'initial');
                $completionAttachments = $complaint->attachments->where('upload_type', 'completion');
            @endphp

            @if($initialAttachments->isNotEmpty())
            <h6 class="fw-bold text-dark mt-3">Allottee Attachments</h6>
            <div class="row g-2 mb-3">
                @foreach($initialAttachments as $file)
                    <div class="col-4">
                        <a href="{{ $file->url }}" target="_blank" class="d-block border rounded p-1 text-center bg-white shadow-sm hover-shadow">
                            @if(in_array(pathinfo($file->file_path, PATHINFO_EXTENSION), ['jpg','jpeg','png','webp']))
                                <img src="{{ $file->url }}" class="img-fluid rounded" style="max-height: 120px; object-fit: cover;">
                            @else
                                <div class="py-4 text-muted"><i class="bi bi-file-earmark-arrow-up fs-2"></i><br><small>View Document</small></div>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
            @endif

            @if($completionAttachments->isNotEmpty())
            <h6 class="fw-bold text-success mt-3"><i class="bi bi-patch-check-fill me-1"></i>Completion / Resolution Proof</h6>
            <div class="row g-2 mb-3">
                @foreach($completionAttachments as $file)
                    <div class="col-4">
                        <a href="{{ $file->url }}" target="_blank" class="d-block border border-success rounded p-1 text-center bg-white shadow-sm hover-shadow">
                            @if(in_array(pathinfo($file->file_path, PATHINFO_EXTENSION), ['jpg','jpeg','png','webp']))
                                <img src="{{ $file->url }}" class="img-fluid rounded" style="max-height: 120px; object-fit: cover;">
                            @else
                                <div class="py-4 text-muted"><i class="bi bi-file-earmark-check fs-2 text-success"></i><br><small>View Document</small></div>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Actions panel based on status & role -->
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-gear-fill me-2 text-primary"></i>Action Panel</h6>
            
            @if(auth()->user()->role === 'viewer')
                <div class="alert alert-info py-2" style="font-size:12px;">You are logged in as a <strong>Viewer</strong>. Modifying complaints is disabled.</div>
            @else
                @php
                    $role = auth()->user()->role;
                    $isStaff = ($role === 'maintenance_staff');
                    $isAdmin = in_array($role, ['super_admin', 'data_entry']);
                @endphp

                <!-- 1. Admin Assignment Form -->
                @if($isAdmin && ($complaint->status === 'new' || $complaint->status === 'under_review' || $complaint->status === 'reopened'))
                <form action="{{ route('admin.complaints.assign', $complaint) }}" method="POST" class="border rounded p-3 bg-light mb-3">
                    @csrf
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-plus-fill me-1 text-success"></i>Assign Technical Staff</h6>
                    <div class="row g-2">
                        <div class="col-md-7 col-sm-12">
                            <select name="assigned_staff_id" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Select Staff --</option>
                                @foreach($staffMembers as $member)
                                    <option value="{{ $member->id }}" {{ $complaint->assigned_staff_id === $member->id ? 'selected' : '' }}>{{ $member->name }} ({{ $member->designation }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 col-sm-12">
                            <button type="submit" class="btn btn-sm btn-success w-100 fw-bold"><i class="bi bi-check-circle me-1"></i>Confirm Assignment</button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Add assignment remarks or instructions...">
                    </div>
                </form>
                @endif

                <!-- 2. Admin Priority Selector -->
                @if($isAdmin && $complaint->status !== 'closed')
                <form action="{{ route('admin.complaints.priority', $complaint) }}" method="POST" class="border rounded p-3 bg-light mb-3">
                    @csrf
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>Adjust Priority</h6>
                    <div class="row g-2">
                        <div class="col-md-7 col-sm-12">
                            <select name="priority" class="form-select form-select-sm" required>
                                <option value="low" {{ $complaint->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $complaint->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $complaint->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="emergency" {{ $complaint->priority === 'emergency' ? 'selected' : '' }}>Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-5 col-sm-12">
                            <button type="submit" class="btn btn-sm btn-outline-warning w-100 fw-bold">Update Priority</button>
                        </div>
                    </div>
                </form>
                @endif

                <!-- 3. Staff / Admin Status Update -->
                @if(($isAdmin || $isStaff) && in_array($complaint->status, ['assigned', 'in_progress', 'waiting_for_material', 'pending_external_vendor', 'reopened']))
                <form action="{{ route('admin.complaints.status', $complaint) }}" method="POST" class="border rounded p-3 bg-light mb-3">
                    @csrf
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-activity me-1 text-primary"></i>Update Status & Progress</h6>
                    <div class="mb-2">
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="" disabled selected>-- Select Status --</option>
                            <option value="in_progress" {{ $complaint->status === 'in_progress' ? 'selected' : '' }}>Mark In Progress</option>
                            <option value="waiting_for_material" {{ $complaint->status === 'waiting_for_material' ? 'selected' : '' }}>Waiting for Material</option>
                            <option value="pending_external_vendor" {{ $complaint->status === 'pending_external_vendor' ? 'selected' : '' }}>Pending External Vendor</option>
                            @if($isAdmin)
                                <option value="rejected" {{ $complaint->status === 'rejected' ? 'selected' : '' }}>Reject / Cancel Complaint</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Provide description or update reason (min 5 chars)..." required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="bi bi-save me-1"></i>Save Progress Status</button>
                </form>
                @endif

                <!-- 4. Staff / Admin Resolution Form (With Photo Proof) -->
                @if(($isAdmin || $isStaff) && in_array($complaint->status, ['assigned', 'in_progress', 'waiting_for_material', 'pending_external_vendor', 'reopened']))
                <form action="{{ route('admin.complaints.resolve', $complaint) }}" method="POST" enctype="multipart/form-data" class="border border-success rounded p-3 bg-white mb-3 shadow-sm">
                    @csrf
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-circle-fill me-1"></i>Mark Complaint as Resolved</h6>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 11px; font-weight:700;">Resolution Action Description</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Explain the work done to resolve the issue..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 11px; font-weight:700;">Upload Proof Photograph (Optional)</label>
                        <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100 fw-bold shadow-sm" style="background:#1B6B35; border:none;"><i class="bi bi-clipboard-check me-1"></i>Mark as Resolved</button>
                </form>
                @endif

                <!-- 5. Admin Official Closure -->
                @if($isAdmin && $complaint->status === 'resolved')
                <form action="{{ route('admin.complaints.close', $complaint) }}" method="POST" class="border border-dark rounded p-3 bg-white mb-3">
                    @csrf
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-lock-fill me-1"></i>Close Complaint</h6>
                    <p class="text-muted mb-2" style="font-size: 11.5px;">Mark the complaint officially closed. Ensure allottee satisfaction has been validated.</p>
                    <div class="mb-2">
                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Add closure remarks (optional)...">
                    </div>
                    <button type="submit" class="btn btn-sm btn-dark w-100 fw-bold"><i class="bi bi-lock"></i> Close Complaint</button>
                </form>
                @endif

                <!-- 6. General Remarks Timeline Comment Form -->
                <form action="{{ route('admin.complaints.remark', $complaint) }}" method="POST" class="border rounded p-3 bg-light">
                    @csrf
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-chat-left-dots-fill me-1 text-info"></i>Add Timeline Remark</h6>
                    <div class="row g-2">
                        <div class="col-9">
                            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Add a timeline note or comment..." required>
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-sm btn-outline-info w-100 fw-bold">Add Note</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Right Column: Interactive Timeline log -->
    <div class="col-lg-5">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-clock-history me-2 text-warning"></i>Activity Timeline</h6>
            <p class="chart-sub">Chronological audit trail of all actions and updates</p>

            <ul class="timeline">
                @foreach($complaint->logs as $log)
                <li class="timeline-item">
                    <span class="timeline-icon">
                        <i class="bi {{ $log->icon }}"></i>
                    </span>
                    <div class="timeline-content">
                        <span class="timeline-time">{{ $log->created_at->format('d M H:i') }}</span>
                        <div class="timeline-actor">{{ $log->actor_name }}</div>
                        
                        <div class="mt-1">
                            <span class="timeline-badge bg-secondary text-white">{{ strtoupper($log->action) }}</span>
                            @if($log->status_from || $log->status_to)
                                <span style="font-size: 11px;" class="text-muted ms-1">
                                    {{ strtoupper($log->status_from ?? 'new') }} <i class="bi bi-arrow-right"></i> {{ strtoupper($log->status_to) }}
                                </span>
                            @endif
                        </div>
                        
                        @if($log->remarks)
                            <div class="timeline-remarks">{!! nl2br(e($log->remarks)) !!}</div>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
