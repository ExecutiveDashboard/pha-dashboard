@extends('layouts.app')
@section('title', $allottee->name)
@section('page-title', 'Allottee Detail')

@section('content')
    <div class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <a href="{{ route('allottees.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to List
        </a>
        <a href="{{ route('bills.show', $allottee) }}" class="btn btn-sm"
            style="background:#1B6B35;color:#fff;font-weight:600;">
            <i class="bi bi-receipt me-1"></i>View Bill
        </a>
        <a href="{{ route('bills.pdf', $allottee) }}" class="btn btn-sm btn-danger">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i>Download PDF
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="text-center mb-3">
                    <div
                        style="width:72px;height:72px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px;color:#1B6B35;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 style="font-weight:700;">{{ $allottee->name ?? 'N/A' }}</h5>
                    <span class="badge {{ $allottee->category === 'B' ? 'badge-b' : 'badge-e' }} mb-2">Category
                        {{ $allottee->category }}</span>
                </div>
                <table class="table table-sm" style="font-size:13px;">
                    <tr>
                        <td class="text-muted">File No</td>
                        <td><strong>{{ $allottee->file_no }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Membership</td>
                        <td>{{ $allottee->membership_no ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">CNIC</td>
                        <td>{{ $allottee->cnic ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">BPS Grade</td>
                        <td>{{ $allottee->bps ? 'BPS-' . $allottee->bps : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cell</td>
                        <td>{{ $allottee->cell ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Office</td>
                        <td>{{ $allottee->office_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Post</td>
                        <td>{{ $allottee->post_held ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">City</td>
                        <td>{{ $allottee->city ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Possession Date</td>
                        <td>{{ $allottee->possession_date?->format('d M Y') ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div class="kpi-card">
                        <div class="kpi-icon icon-amber"><i class="bi bi-currency-rupee"></i></div>
                        <div class="kpi-value">Rs. {{ number_format($allottee->maintenance_charges) }}</div>
                        <div class="kpi-label">Maintenance Charges</div>
                        <div class="kpi-sub">{{ $allottee->covered_area }} Sq Ft × {{ $allottee->due_months }} months</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card">
                        <div class="kpi-icon icon-purple"><i class="bi bi-shield-fill"></i></div>
                        <div class="kpi-value">Rs. {{ number_format($allottee->watch_ward_charges) }}</div>
                        <div class="kpi-label">Watch & Ward Charges</div>
                        <div class="kpi-sub">Security charges applicable</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card">
                        <div class="kpi-icon icon-red"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div class="kpi-value">Rs. {{ number_format($allottee->fine) }}</div>
                        <div class="kpi-label">Delay Charges (10%)</div>
                        <div class="kpi-sub">Fine on late payment</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card" style="border: 2px solid #1B6B35;">
                        <div class="kpi-icon icon-green"><i class="bi bi-bank"></i></div>
                        <div class="kpi-value" style="color:#1B6B35;">Rs.
                            {{ number_format($allottee->total_maintenance_charges) }}</div>
                        <div class="kpi-label">Total Payable</div>
                        <div class="kpi-sub">{{ $allottee->due_months ?? 0 }} months overdue</div>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <h6>Additional Information</h6>
                <table class="table table-sm" style="font-size:13px;">
                    <tr>
                        <td class="text-muted">Block No</td>
                        <td>{{ $allottee->block_no ?? '—' }}</td>
                        <td class="text-muted">Floor</td>
                        <td>{{ $allottee->floor ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Flat No</td>
                        <td>{{ $allottee->flat_no ?? '—' }}</td>
                        <td class="text-muted">Area</td>
                        <td>{{ $allottee->covered_area }} Sq Ft</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cadre Group</td>
                        <td>{{ $allottee->cadre_group ?? '—' }}</td>
                        <td class="text-muted">Balloting</td>
                        <td>{{ $allottee->balloting_fcfs ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">PAL Issued</td>
                        <td>{{ $allottee->pal ?? '—' }}</td>
                        <td class="text-muted">Transfer</td>
                        <td>{{ $allottee->transfer ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Mailing Address</td>
                        <td colspan="3">{{ $allottee->mailing_address ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Payment Recording (Admin) --}}
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="chart-card">
                <h6><i class="bi bi-cash-coin me-2" style="color:#1B6B35;"></i>Record Payment</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div style="background:#dcfce7;border-radius:10px;padding:12px;text-align:center;">
                            <div style="font-size:11px;font-weight:600;color:#166534;">AMOUNT PAID</div>
                            <div style="font-size:20px;font-weight:800;color:#1B6B35;">Rs.
                                {{ number_format($allottee->amount_paid) }}</div>
                            @if($allottee->payment_date)
                                <div style="font-size:11px;color:#166534;">{{ $allottee->payment_date->format('d M Y') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="background:#fee2e2;border-radius:10px;padding:12px;text-align:center;">
                            <div style="font-size:11px;font-weight:600;color:#991b1b;">AMOUNT PENDING</div>
                            <div style="font-size:20px;font-weight:800;color:#dc2626;">Rs.
                                {{ number_format($allottee->amount_pending) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="background:#f0f4f8;border-radius:10px;padding:12px;text-align:center;">
                            <div style="font-size:11px;font-weight:600;color:#64748b;">PAYMENT MODE</div>
                            <div style="font-size:16px;font-weight:700;color:#1a2332;">
                                {{ $allottee->payment_mode ? ucfirst($allottee->payment_mode) : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="background:#f0f4f8;border-radius:10px;padding:12px;text-align:center;">
                            <div style="font-size:11px;font-weight:600;color:#64748b;">PAYMENT REF</div>
                            <div style="font-size:13px;font-weight:700;color:#1a2332;">{{ $allottee->payment_ref ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('allottees.payment', $allottee) }}" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Amount Paid (Rs.)</label>
                        <input type="number" name="amount_paid" class="form-control" step="0.01" min="0"
                            value="{{ $allottee->amount_paid }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Payment Mode</label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="cash" {{ $allottee->payment_mode == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="online" {{ $allottee->payment_mode == 'online' ? 'selected' : '' }}>Online Transfer
                            </option>
                            <option value="cheque" {{ $allottee->payment_mode == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control"
                            value="{{ $allottee->payment_date?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Reference No.</label>
                        <input type="text" name="payment_ref" class="form-control" value="{{ $allottee->payment_ref }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn w-100"
                            style="background:#1B6B35;color:#fff;font-weight:600;font-size:13px;">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection