<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints — PHA Maintenance Portal</title>
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
        .complaint-item { background: #fff; border-radius: 12px; padding: 18px; border: 1px solid #e8edf3; margin-bottom: 12px; transition: all 0.2s; }
        .complaint-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .badge-status { font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 4px 10px; border-radius: 20px; }
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

    <!-- Allottee Header -->
    <div class="allottee-card">
        <div class="d-flex align-items-center gap-4">
            <div style="width:64px;height:64px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:28px;color:#1B6B35;flex-shrink:0;">
                <i class="bi bi-person-fill"></i>
            </div>
            <div>
                <h4 style="font-weight:800;margin:0;">{{ $allottee->display_name }}</h4>
                <div style="font-size:13px;color:#64748b;">CNIC: {{ $allottee->display_cnic }} &nbsp;|&nbsp; Cell: {{ $allottee->cell ?? '—' }}</div>
                <div class="mt-1">
                    <span class="badge" style="background:#dbeafe;color:#1d4ed8;">Category {{ $allottee->category }}</span>
                    <span class="badge ms-1" style="background:#f0f9f4;color:#1B6B35;">{{ $allottee->covered_area }} Sq Ft</span>
                    <span class="badge ms-1" style="background:#e8f5ee;color:#1B6B35;">Block {{ $allottee->block_no }} / Flat {{ $allottee->flat_no }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <div class="d-flex gap-2">
            <a href="{{ route('portal.dashboard') }}" class="btn btn-sm btn-outline-secondary fw-bold" style="border-radius:20px; padding: 6px 18px;"><i class="bi bi-receipt me-1"></i>Billing & Payments</a>
            <a href="{{ route('portal.complaints.index') }}" class="btn btn-sm btn-success fw-bold animate-fade" style="background:#1B6B35; border:none; border-radius:20px; padding: 6px 16px;"><i class="bi bi-chat-left-text me-1"></i>Help & Complaints</a>
        </div>
        <button class="btn btn-sm btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#lodgeModal" style="background:#1B6B35; border:none; border-radius:8px; padding: 8px 16px;"><i class="bi bi-plus-circle me-1"></i>Lodge a Complaint</button>
    </div>

    <!-- Complaints list -->
    <div class="row">
        <div class="col-12">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-success"></i>My Registered Complaints</h5>
            
            @forelse($complaints as $c)
            <div class="complaint-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="fw-bold text-primary" style="font-size: 14.5px;">{{ $c->complaint_number }}</span>
                        <span class="text-muted ms-2" style="font-size:11.5px;"><i class="bi bi-calendar3 me-1"></i>{{ $c->created_at->format('d M Y') }}</span>
                    </div>
                    <div>
                        <!-- Status Badge -->
                        @if($c->status === 'new')
                            <span class="badge-status bg-primary text-white">Submitted</span>
                        @elseif($c->status === 'assigned' || $c->status === 'in_progress')
                            <span class="badge-status bg-info text-dark">Under Process</span>
                        @elseif($c->status === 'waiting_for_material' || $c->status === 'pending_external_vendor')
                            <span class="badge-status bg-warning text-dark">Delayed / Pending</span>
                        @elseif($c->status === 'resolved')
                            <span class="badge-status bg-success text-white">Resolved</span>
                        @elseif($c->status === 'closed')
                            <span class="badge-status bg-light text-muted border">Closed</span>
                        @elseif($c->status === 'reopened')
                            <span class="badge-status bg-danger text-white">Reopened</span>
                        @else
                            <span class="badge-status bg-secondary text-white">{{ $c->status }}</span>
                        @endif
                    </div>
                </div>
                
                <h6 class="fw-bold text-dark mb-1">{{ $c->subject }}</h6>
                <p class="text-muted mb-2" style="font-size: 12.5px; max-height:38px; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                    {{ $c->description }}
                </p>
                
                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2" style="font-size:12px;">
                    <div>
                        Category: <strong class="text-dark">{{ $c->category->name }}</strong>
                    </div>
                    <a href="{{ route('portal.complaints.show', $c->id) }}" class="btn btn-sm btn-outline-success fw-bold py-1 px-3" style="font-size:11.5px; border-radius:6px;">Track Details &amp; Chat <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
            @empty
            <div class="allottee-card text-center py-5 text-muted">
                <i class="bi bi-chat-left-dots fs-1 mb-2"></i>
                <p class="mb-0">You haven't registered any maintenance complaints yet.</p>
                <button class="btn btn-success btn-sm mt-3 fw-bold" data-bs-toggle="modal" data-bs-target="#lodgeModal" style="background:#1B6B35; border:none; border-radius:8px;">Lodge Your First Complaint</button>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Lodge Complaint Modal -->
<div class="modal fade" id="lodgeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <form action="{{ route('portal.complaints.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-patch-plus-fill text-success me-2"></i>Lodge a Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 12.5px; font-weight:600;">Complaint Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="" disabled selected>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 12.5px; font-weight:600;">Subject / Issue Summary</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Electrical wiring failure in lounge" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 12.5px; font-weight:600;">Detailed Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the issue in detail, including specific locations..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 12.5px; font-weight:600;">Attach Images (Optional, max 3)</label>
                        <input type="file" name="images[]" class="form-control mb-1" accept="image/*" multiple>
                        <small class="text-muted" style="font-size:11px;">You can upload JPEG, PNG, or WEBP files up to 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold" style="background:#1B6B35; border:none; padding:8px 24px;">Submit Complaint</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
