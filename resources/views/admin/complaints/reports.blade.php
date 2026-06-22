@extends('layouts.app')
@section('title', 'CMS Reports')
@section('page-title', 'Complaint System Reports')

@section('content')
<div class="row g-3">
    <!-- Reports Filter Card -->
    <div class="col-12">
        <div class="chart-card">
            <h6 class="section-title mb-3"><i class="bi bi-filter-square-fill me-2 text-success"></i>Report Filter Options</h6>
            <form method="GET" action="{{ route('admin.complaints.reports') }}" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 11px; font-weight: 600;">Housing Project</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">-- All Projects --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 11px; font-weight: 600;">Complaint Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size: 11px; font-weight: 600;">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">-- All Priorities --</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="emergency" {{ request('priority') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size: 11px; font-weight: 600;">Status</label>
                    <select name="status" class="form-select form-select-sm">
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
                <div class="col-md-2">
                    <label class="form-label" style="font-size: 11px; font-weight: 600;">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2 mt-md-2">
                    <label class="form-label" style="font-size: 11px; font-weight: 600;">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-10 mt-md-4 text-end d-flex justify-content-end gap-2 align-items-end">
                    <button type="submit" class="btn btn-sm btn-success fw-bold" style="background:#1B6B35; border:none; padding: 8px 16px;"><i class="bi bi-search me-1"></i>Search Report</button>
                    
                    <button type="button" class="btn btn-sm btn-outline-dark fw-bold" onclick="window.print()" style="padding: 8px 16px;"><i class="bi bi-printer me-1"></i>Print Report</button>
                    
                    <a href="{{ route('admin.complaints.export', request()->all()) }}" class="btn btn-sm btn-outline-success fw-bold" style="padding: 8px 16px;"><i class="bi bi-file-earmark-excel me-1"></i>Export CSV</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Results Table -->
    <div class="col-12">
        <div class="chart-card">
            <h6 class="section-title mb-3 d-print-none"><i class="bi bi-table me-2 text-primary"></i>Report Preview ({{ $complaints->count() }} Records)</h6>
            
            <div class="d-none d-print-block text-center mb-4">
                <h2>PHA Foundation — Ministry of Housing & Works</h2>
                <h4>Complaint System Audit & Analytical Report</h4>
                <p class="text-muted">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
                <hr>
            </div>

            <div class="table-responsive">
                <table class="table data-table align-middle table-sm" style="font-size:11.5px;">
                    <thead>
                        <tr>
                            <th>Complaint #</th>
                            <th>Date</th>
                            <th>Project</th>
                            <th>Block/Flat</th>
                            <th>Allottee Name</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Assigned Staff</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $c)
                        <tr>
                            <td class="fw-bold">{{ $c->complaint_number }}</td>
                            <td>{{ $c->created_at->format('Y-m-d') }}</td>
                            <td>{{ $c->project->name }}</td>
                            <td>Blk: {{ $c->allottee->block_no }} / {{ $c->allottee->flat_no }}</td>
                            <td class="fw-bold">{{ $c->allottee->name }}</td>
                            <td>{{ $c->category->name }}</td>
                            <td>{{ strtoupper($c->priority) }}</td>
                            <td>{{ $c->assignedStaff->name ?? 'Unassigned' }}</td>
                            <td>{{ strtoupper(str_replace('_', ' ', $c->status)) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No records match the filter criteria. Change filters and search again.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: white !important; color: black !important; }
    .sidebar, .topbar, .d-print-none, .btn, .nav-tabs, form { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .chart-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
    .table { width: 100% !important; border-collapse: collapse !important; }
    .table th, .table td { border: 1px solid #000 !important; padding: 6px !important; }
}
</style>
@endsection
