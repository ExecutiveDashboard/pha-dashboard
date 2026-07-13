@extends('layouts.app')
@section('title', 'Edit Allottee Profile')
@section('page-title', 'Edit Profile: ' . $allottee->name)

@section('content')
@php
    $isReadOnly = $allottee->status === 'inactive';
@endphp

<div class="mb-3">
    <a href="{{ route('allottees.show', $allottee) }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Profile</a>
</div>

@if($isReadOnly)
<div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius:10px;">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
        <div>
            <h6 class="fw-bold mb-1">Historical Ownership Record (Read-Only)</h6>
            <p class="mb-0 text-muted" style="font-size:12.5px;">This profile represents a previous owner and is locked for audit compliance. Historical records cannot be modified.</p>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3" style="border-radius:12px 12px 0 0;">
        <h5 class="mb-0 fw-bold" style="color:#1B6B35;"><i class="bi bi-pencil-square me-2"></i>Edit Allottee Data</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('allottees.update', $allottee) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- SECTION 1: Personal Details --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3" style="color:#0f4423;border-bottom:1px solid #e2e8f0;padding-bottom:5px;">Personal Details</h6>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $allottee->name) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Father/Husband/Spouse Name</label>
                        <input type="text" name="father_spouse_name" class="form-control" value="{{ old('father_spouse_name', $allottee->father_spouse_name) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">CNIC</label>
                        <input type="text" name="cnic" class="form-control" value="{{ old('cnic', $allottee->cnic) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $allottee->email) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Mobile / Cell</label>
                        <input type="text" name="cell" class="form-control" value="{{ old('cell', $allottee->cell) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Basic Pay Scale (BPS)</label>
                        <input type="text" name="bps" class="form-control" value="{{ old('bps', $allottee->bps) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Mailing Address</label>
                        <textarea name="mailing_address" class="form-control" rows="3" {{ $isReadOnly ? 'disabled' : '' }}>{{ old('mailing_address', $allottee->mailing_address) }}</textarea>
                    </div>
                </div>

                {{-- SECTION 2: Property & Charges --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3" style="color:#0f4423;border-bottom:1px solid #e2e8f0;padding-bottom:5px;">Property & Charges</h6>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Category</label>
                            <select name="category" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                                <option value="B" {{ old('category', $allottee->category) == 'B' ? 'selected' : '' }}>Category B</option>
                                <option value="E" {{ old('category', $allottee->category) == 'E' ? 'selected' : '' }}>Category E</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Possession Date</label>
                            <input type="date" name="possession_date" class="form-control" value="{{ old('possession_date', $allottee->possession_date ? $allottee->possession_date->format('Y-m-d') : '') }}" {{ $isReadOnly ? 'disabled' : '' }}>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Block No.</label>
                            <input type="text" name="block_no" class="form-control" value="{{ old('block_no', $allottee->block_no) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Flat No.</label>
                            <input type="text" name="flat_no" class="form-control" value="{{ old('flat_no', $allottee->flat_no) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mt-4 p-3" style="border-radius:10px;">
                        <h6 class="fw-bold mb-3" style="font-size:13px;color:#d97706;"><i class="bi bi-plus-circle me-1"></i>Extra Charges</h6>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="has_parking" id="has_parking" value="1" {{ old('has_parking', $allottee->has_parking) ? 'checked' : '' }} {{ $isReadOnly ? 'disabled' : '' }}>
                                <label class="form-check-label fw-bold" for="has_parking">Enable Parking Charges</label>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="parking_charges" class="form-control" placeholder="Enter custom amount or leave default" value="{{ old('parking_charges', $allottee->parking_charges) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                            </div>
                            <small class="text-muted" style="font-size:10px;">If 0.00, it will use global default parking rate.</small>
                        </div>

                        <div class="mb-2">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="has_water" id="has_water" value="1" {{ old('has_water', $allottee->has_water) ? 'checked' : '' }} {{ $isReadOnly ? 'disabled' : '' }}>
                                <label class="form-check-label fw-bold" for="has_water">Enable Water Charges</label>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="water_charges" class="form-control" placeholder="Enter custom amount or leave default" value="{{ old('water_charges', $allottee->water_charges) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                            </div>
                            <small class="text-muted" style="font-size:10px;">If 0.00, it will use global default water rate.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: Occupancy & Tenant Details --}}
            <div class="row mt-4 pt-3 border-top">
                <div class="col-12">
                    <h6 class="fw-bold mb-3" style="color:#0f4423;border-bottom:1px solid #e2e8f0;padding-bottom:5px;">Occupancy Details</h6>
                    
                    <div class="mb-3 col-md-4">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Occupancy Status</label>
                        <select name="occupancy_status" id="occupancy_status" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                            <option value="owner_occupied" {{ old('occupancy_status', $allottee->occupancy_status) == 'owner_occupied' ? 'selected' : '' }}>Owner Occupied</option>
                            <option value="tenant_occupied" {{ old('occupancy_status', $allottee->occupancy_status) == 'tenant_occupied' ? 'selected' : '' }}>Tenant Occupied</option>
                        </select>
                    </div>

                    @php
                        $activeTenant = $allottee->activeTenant();
                    @endphp

                    <div id="tenant_details_section" style="display: {{ old('occupancy_status', $allottee->occupancy_status) == 'tenant_occupied' ? 'block' : 'none' }};">
                        <div class="card border-0 bg-light p-4 mb-3" style="border-radius:10px;">
                            <h6 class="fw-bold mb-3 text-success"><i class="bi bi-people-fill me-2"></i>Tenant Information</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Tenant Name</label>
                                    <input type="text" name="tenant_name" class="form-control" value="{{ old('tenant_name', $activeTenant ? $activeTenant->tenant_name : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Tenant CNIC</label>
                                    <input type="text" name="tenant_cnic" class="form-control" placeholder="xxxxx-xxxxxxx-x" value="{{ old('tenant_cnic', $activeTenant ? $activeTenant->tenant_cnic : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Father / Husband / Spouse Name</label>
                                    <input type="text" name="spouse_name" class="form-control" value="{{ old('spouse_name', $activeTenant ? $activeTenant->spouse_name : '') }}">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Tenant Mobile No.</label>
                                    <input type="text" name="mobile_no" class="form-control" value="{{ old('mobile_no', $activeTenant ? $activeTenant->mobile_no : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Alternate Contact No.</label>
                                    <input type="text" name="alternate_contact_no" class="form-control" value="{{ old('alternate_contact_no', $activeTenant ? $activeTenant->alternate_contact_no : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Tenant Email (optional)</label>
                                    <input type="email" name="tenant_email" class="form-control" value="{{ old('tenant_email', $activeTenant ? $activeTenant->tenant_email : '') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Current Address</label>
                                    <input type="text" name="current_address" class="form-control" value="{{ old('current_address', $activeTenant ? $activeTenant->current_address : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Permanent Address</label>
                                    <input type="text" name="permanent_address" class="form-control" value="{{ old('permanent_address', $activeTenant ? $activeTenant->permanent_address : '') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Agreement Number</label>
                                    <input type="text" name="agreement_no" class="form-control" value="{{ old('agreement_no', $activeTenant ? $activeTenant->agreement_no : '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Agreement Date</label>
                                    <input type="date" name="agreement_date" class="form-control" value="{{ old('agreement_date', $activeTenant && $activeTenant->agreement_date ? $activeTenant->agreement_date->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Agreement Start Date</label>
                                    <input type="date" name="agreement_start_date" class="form-control" value="{{ old('agreement_start_date', $activeTenant && $activeTenant->agreement_start_date ? $activeTenant->agreement_start_date->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Agreement Expiry Date</label>
                                    <input type="date" name="agreement_expiry_date" class="form-control" value="{{ old('agreement_expiry_date', $activeTenant && $activeTenant->agreement_expiry_date ? $activeTenant->agreement_expiry_date->format('Y-m-d') : '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Duration of Stay</label>
                                    <input type="text" name="duration_of_stay" class="form-control" placeholder="e.g. 1 Year" value="{{ old('duration_of_stay', $activeTenant ? $activeTenant->duration_of_stay : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Monthly Rent (Rs. - optional)</label>
                                    <input type="number" name="monthly_rent" class="form-control" value="{{ old('monthly_rent', $activeTenant ? $activeTenant->monthly_rent : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Security Deposit (Rs. - optional)</label>
                                    <input type="number" name="security_deposit" class="form-control" value="{{ old('security_deposit', $activeTenant ? $activeTenant->security_deposit : '') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Emergency Contact Person</label>
                                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $activeTenant ? $activeTenant->emergency_contact_name : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Emergency Contact Number</label>
                                    <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $activeTenant ? $activeTenant->emergency_contact_phone : '') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" style="font-size:12px;font-weight:600;">Remarks / Notes</label>
                                    <textarea name="tenant_remarks" class="form-control" rows="2">{{ old('tenant_remarks', $activeTenant ? $activeTenant->remarks : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-end">
                <a href="{{ route('allottees.show', $allottee) }}" class="btn btn-light border fw-bold me-2">Cancel</a>
                @if(!$isReadOnly)
                <button type="submit" class="btn btn-success fw-bold px-4" style="background:#1B6B35;">Save Changes</button>
                @endif
            </div>
        </form>

        {{-- SECTION 4: Ownership History (Read-Only) --}}
        <div class="row mt-5 pt-3 border-top">
            <div class="col-12">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-clock-history fs-5 me-2 text-success"></i>
                    <h6 class="fw-bold mb-0" style="color:#0f4423;">Ownership History Timeline</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" style="font-size:12.5px;">
                        <thead style="background:#f8fafc;font-weight:600;">
                            <tr>
                                <th>Owner Name</th>
                                <th>CNIC</th>
                                <th>Transfer Type</th>
                                <th>Ownership From</th>
                                <th>Ownership To</th>
                                <th class="text-end">Balance at Transfer</th>
                                <th class="text-center">Balance Transferred</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $allOwners = \App\Models\Allottee::withoutGlobalScopes()
                                    ->where('property_id', $allottee->property_id)
                                    ->orderBy('ownership_start_date', 'asc')
                                    ->get();
                            @endphp
                            @forelse($allOwners as $owner)
                                @php
                                    $historyRecord = \App\Models\PropertyOwnershipHistory::where('previous_owner_id', $owner->id)->first();
                                    $balance = $historyRecord ? $historyRecord->outstanding_balance_at_transfer : null;
                                    $transferred = $historyRecord ? ($historyRecord->balance_transfer_status === 'transferred' ? 'Yes' : 'No') : '—';
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $owner->name }}</td>
                                    <td>{{ $owner->cnic }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $owner->transfer_type ? ucfirst($owner->transfer_type) : 'Original Allotment' }}</span></td>
                                    <td>{{ $owner->ownership_start_date ? $owner->ownership_start_date->format('d M Y') : '—' }}</td>
                                    <td>{{ $owner->ownership_end_date ? $owner->ownership_end_date->format('d M Y') : ($owner->status === 'active' ? 'Present' : '—') }}</td>
                                    <td class="text-end fw-bold">{{ $balance !== null ? 'Rs. ' . number_format($balance, 2) : '—' }}</td>
                                    <td class="text-center">{{ $transferred }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $owner->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $owner->status === 'active' ? 'Current Owner' : 'Previous Owner' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">No ownership history records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const occupancySelect = document.getElementById('occupancy_status');
        const tenantSection = document.getElementById('tenant_details_section');

        occupancySelect.addEventListener('change', function () {
            if (this.value === 'tenant_occupied') {
                tenantSection.style.display = 'block';
            } else {
                tenantSection.style.display = 'none';
            }
        });
    });
</script>
@endsection
