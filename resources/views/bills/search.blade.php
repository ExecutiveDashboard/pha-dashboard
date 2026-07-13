@extends('layouts.app')
@section('title', 'Bill Search')
@section('page-title', 'Quick Bill Search')

@section('content')

{{-- Search Box --}}
<div class="chart-card mb-4" style="border:2px solid #1B6B35;">
    <h6 style="font-size:15px;font-weight:800;color:#1B6B35;margin-bottom:4px;">
        <i class="bi bi-search me-2"></i>Search Bill by CNIC / Mobile / Name / File No.
    </h6>
    <p style="font-size:12px;color:#64748b;margin-bottom:16px;">
        Enter any of the following to find an allottee's payment detail instantly.
    </p>
    <form method="GET" action="{{ route('bills.search') }}" class="row g-2 align-items-end">
        <div class="col-md-8">
            <label class="form-label" style="font-size:12px;font-weight:600;">Search Query</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f0f9f4;border-color:#1B6B35;">
                    <i class="bi bi-search" style="color:#1B6B35;"></i>
                </span>
                <input type="text" name="q" id="q" class="form-control form-control-lg"
                       placeholder="Enter CNIC (e.g. 61101-1234567-1), Mobile, Name, or File No..."
                       value="{{ $q }}" autofocus
                       style="border-color:#1B6B35;font-size:14px;">
            </div>
            <small style="font-size:11px;color:#94a3b8;" class="mt-1 d-block">
                Minimum 3 characters required. You can search by: <strong>CNIC, Mobile Number, Full Name, File Number, Membership Number</strong>
            </small>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn w-100 btn-lg" style="background:#1B6B35;color:#fff;font-weight:700;height:48px;">
                <i class="bi bi-search me-2"></i>Search
            </button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('bills.search') }}" class="btn btn-outline-secondary w-100 btn-lg" style="height:48px;">
                <i class="bi bi-x-circle me-1"></i>Clear
            </a>
        </div>
    </form>
</div>

@if($searched && $allottees->isEmpty())
<div class="chart-card">
    <div class="text-center py-5">
        <i class="bi bi-search" style="font-size:48px;color:#d1d5db;"></i>
        <h5 style="margin-top:16px;color:#64748b;">No Results Found</h5>
        <p style="color:#94a3b8;font-size:13px;">
            No allottee found matching "<strong>{{ $q }}</strong>".<br>
            Try searching with CNIC, mobile number, or exact file number.
        </p>
    </div>
</div>
@endif

@if($searched && $allottees->isNotEmpty())
{{-- Results --}}
<div class="section-heading">
    <i class="bi bi-people-fill text-success me-2"></i>
    {{ $allottees->count() }} Result(s) for "{{ $q }}"
</div>

<div class="chart-card">
    <div class="table-responsive">
        <table class="table data-table mb-0" style="font-size:12px;">
            <thead>
                <tr>
                    <th>Name / CNIC</th>
                    <th>File No.</th>
                    <th>Category</th>
                    <th>Block / Flat</th>
                    <th>Mobile</th>
                    <th class="text-end">Total Payable</th>
                    <th class="text-end">Amount Paid</th>
                    <th class="text-end">Amount Due</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Due Months</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allottees as $a)
                @php
                    $pending = max(0, (float)$a->total_maintenance_charges - (float)$a->amount_paid);
                    $status  = $a->payment_status;
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:13px;">{{ $a->name ?? 'N/A' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">
                            {{ $a->cnic ?? '—' }}
                            @if($a->occupancy_status === 'tenant_occupied' && ($activeTenant = $a->activeTenant()))
                                <br><span class="text-success" style="font-weight:600;">Tenant: {{ $activeTenant->tenant_name }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-weight:600;">{{ $a->file_no }}</td>
                    <td>
                        <span class="badge {{ $a->category==='B'?'badge-b':'badge-e' }}">Cat-{{ $a->category }}</span>
                        <div style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $a->covered_area }} Sq Ft</div>
                    </td>
                    <td style="font-size:12px;">
                        Blk {{ $a->block_no ?? '—' }}<br>
                        <span style="color:#94a3b8;">Flat {{ $a->flat_no ?? '—' }}</span>
                    </td>
                    <td>{{ $a->cell ?? '—' }}</td>
                    <td class="text-end fw-700" style="color:#1a2332;">
                        Rs. {{ number_format($a->total_maintenance_charges) }}
                    </td>
                    <td class="text-end" style="color:#1B6B35;font-weight:700;">
                        Rs. {{ number_format($a->amount_paid) }}
                    </td>
                    <td class="text-end" style="color:{{ $pending > 0 ? '#dc2626' : '#1B6B35' }};font-weight:800;">
                        Rs. {{ number_format($pending) }}
                    </td>
                    <td class="text-center">
                        @if($status === 'paid')
                            <span class="badge" style="background:#dcfce7;color:#166534;">PAID</span>
                        @elseif($status === 'partial')
                            <span class="badge" style="background:#fef3c7;color:#92400e;">PARTIAL</span>
                        @else
                            <span class="badge" style="background:#fee2e2;color:#dc2626;">UNPAID</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($a->due_months > 0)
                            <span class="badge {{ $a->due_months >= 3 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ $a->due_months }} mo
                            </span>
                        @else
                            <span class="badge bg-success">0</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('bills.show', $a) }}" class="btn btn-sm"
                               style="background:#1B6B35;color:#fff;font-size:11px;">
                                <i class="bi bi-receipt me-1"></i>View Bill
                            </a>
                            <a href="{{ route('bills.pdf', $a) }}" class="btn btn-sm btn-danger"
                               style="font-size:11px;" title="Download PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(!$searched)
{{-- Landing state --}}
<div class="row g-3">
    <div class="col-md-4">
        <div class="chart-card text-center" style="padding:28px 20px;">
            <div style="width:56px;height:56px;border-radius:14px;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#2563eb;">
                <i class="bi bi-credit-card-2-front-fill"></i>
            </div>
            <h6 style="font-weight:700;">Search by CNIC</h6>
            <p style="font-size:12px;color:#64748b;">Enter full or partial CNIC number to find allottee payment details.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card text-center" style="padding:28px 20px;">
            <div style="width:56px;height:56px;border-radius:14px;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#1B6B35;">
                <i class="bi bi-phone-fill"></i>
            </div>
            <h6 style="font-weight:700;">Search by Mobile</h6>
            <p style="font-size:12px;color:#64748b;">Search using the registered mobile number of the allottee.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card text-center" style="padding:28px 20px;">
            <div style="width:56px;height:56px;border-radius:14px;background:#ede9fe;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#7c3aed;">
                <i class="bi bi-folder2-open"></i>
            </div>
            <h6 style="font-weight:700;">Search by File / Reg. No.</h6>
            <p style="font-size:12px;color:#64748b;">Enter the file number or registration number to locate the allottee.</p>
        </div>
    </div>
</div>
@endif

@endsection
