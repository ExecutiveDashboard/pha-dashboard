@extends('layouts.app')
@section('title', 'Allottee List')
@section('page-title')
    All Allottees — {{ \App\Models\Project::active()?->name ?? 'I-16/3' }}
@endsection
@section('content')

{{-- Filters --}}
<div class="chart-card mb-4">
    <form method="GET" action="{{ route('allottees.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label" style="font-size:12px;font-weight:600;">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, CNIC, File No..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:12px;font-weight:600;">Category</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <option value="B" {{ request('category')=='B'?'selected':'' }}>Category B (1496 Sq Ft)</option>
                <option value="E" {{ request('category')=='E'?'selected':'' }}>Category E (972 Sq Ft)</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:12px;font-weight:600;">City</label>
            <select name="city" class="form-select form-select-sm">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city')==$city?'selected':'' }}>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:12px;font-weight:600;">BPS Grade</label>
            <select name="bps" class="form-select form-select-sm">
                <option value="">All BPS</option>
                @foreach($bpsList as $b)
                    <option value="{{ $b }}" {{ request('bps')==$b?'selected':'' }}>BPS-{{ $b }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:12px;font-weight:600;">Type</label>
            <select name="defaulter" class="form-select form-select-sm">
                <option value="">All Allottees</option>
                <option value="1" {{ request('defaulter')=='1'?'selected':'' }}>Defaulters Only</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-sm w-100" style="background:#1B6B35;color:#fff;">Filter</button>
        </div>
    </form>
</div>

{{-- Stats bar --}}
<div class="d-flex align-items-center gap-3 mb-3">
    <span style="font-size:13px;color:#64748b;">
        Showing <strong>{{ $allottees->total() }}</strong> allottees
    </span>
    @if(request()->hasAny(['search','category','city','bps','defaulter']))
        <a href="{{ route('allottees.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:12px;">
            <i class="bi bi-x-circle me-1"></i>Clear Filters
        </a>
    @endif
</div>

{{-- Table --}}
<div class="chart-card">
    <div class="table-responsive">
        <table class="table data-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>File No.</th>
                    <th>Cat</th>
                    <th>Block/Flat</th>
                    <th>BPS</th>
                    <th>Due Months</th>
                    <th>Maintenance</th>
                    <th>W&W</th>
                    <th>Fine</th>
                    <th>Total Payable</th>
                    <th>City</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($allottees as $a)
                <tr>
                    <td style="color:#94a3b8;font-size:12px;">{{ $allottees->firstItem() + $loop->index }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $a->name ?? '—' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $a->cnic ?? '' }}</div>
                    </td>
                    <td style="font-size:12px;">{{ $a->file_no }}</td>
                    <td><span class="badge {{ $a->category==='B'?'badge-b':'badge-e' }}">{{ $a->category }}</span></td>
                    <td style="font-size:12px;">Blk {{ $a->block_no }}<br><span style="color:#94a3b8;">Flat {{ $a->flat_no }}</span></td>
                    <td style="font-size:12px;">{{ $a->bps ? 'BPS-'.$a->bps : '—' }}</td>
                    <td>
                        @if($a->overdue_months > 0)
                            <span class="badge {{ $a->overdue_months >= 3 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $a->overdue_months }}</span>
                        @else
                            <span class="badge bg-success">0</span>
                        @endif
                    </td>
                    <td style="font-size:13px;">Rs. {{ number_format($a->maintenance_charges) }}</td>
                    <td style="font-size:13px;">Rs. {{ number_format($a->watch_ward_charges) }}</td>
                    <td style="font-size:13px;color:#dc2626;">Rs. {{ number_format($a->fine) }}</td>
                    <td style="font-weight:700;color:#1B6B35;">Rs. {{ number_format($a->total_maintenance_charges) }}</td>
                    <td style="font-size:12px;">{{ $a->city ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('allottees.show', $a) }}" class="btn btn-sm btn-outline-secondary" style="font-size:11px;">View</a>
                            <a href="{{ route('bills.show', $a) }}" class="btn btn-sm" style="background:#1B6B35;color:#fff;font-size:11px;" title="View Bill"><i class="bi bi-receipt"></i></a>
                            <a href="{{ route('bills.pdf', $a) }}" class="btn btn-sm btn-danger" style="font-size:11px;" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="13" class="text-center py-4 text-muted">No allottees found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $allottees->links() }}</div>
</div>

@endsection
