@extends('layouts.app')
@section('title', 'Projects Setup')
@section('page-title', 'Multi-Project Configuration')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="section-title mb-0"><i class="bi bi-buildings-fill me-2 text-primary"></i>Managed Projects</h6>
                    <p class="chart-sub mb-0 mt-1">Switch between different PHAF projects. (Note: Presentation placeholder. Switching updates the active flag but uses the same underlying demo database).</p>
                </div>
                <button class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-2"></i>Add New Project</button>
            </div>

            <div class="table-responsive">
                <table class="table data-table mb-0 align-middle" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Status</th>
                            <th>Project Code</th>
                            <th>Project Name</th>
                            <th>City</th>
                            <th class="text-end">Units</th>
                            <th class="text-end">Maint. Rate</th>
                            <th class="text-end">W&W Amt</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $p)
                            <tr class="{{ $p->is_active ? 'table-success' : '' }}">
                                <td class="text-center">
                                    @if($p->is_active)
                                        <i class="bi bi-circle-fill text-success" title="Active"></i>
                                    @else
                                        <i class="bi bi-circle text-muted" title="Inactive"></i>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ $p->code }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $p->full_name }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $p->description }}</div>
                                </td>
                                <td>{{ $p->city }}</td>
                                <td class="text-end">{{ number_format($p->total_units) }}</td>
                                <td class="text-end">Rs. {{ $p->maintenance_rate }}/sqft</td>
                                <td class="text-end">Rs. {{ number_format($p->ww_amount) }}</td>
                                <td class="text-center">
                                    @if($p->is_active)
                                        <span class="badge bg-success">Currently Active</span>
                                    @else
                                        <form action="{{ route('projects.switch') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="project_id" value="{{ $p->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 11px;">Switch To This</button>
                                        </form>
                                    @endif
                                    <button class="btn btn-sm btn-outline-secondary py-1 px-2 ms-1" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#editBankModal{{ $p->id }}">
                                        <i class="bi bi-bank2"></i> Edit Bank
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-bank2 me-2 text-success"></i>Bank Accounts Configuration</h6>
            <div class="alert alert-light border mb-0" style="font-size: 12px;">
                <strong class="d-block mb-2 text-dark">{{ $projects->firstWhere('is_active', true)->name ?? 'No Active Project' }}</strong>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Bank Name:</span>
                    <span class="fw-bold">{{ $projects->firstWhere('is_active', true)->bank_name ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Account No:</span>
                    <span class="fw-bold text-primary">{{ $projects->firstWhere('is_active', true)->bank_account_no ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Branch:</span>
                    <span>{{ $projects->firstWhere('is_active', true)->bank_branch ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<!-- Modals for editing banks -->
@foreach($projects as $p)
<div class="modal fade" id="editBankModal{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fs-6"><i class="bi bi-bank2 me-2"></i>Edit Bank Details: {{ $p->code }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('projects.update-bank', $p) }}" method="POST">
                @csrf
                <div class="modal-body" style="font-size: 13px;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bank / Gateway Name</label>
                        <select name="bank_name" class="form-select" required>
                            <option value="National Bank of Pakistan" {{ $p->bank_name == 'National Bank of Pakistan' ? 'selected' : '' }}>National Bank of Pakistan</option>
                            <option value="1Bill / Kuickpay" {{ $p->bank_name == '1Bill / Kuickpay' ? 'selected' : '' }}>1Bill / Kuickpay</option>
                            <option value="Raast / QR Code" {{ $p->bank_name == 'Raast / QR Code' ? 'selected' : '' }}>Raast / QR Code</option>
                            <option value="Habib Bank Limited" {{ $p->bank_name == 'Habib Bank Limited' ? 'selected' : '' }}>Habib Bank Limited</option>
                            <option value="Bank Alfalah" {{ $p->bank_name == 'Bank Alfalah' ? 'selected' : '' }}>Bank Alfalah</option>
                            <option value="Meezan Bank" {{ $p->bank_name == 'Meezan Bank' ? 'selected' : '' }}>Meezan Bank</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Account Number / IBAN / Prefix</label>
                        <input type="text" name="bank_account_no" class="form-control" value="{{ $p->bank_account_no }}" required>
                        <small class="text-muted">Enter IBAN for banks, or the PSID/Raast prefix code.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Branch Name (Optional)</label>
                        <input type="text" name="bank_branch" class="form-control" value="{{ $p->bank_branch }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Save Details</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endpush
