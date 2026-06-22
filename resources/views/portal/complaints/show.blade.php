<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Ticket — PHA Maintenance Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f4f8; }
        .portal-topbar {
            background: linear-gradient(135deg, #0f4423, #1B6B35);
            padding: 12px 24px; display: flex; align-items: center; justify-content: space-between;
        }
        .portal-topbar .brand { display: flex; align-items: center; gap: 12px; }
        .portal-topbar .brand img { height: 36px; }
        .portal-topbar .brand-text .t1 { color: #fff; font-weight: 700; font-size: 14px; }
        .portal-topbar .brand-text .t2 { color: rgba(255,255,255,0.6); font-size: 11px; }
        .page-body { padding: 28px; max-width: 900px; margin: 0 auto; }
        .allottee-card { background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #e8edf3; margin-bottom: 20px; }
        .timeline { position: relative; padding-left: 24px; list-style: none; margin-top: 15px; }
        .timeline::before { content: ''; position: absolute; top: 5px; left: 9px; height: calc(100% - 15px); width: 2px; background: #cbd5e1; }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-icon { position: absolute; left: -24px; top: 2px; background: #fff; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 4px rgba(0,0,0,0.15); font-size: 10px; }
        .timeline-content { background: #f8fafc; border-radius: 10px; padding: 12px 14px; border: 1px solid #e2e8f0; }
        .timeline-actor { font-weight: 700; font-size: 12px; color: #1e293b; }
        .timeline-time { font-size: 10px; color: #64748b; float: right; }
        .timeline-remarks { font-size: 11.5px; color: #475569; margin-top: 4px; }
        .timeline-badge { font-size: 9px; padding: 1px 5px; border-radius: 3px; font-weight: 600; }
    </style>
</head>
<body>

<div class="portal-topbar">
    <div class="brand">
        <img src="{{ asset('images/logos/govt-pk.svg') }}" alt="Govt">
        <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA">
        <div class="brand-text">
            <div class="t1">PHA Maintenance Services Portal</div>
            <div class="t2">Government of Pakistan — Ministry of Housing & Works</div>
        </div>
    </div>
    <form method="POST" action="{{ route('portal.logout') }}">
        @csrf
        <button class="btn btn-sm btn-outline-light" style="font-size:12px;"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</button>
    </form>
</div>

<div class="page-body">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:10px;">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="mb-3">
        <a href="{{ route('portal.complaints.index') }}" class="btn btn-sm btn-outline-secondary fw-bold" style="border-radius:20px; padding: 6px 16px;"><i class="bi bi-arrow-left me-1"></i>Back to Complaints List</a>
    </div>

    <!-- Ticket Detail and Timeline -->
    <div class="row g-3">
        <!-- Details Column -->
        <div class="col-md-7">
            <div class="allottee-card">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold m-0 text-dark">Ticket Details</h5>
                    <div>
                        @if($complaint->status === 'new')
                            <span class="badge bg-primary">SUBMITTED</span>
                        @elseif($complaint->status === 'assigned' || $complaint->status === 'in_progress')
                            <span class="badge bg-info text-dark">UNDER PROCESS</span>
                        @elseif($complaint->status === 'resolved')
                            <span class="badge bg-success">RESOLVED</span>
                        @elseif($complaint->status === 'closed')
                            <span class="badge bg-light text-muted border">CLOSED</span>
                        @elseif($complaint->status === 'reopened')
                            <span class="badge bg-danger">REOPENED</span>
                        @else
                            <span class="badge bg-secondary">{{ strtoupper($complaint->status) }}</span>
                        @endif
                    </div>
                </div>

                <table class="table table-sm table-borderless" style="font-size: 13px;">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Ticket Number</td>
                        <td class="fw-bold text-primary">{{ $complaint->complaint_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lodged Date</td>
                        <td class="fw-bold">{{ $complaint->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Category</td>
                        <td class="fw-bold"><span class="badge bg-light text-dark border">{{ $complaint->category->name }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Unit Details</td>
                        <td class="fw-bold">Block {{ $complaint->allottee->block_no }}, Flat {{ $complaint->allottee->flat_no }}</td>
                    </tr>
                    @if($complaint->assignedStaff)
                    <tr>
                        <td class="text-muted">Assigned Technician</td>
                        <td class="fw-bold"><i class="bi bi-person-check-fill text-success me-1"></i>{{ $complaint->assignedStaff->name }} ({{ $complaint->assignedStaff->designation }})</td>
                    </tr>
                    @endif
                </table>

                <h6 class="fw-bold text-dark border-top pt-2 mt-3">Subject</h6>
                <div class="fw-bold mb-2" style="font-size: 14px;">{{ $complaint->subject }}</div>
                
                <h6 class="fw-bold text-dark mt-2">Description</h6>
                <div class="p-3 border rounded bg-light mb-3" style="font-size: 12.5px; line-height: 1.6; color: #4b5563;">
                    {!! nl2br(e($complaint->description)) !!}
                </div>

                <!-- Attachments -->
                @php
                    $initialAtt = $complaint->attachments->where('upload_type', 'initial');
                    $completionAtt = $complaint->attachments->where('upload_type', 'completion');
                @endphp

                @if($initialAtt->isNotEmpty())
                <h6 class="fw-bold text-dark mt-3">Uploaded Photographs</h6>
                <div class="row g-2 mb-3">
                    @foreach($initialAtt as $file)
                        <div class="col-4">
                            <a href="{{ $file->url }}" target="_blank" class="d-block border rounded p-1 text-center bg-white">
                                <img src="{{ $file->url }}" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
                            </a>
                        </div>
                    @endforeach
                </div>
                @endif

                @if($completionAtt->isNotEmpty())
                <h6 class="fw-bold text-success mt-3"><i class="bi bi-patch-check-fill me-1"></i>Work Completion Proof</h6>
                <div class="row g-2 mb-3">
                    @foreach($completionAtt as $file)
                        <div class="col-4">
                            <a href="{{ $file->url }}" target="_blank" class="d-block border border-success rounded p-1 text-center bg-white">
                                <img src="{{ $file->url }}" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
                            </a>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Feedback & Actions Block -->
            @if($complaint->status === 'resolved')
            <div class="allottee-card border border-success">
                <h5 class="fw-bold text-success mb-2"><i class="bi bi-star-fill me-1"></i>Satisfaction Confirmation</h5>
                <p style="font-size:12.5px;" class="text-muted">The maintenance technician has marked this ticket as resolved. Please confirm if you are satisfied with the quality of work performed.</p>
                
                <form action="{{ route('portal.complaints.feedback', $complaint->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:12.5px;">Are you satisfied?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="satisfaction" id="sat_yes" value="satisfied" checked>
                                <label class="form-check-label text-success fw-bold" for="sat_yes"><i class="bi bi-emoji-smile me-1"></i>Yes, Satisfied</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="satisfaction" id="sat_no" value="unsatisfied">
                                <label class="form-check-label text-danger fw-bold" for="sat_no"><i class="bi bi-emoji-frown me-1"></i>No, Unsatisfied (Reopen)</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:12.5px;">Remarks / Feedback Comments</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="3" placeholder="Add feedback remarks regarding the work done..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100 fw-bold shadow-sm" style="background:#1B6B35; border:none; padding:8px;"><i class="bi bi-check-circle me-1"></i>Submit Feedback &amp; Close Ticket</button>
                </form>
            </div>
            @endif

            @if($complaint->status === 'closed')
            <div class="allottee-card text-center border bg-light">
                <i class="bi bi-lock-fill text-muted fs-2"></i>
                <h6 class="fw-bold text-dark mt-2">This ticket is closed.</h6>
                <p style="font-size:12px;" class="text-muted">If the issue has occurred again or was not properly fixed, you may reopen the complaint ticket.</p>
                <button class="btn btn-sm btn-outline-danger fw-bold" data-bs-toggle="collapse" data-bs-target="#reopenForm"><i class="bi bi-arrow-counterclockwise"></i> Reopen Complaint Ticket</button>
                
                <div class="collapse mt-3 text-start border rounded p-3 bg-white" id="reopenForm">
                    <form action="{{ route('portal.complaints.reopen', $complaint->id) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label fw-bold" style="font-size: 12px;">Reason for Reopening</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Describe why you need to reopen this complaint ticket (min 5 chars)..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-danger fw-bold">Confirm Reopen</button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Send Remark Form (Allottee Comment box) -->
            @if($complaint->status !== 'closed' && $complaint->status !== 'resolved')
            <div class="allottee-card">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-reply-fill text-info me-1"></i>Send Comment or Update</h6>
                <form action="{{ route('portal.complaints.remark', $complaint->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Ask a question or add details to the timeline..." required>
                        <button type="submit" class="btn btn-sm btn-success fw-bold" style="background:#1B6B35; border:none; padding:0 20px;">Send Note</button>
                    </div>
                </form>
            </div>
            @endif
        </div>

        <!-- Timeline Column -->
        <div class="col-md-5">
            <div class="allottee-card h-100">
                <h5 class="fw-bold text-dark mb-2"><i class="bi bi-clock-history text-success me-2"></i>Activity Timeline</h5>
                <p style="font-size: 11px;" class="text-muted">History of status updates and notes</p>

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
                                    <span style="font-size: 10px;" class="text-muted ms-1">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
