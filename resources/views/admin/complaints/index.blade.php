@extends('layouts.app')
@section('title', 'Manage Complaints')
@section('page-title', 'Manage Complaints')

@section('content')
<div class="row g-3">
    <!-- Filters & Search Card -->
    <div class="col-12">
        <div class="chart-card">
            <form method="GET" action="{{ route('admin.complaints.index') }}" class="row g-2 align-items-center">
                <!-- Keep existing tab selection active -->
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <div class="col-md-4 col-sm-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by Complaint #, Allottee, Unit..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="col-md-2 col-sm-6">
                    <select name="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2 col-sm-6">
                    <select name="priority" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Priorities --</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="emergency" {{ request('priority') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="waiting_for_material" {{ request('status') === 'waiting_for_material' ? 'selected' : '' }}>Waiting for Material</option>
                        <option value="pending_external_vendor" {{ request('status') === 'pending_external_vendor' ? 'selected' : '' }}>Pending External Vendor</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="reopened" {{ request('status') === 'reopened' ? 'selected' : '' }}>Reopened</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 text-end">
                    <button type="submit" class="btn btn-success fw-bold w-100" style="background:#1B6B35;border:none;"><i class="bi bi-filter me-1"></i>Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Tabs Nav -->
    <div class="col-12">
        <ul class="nav nav-tabs border-bottom-2 mt-2">
            <li class="nav-item">
                <a class="nav-link fw-bold px-3 {{ $tab === 'all' ? 'active text-success' : 'text-secondary' }}" href="{{ route('admin.complaints.index', array_merge(request()->except('tab'), ['tab' => 'all'])) }}">
                    All Complaints
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-3 {{ $tab === 'new' ? 'active text-primary' : 'text-secondary' }}" href="{{ route('admin.complaints.index', array_merge(request()->except('tab'), ['tab' => 'new'])) }}">
                    New
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-3 {{ $tab === 'assigned' ? 'active text-warning' : 'text-secondary' }}" href="{{ route('admin.complaints.index', array_merge(request()->except('tab'), ['tab' => 'assigned'])) }}">
                    Assigned
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-3 {{ $tab === 'in_progress' ? 'active text-info' : 'text-secondary' }}" href="{{ route('admin.complaints.index', array_merge(request()->except('tab'), ['tab' => 'in_progress'])) }}">
                    In Progress
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-3 {{ $tab === 'pending' ? 'active text-dark' : 'text-secondary' }}" href="{{ route('admin.complaints.index', array_merge(request()->except('tab'), ['tab' => 'pending'])) }}">
                    Pending (Blocked)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-3 {{ $tab === 'resolved' ? 'active text-success' : 'text-secondary' }}" href="{{ route('admin.complaints.index', array_merge(request()->except('tab'), ['tab' => 'resolved'])) }}">
                    Resolved
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-3 {{ $tab === 'closed' ? 'active text-muted' : 'text-secondary' }}" href="{{ route('admin.complaints.index', array_merge(request()->except('tab'), ['tab' => 'closed'])) }}">
                    Closed
                </a>
            </li>
        </ul>
    </div>

    <!-- Complaints Listing Table -->
    <div class="col-12">
        <div class="chart-card">
            <div class="table-responsive">
                <table class="table data-table align-middle">
                    <thead>
                        <tr>
                            <th>Complaint #</th>
                            <th>Date &amp; Time</th>
                            <th>Allottee &amp; Unit</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $c)
                        <tr>
                            <td class="fw-bold text-primary">{{ $c->complaint_number }}</td>
                            <td style="font-size: 11.5px;">{{ $c->created_at->format('d M Y') }}<br><small class="text-muted">{{ $c->created_at->format('h:i A') }}</small></td>
                            <td>
                                <div class="fw-bold">{{ $c->allottee->name ?? 'N/A' }}</div>
                                <small class="text-muted">Blk {{ $c->allottee->block_no ?? '—' }} / Flat {{ $c->allottee->flat_no ?? '—' }}</small>
                            </td>
                            <td class="fw-bold" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $c->subject }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $c->category->name ?? 'Unknown' }}</span></td>
                            <td>
                                <span class="badge {{ $c->priority_badge_class }}">
                                    {{ strtoupper($c->priority) }}
                                </span>
                            </td>
                            <td>
                                @if($c->assignedStaff)
                                    <div class="fw-600 text-dark"><i class="bi bi-person-circle text-success me-1"></i>{{ $c->assignedStaff->name }}</div>
                                    <small class="text-muted" style="font-size: 10px;">{{ $c->assignedStaff->designation }}</small>
                                @else
                                    <span class="text-muted"><i class="bi bi-dash-circle-dotted me-1"></i>Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $c->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $c->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.complaints.show', $c) }}" class="btn btn-sm btn-outline-success fw-bold" style="font-size: 11.5px;"><i class="bi bi-eye me-1"></i>View Details</a>
                                
                                @if(in_array(auth()->user()->role, ['super_admin', 'data_entry']) && $c->status === 'new')
                                    <button class="btn btn-sm btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#assignModal{{ $c->id }}" style="font-size: 11.5px; background: #1B6B35; border:none; margin-left: 2px;"><i class="bi bi-person-plus"></i> Assign</button>
                                @endif
                            </td>
                        </tr>

                        <!-- Quick Assign Modal -->
                        @if(in_array(auth()->user()->role, ['super_admin', 'data_entry']) && $c->status === 'new')
                        <div class="modal fade" id="assignModal{{ $c->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content" style="border-radius: 12px; border: none;">
                                    <form action="{{ route('admin.complaints.assign', $c) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">Assign Complaint: {{ $c->complaint_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-start">
                                            <div class="mb-3">
                                                <label class="form-label" style="font-size: 12.5px; font-weight: 600;">Subject</label>
                                                <input type="text" class="form-control bg-light" value="{{ $c->subject }}" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" style="font-size: 12.5px; font-weight: 600;">Category</label>
                                                <input type="text" class="form-control bg-light" value="{{ $c->category->name ?? 'Unknown' }}" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" style="font-size: 12.5px; font-weight: 600;">Assign Maintenance Staff</label>
                                                <select name="assigned_staff_id" class="form-select" required>
                                                    <option value="" disabled selected>-- Select Technician --</option>
                                                    @foreach($staffMembers as $member)
                                                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->designation }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" style="font-size: 12.5px; font-weight: 600;">Assignment Instructions</label>
                                                <textarea name="remarks" class="form-control" rows="3" placeholder="Add specific instructions for the technician..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success fw-bold">Assign Technician</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No complaints match the filter criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $complaints->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
