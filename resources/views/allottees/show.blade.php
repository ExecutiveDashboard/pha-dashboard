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
        <a href="{{ route('bills.challan', $allottee) }}" class="btn btn-sm btn-primary" target="_blank">
            <i class="bi bi-printer-fill me-1"></i>Generate Bank Challan (4-Part)
        </a>
        <a href="{{ route('allottees.edit', $allottee) }}" class="btn btn-sm btn-outline-dark ms-auto">
            <i class="bi bi-pencil-square me-1"></i>Edit Allottee Data
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
                        <td class="text-muted">Father/Spouse</td>
                        <td>{{ $allottee->father_spouse_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>{{ $allottee->email ?? '—' }}</td>
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
                        <div class="kpi-sub">{{ $allottee->overdue_months ?? 0 }} months overdue</div>
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
                        <label class="form-label" style="font-size:12px;font-weight:600;">Amount Received (Rs.)</label>
                        <input type="number" name="amount_paid" class="form-control" step="0.01" min="0"
                            placeholder="e.g. 5000" required>
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
    {{-- History and Actions Section --}}
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="chart-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-success"></i>History & Property Master</h6>
                    <button class="btn btn-sm btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#transferOwnershipModal">
                        <i class="bi bi-arrow-left-right me-1"></i>Transfer Ownership
                    </button>
                </div>

                <ul class="nav nav-tabs mb-3" id="historyTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-success" id="tenant-tab" data-bs-toggle="tab" data-bs-target="#tenant-pane" type="button" role="tab">
                            Tenant History ({{ $allottee->property ? $allottee->property->tenants()->count() : 0 }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-success" id="ownership-tab" data-bs-toggle="tab" data-bs-target="#ownership-pane" type="button" role="tab">
                            Ownership History ({{ $allottee->property ? \App\Models\Allottee::withoutGlobalScopes()->where('property_id', $allottee->property_id)->count() : 1 }})
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="historyTabContent">
                    {{-- Tenant History Pane --}}
                    <div class="tab-pane fade show active" id="tenant-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" style="font-size:12px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tenant Name</th>
                                        <th>CNIC</th>
                                        <th>Mobile No.</th>
                                        <th>Agreement No.</th>
                                        <th>Start Date</th>
                                        <th>Expiry Date</th>
                                        <th>Rent / Deposit</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $tenantHistory = $allottee->property ? $allottee->property->tenants()->orderByDesc('created_at')->get() : collect();
                                    @endphp
                                    @forelse($tenantHistory as $tenant)
                                        <tr>
                                            <td><strong>{{ $tenant->tenant_name }}</strong></td>
                                            <td>{{ $tenant->tenant_cnic }}</td>
                                            <td>{{ $tenant->mobile_no }}</td>
                                            <td>{{ $tenant->agreement_no }}</td>
                                            <td>{{ $tenant->agreement_start_date?->format('d M Y') ?? '—' }}</td>
                                            <td>{{ $tenant->agreement_expiry_date?->format('d M Y') ?? '—' }}</td>
                                            <td>
                                                Rs. {{ number_format($tenant->monthly_rent ?? 0) }} / 
                                                Rs. {{ number_format($tenant->security_deposit ?? 0) }}
                                            </td>
                                            <td>
                                                <span class="badge {{ $tenant->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $tenant->is_active ? 'Active' : 'Archived' }}
                                                </span>
                                            </td>
                                            <td>{{ $tenant->remarks ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-3 text-muted">No historical tenant records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Ownership History Pane --}}
                    <div class="tab-pane fade" id="ownership-pane" role="tabpanel">
                        @php
                            $allOwnersQuery = \App\Models\Allottee::withoutGlobalScopes()
                                ->where('property_id', $allottee->property_id)
                                ->orderBy('ownership_start_date', 'asc');
                            
                            $allOwners = $allOwnersQuery->get();
                            $totalOwners = $allOwners->count();
                            
                            $firstAllotment = $allOwners->first();
                            $lastTransfer = $allOwners->last();
                            
                            $currentOwner = $allOwners->where('status', 'active')->first() ?? $allottee;
                        @endphp
                        
                        <!-- Property Ownership Summary Dashboard -->
                        <div class="row g-2 mb-4 bg-light p-3 border rounded" style="margin: 0;">
                            <div class="col-md-4">
                                <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Current Owner</div>
                                <div class="fw-bold text-success" style="font-size:15px;">{{ $currentOwner->name }}</div>
                                <div style="font-size:11px;color:#64748b;">CNIC: {{ $currentOwner->cnic ?? '—' }}</div>
                            </div>
                            <div class="col-md-2 border-start ps-3">
                                <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Total Owners</div>
                                <div class="fw-bold" style="font-size:18px;color:#0f4423;">{{ $totalOwners }}</div>
                            </div>
                            <div class="col-md-3 border-start ps-3">
                                <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">First Allotment</div>
                                <div class="fw-bold" style="font-size:13px;color:#1e293b;">
                                    {{ $firstAllotment && $firstAllotment->ownership_start_date ? $firstAllotment->ownership_start_date->format('d-M-Y') : '—' }}
                                </div>
                            </div>
                            <div class="col-md-3 border-start ps-3">
                                <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Last Transfer</div>
                                <div class="fw-bold" style="font-size:13px;color:#1e293b;">
                                    {{ $lastTransfer && $lastTransfer->ownership_start_date ? $lastTransfer->ownership_start_date->format('d-M-Y') : '—' }}
                                </div>
                                <div style="font-size:10px;color:#64748b;">Since: {{ $currentOwner->ownership_start_date ? $currentOwner->ownership_start_date->format('d-M-Y') : '—' }}</div>
                            </div>
                            <div class="col-12 mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size:11.5px;color:#475569;">
                                <div><strong>Occupancy:</strong> <span class="badge {{ $currentOwner->occupancy_status === 'tenant_occupied' ? 'bg-warning text-dark' : 'bg-success' }}">{{ $currentOwner->occupancy_status === 'tenant_occupied' ? 'Tenant Occupied' : 'Owner Occupied' }}</span></div>
                                @if($currentOwner->activeTenant())
                                    <div><strong>Tenant:</strong> {{ $currentOwner->activeTenant()->tenant_name }} (CNIC: {{ $currentOwner->activeTenant()->tenant_cnic }})</div>
                                @endif
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" style="font-size:11.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Owner Name</th>
                                        <th>CNIC</th>
                                        <th>Cell No</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Transfer Type</th>
                                        <th>Reference No.</th>
                                        <th>Approval Date</th>
                                        <th>Possession Handover</th>
                                        <th class="text-end">Balance at Transfer</th>
                                        <th class="text-center">Balance Transferred</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allOwners->sortByDesc('ownership_start_date') as $owner)
                                        @php
                                            $transfer = \App\Models\PropertyOwnershipHistory::where('new_owner_id', $owner->id)->first();
                                            $transferFrom = \App\Models\PropertyOwnershipHistory::where('previous_owner_id', $owner->id)->first();
                                            
                                            $appDate = $transfer ? $transfer->transfer_approval_date?->format('d M Y') : '—';
                                            $posDate = $transfer ? $transfer->possession_handover_date?->format('d M Y') : '—';
                                            $balance = $transferFrom ? 'Rs. ' . number_format($transferFrom->outstanding_balance_at_transfer, 2) : '—';
                                            $balStatus = $transferFrom ? ucfirst($transferFrom->balance_transfer_status) : '—';
                                        @endphp
                                        <tr class="{{ $owner->id === $allottee->id ? 'table-success' : '' }}">
                                            <td><strong>{{ $owner->name }}</strong></td>
                                            <td>{{ $owner->cnic }}</td>
                                            <td>{{ $owner->cell }}</td>
                                            <td>{{ $owner->ownership_start_date?->format('d M Y') ?? '—' }}</td>
                                            <td>{{ $owner->ownership_end_date?->format('d M Y') ?? ($owner->status === 'active' ? 'Present' : '—') }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $owner->transfer_type ? ucfirst($owner->transfer_type) : 'Original Allotment' }}</span></td>
                                            <td>{{ $owner->transfer_ref_no ?? '—' }}</td>
                                            <td>{{ $appDate }}</td>
                                            <td>{{ $posDate }}</td>
                                            <td class="text-end fw-bold">{{ $balance }}</td>
                                            <td class="text-center">{{ $balStatus }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $owner->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $owner->status === 'active' ? 'Current Owner' : 'Previous Owner' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Transfer Ownership Modal --}}
    <div class="modal fade" id="transferOwnershipModal" tabindex="-1" aria-labelledby="transferOwnershipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header bg-danger text-white py-3" style="border-radius:12px 12px 0 0;">
                    <h5 class="modal-title fw-bold" id="transferOwnershipModalLabel"><i class="bi bi-arrow-left-right me-2"></i>Transfer Property Ownership</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('allottees.transfer', $allottee) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-warning border-0" style="background:#fffcf0; color:#b45309; border-radius:8px;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Warning:</strong> Initiating this transfer will archive the current owner record (<strong>{{ $allottee->name }}</strong>) and assign ownership of flat <strong>{{ $allottee->flat_no }}</strong> to the new allottee. Outstanding balances will be governed by system configuration rules.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:12px;">New Owner Full Name</label>
                                <input type="text" name="new_owner_name" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:12px;">Father / Husband / Spouse Name</label>
                                <input type="text" name="new_owner_father_spouse" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">New Owner CNIC</label>
                                <input type="text" name="new_owner_cnic" class="form-control form-control-sm" placeholder="xxxxx-xxxxxxx-x" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">New Owner Cell No.</label>
                                <input type="text" name="new_owner_cell" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">New Owner Email</label>
                                <input type="email" name="new_owner_email" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">Transfer Type</label>
                                <select name="transfer_type" class="form-select form-select-sm" required>
                                    <option value="transfer">Transfer</option>
                                    <option value="sale">Sale</option>
                                    <option value="cancellation">Cancellation</option>
                                    <option value="reallotment">Re-allotment</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">Transfer Date</label>
                                <input type="date" name="transfer_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">Effective Date</label>
                                <input type="date" name="effective_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">Transfer Approval Date</label>
                                <input type="date" name="transfer_approval_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">Possession Handover Date</label>
                                <input type="date" name="possession_handover_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:12px;">Transfer Reference Number / Letter No.</label>
                                <input type="text" name="transfer_ref_no" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" style="font-size:12px;">Remarks / Directives</label>
                                <textarea name="remarks" class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger fw-bold"><i class="bi bi-check-circle me-1"></i>Confirm & Complete Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection