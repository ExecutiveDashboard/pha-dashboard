@extends('layouts.app')
@section('title', 'Monthly Bills')
@section('page-title', 'Monthly Bill Management')

@section('content')

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-blue"><i class="bi bi-calendar-check"></i></div>
            <div class="kpi-value">{{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</div>
            <div class="kpi-label">Selected Month</div>
            <form class="d-flex mt-2" method="GET" action="{{ route('monthly-bills.index') }}">
                <input type="month" name="month" class="form-control form-control-sm me-2" value="{{ $selectedMonth }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-purple"><i class="bi bi-receipt"></i></div>
            <div class="kpi-value">{{ number_format($billCount) }}</div>
            <div class="kpi-label">Bills Generated</div>
            <div class="kpi-sub">Total billed: Rs. {{ number_format($totalAmount) }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-green"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-value">{{ number_format($paidCount) }}</div>
            <div class="kpi-label">Paid / Settled</div>
            <div class="kpi-sub">Total collected: Rs. {{ number_format($paidAmount) }}</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-red"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div class="kpi-value">{{ number_format($unpaidCount) }}</div>
            <div class="kpi-label">Unpaid</div>
            <div class="kpi-sub">Remaining: Rs. {{ number_format($totalAmount - $paidAmount) }}</div>
        </div>
    </div>
</div>

<div class="chart-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="section-title mb-0"><i class="bi bi-list-columns-reverse me-2"></i>Bills for {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</h6>
        <div>
            @if($billCount === 0)
                <form action="{{ route('monthly-bills.generate') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-magic me-2"></i>Generate Bills for {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</button>
                </form>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table data-table mb-0" style="font-size: 12px;">
            <thead>
                <tr>
                    <th>Allottee</th>
                    <th>File No.</th>
                    <th>PSID</th>
                    <th class="text-end">Maint. (Rs.)</th>
                    <th class="text-end">W&W (Rs.)</th>
                    <th class="text-end">Fine (Rs.)</th>
                    <th class="text-end">Total (Rs.)</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                    <tr>
                        <td>
                            <strong>{{ $bill->allottee->name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">Blk {{ $bill->allottee->block_no }} / Flt {{ $bill->allottee->flat_no }}</small>
                        </td>
                        <td>{{ $bill->allottee->file_no }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $bill->psid }}</span></td>
                        <td class="text-end">{{ number_format($bill->maintenance_amount) }}</td>
                        <td class="text-end">{{ number_format($bill->ww_amount) }}</td>
                        <td class="text-end text-danger">{{ number_format($bill->fine_amount) }}</td>
                        <td class="text-end fw-bold">{{ number_format($bill->total_amount) }}</td>
                        <td>
                            <span class="badge bg-{{ $bill->status_color }}">{{ strtoupper($bill->status) }}</span>
                            @if($bill->is_locked) <i class="bi bi-lock-fill text-muted ms-1" title="Locked"></i> @endif
                        </td>
                        <td class="text-center">
                            @if(!$bill->is_locked)
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#payModal{{ $bill->id }}" title="Record Payment"><i class="bi bi-cash"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" data-bs-toggle="modal" data-bs-target="#settleModal{{ $bill->id }}" title="Manual Settle"><i class="bi bi-check2-all"></i></button>
                            @else
                                <button type="button" class="btn btn-sm btn-secondary py-0 px-2 disabled"><i class="bi bi-lock-fill"></i> Locked</button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="checkPsid({{ $bill->id }})" title="Simulate 1Bill/PSID Check"><i class="bi bi-arrow-repeat"></i> PSID</button>
                        </td>
                    </tr>

                    <!-- Payment Modal -->
                    <div class="modal fade" id="payModal{{ $bill->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('monthly-bills.pay', $bill->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Record Payment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info py-2" style="font-size:12px;">Recording payment for <strong>{{ $bill->allottee->name }}</strong> — {{ $bill->bill_month_label }}</div>
                                        <div class="mb-3">
                                            <label class="form-label">Total Due (Rs.)</label>
                                            <input type="text" class="form-control bg-light" value="{{ number_format($bill->amount_due) }}" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Amount Paid (Rs.)</label>
                                            <input type="number" step="0.01" name="paid_amount" class="form-control" value="{{ $bill->amount_due }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Payment Mode</label>
                                            <select name="payment_mode" class="form-select" required>
                                                <option value="psid">1Bill / PSID</option>
                                                <option value="cash">Cash</option>
                                                <option value="online">Online Transfer</option>
                                                <option value="cheque">Cheque</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Payment Date</label>
                                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Reference No. (Optional)</label>
                                            <input type="text" name="payment_ref" class="form-control" placeholder="Transaction ID or Cheque No">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Save Payment</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Settle Modal -->
                    <div class="modal fade" id="settleModal{{ $bill->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('monthly-bills.settle', $bill->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title text-info">Manual Settlement (Admin Override)</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-warning py-2" style="font-size:12px;">This will mark the bill as <strong>SETTLED</strong> and lock it. Only use this if the payment was resolved outside the standard system or waived by authority.</div>
                                        <div class="mb-3">
                                            <label class="form-label">Reason / Authority Note</label>
                                            <textarea name="settled_note" class="form-control" rows="3" required placeholder="e.g. Approved by Director via letter ref #123"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-info text-white">Confirm Settlement</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No bills generated for {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }} yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">
        {{ $bills->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- PSID Check Modal -->
<div class="modal fade" id="psidModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content text-center">
            <div class="modal-body p-4">
                <div id="psidLoading">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h6>Contacting 1Bill/1Link API...</h6>
                    <p class="text-muted small">Checking PSID <span id="psidRef" class="fw-bold"></span></p>
                </div>
                <div id="psidResult" class="d-none">
                    <i id="psidIcon" class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3" id="psidStatusText">PAID</h5>
                    <p class="text-muted small" id="psidMessage"></p>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function checkPsid(billId) {
        const modal = new bootstrap.Modal(document.getElementById('psidModal'));
        document.getElementById('psidLoading').classList.remove('d-none');
        document.getElementById('psidResult').classList.add('d-none');
        document.getElementById('psidRef').innerText = '...';
        modal.show();

        fetch(`/monthly-bills/${billId}/check-psid`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('psidRef').innerText = data.psid;
                
                // Simulate API delay for presentation
                setTimeout(() => {
                    document.getElementById('psidLoading').classList.add('d-none');
                    document.getElementById('psidResult').classList.remove('d-none');
                    
                    document.getElementById('psidStatusText').innerText = data.status;
                    document.getElementById('psidMessage').innerText = data.message;
                    
                    if(data.status === 'PAID') {
                        document.getElementById('psidIcon').className = 'bi bi-check-circle-fill text-success';
                        document.getElementById('psidStatusText').className = 'mt-3 text-success';
                    } else {
                        document.getElementById('psidIcon').className = 'bi bi-clock-history text-warning';
                        document.getElementById('psidStatusText').className = 'mt-3 text-warning';
                    }
                }, 1500);
            })
            .catch(err => {
                console.error(err);
                modal.hide();
                alert('Error checking PSID status.');
            });
    }
</script>
@endpush
