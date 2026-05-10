@extends('layouts.app')
@section('title', 'Edit Allottee Profile')
@section('page-title', 'Edit Profile: ' . $allottee->name)

@section('content')
<div class="mb-3">
    <a href="{{ route('allottees.show', $allottee) }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Profile</a>
</div>

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
                        <input type="text" name="name" class="form-control" value="{{ old('name', $allottee->name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">CNIC</label>
                        <input type="text" name="cnic" class="form-control" value="{{ old('cnic', $allottee->cnic) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Mobile / Cell</label>
                        <input type="text" name="cell" class="form-control" value="{{ old('cell', $allottee->cell) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Basic Pay Scale (BPS)</label>
                        <input type="text" name="bps" class="form-control" value="{{ old('bps', $allottee->bps) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Mailing Address</label>
                        <textarea name="mailing_address" class="form-control" rows="3">{{ old('mailing_address', $allottee->mailing_address) }}</textarea>
                    </div>
                </div>

                {{-- SECTION 2: Property & Charges --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3" style="color:#0f4423;border-bottom:1px solid #e2e8f0;padding-bottom:5px;">Property & Charges</h6>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Category</label>
                            <select name="category" class="form-select">
                                <option value="B" {{ old('category', $allottee->category) == 'B' ? 'selected' : '' }}>Category B</option>
                                <option value="E" {{ old('category', $allottee->category) == 'E' ? 'selected' : '' }}>Category E</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Possession Date</label>
                            <input type="date" name="possession_date" class="form-control" value="{{ old('possession_date', $allottee->possession_date ? $allottee->possession_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Block No.</label>
                            <input type="text" name="block_no" class="form-control" value="{{ old('block_no', $allottee->block_no) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Flat No.</label>
                            <input type="text" name="flat_no" class="form-control" value="{{ old('flat_no', $allottee->flat_no) }}">
                        </div>
                    </div>

                    <div class="card border-0 bg-light mt-4 p-3" style="border-radius:10px;">
                        <h6 class="fw-bold mb-3" style="font-size:13px;color:#d97706;"><i class="bi bi-plus-circle me-1"></i>Extra Charges</h6>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="has_parking" id="has_parking" value="1" {{ old('has_parking', $allottee->has_parking) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="has_parking">Enable Parking Charges</label>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="parking_charges" class="form-control" placeholder="Enter custom amount or leave default" value="{{ old('parking_charges', $allottee->parking_charges) }}">
                            </div>
                            <small class="text-muted" style="font-size:10px;">If 0.00, it will use global default parking rate.</small>
                        </div>

                        <div class="mb-2">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="has_water" id="has_water" value="1" {{ old('has_water', $allottee->has_water) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="has_water">Enable Water Charges</label>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="water_charges" class="form-control" placeholder="Enter custom amount or leave default" value="{{ old('water_charges', $allottee->water_charges) }}">
                            </div>
                            <small class="text-muted" style="font-size:10px;">If 0.00, it will use global default water rate.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-end">
                <a href="{{ route('allottees.show', $allottee) }}" class="btn btn-light border fw-bold me-2">Cancel</a>
                <button type="submit" class="btn btn-success fw-bold px-4" style="background:#1B6B35;">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
