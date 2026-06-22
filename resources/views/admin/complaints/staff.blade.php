@extends('layouts.app')
@section('title', 'Maintenance Staff Management')
@section('page-title', 'Manage Maintenance Staff')

@section('content')
<div class="row g-3">
    <!-- Left: Add New Staff Member -->
    <div class="col-lg-4">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Add Staff Member</h6>
            <form action="{{ route('admin.complaints.staff.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Muhammad Ahmad" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Designation</label>
                    <select name="designation" class="form-select" required>
                        <option value="" disabled selected>Select Designation</option>
                        <option value="Maintenance Supervisor">Maintenance Supervisor</option>
                        <option value="Electrician">Electrician</option>
                        <option value="Plumber">Plumber</option>
                        <option value="Civil Technician">Civil Technician</option>
                        <option value="Carpenter">Carpenter</option>
                        <option value="Cleaning Staff">Cleaning Staff</option>
                        <option value="Security Staff">Security Staff</option>
                        <option value="External Contractor">External Contractor</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="e.g. 03001234567">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Link User Account (Optional)</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- No linked login account --</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <div class="form-text" style="font-size: 11px;">Select a user registered under the 'maintenance_staff' role to allow them to log in.</div>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active_new" value="1" checked>
                    <label class="form-check-label" for="is_active_new" style="font-size: 12px; font-weight: 600;">Active & Available</label>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold"><i class="bi bi-plus-circle me-1"></i>Add Staff Member</button>
            </form>
        </div>
    </div>
    
    <!-- Right: Existing Staff List -->
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-people-fill me-2 text-success"></i>Existing Staff Members</h6>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Phone</th>
                            <th>User Login</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                        <tr>
                            <td class="fw-bold">{{ $member->name }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $member->designation }}</span>
                            </td>
                            <td>{{ $member->phone ?? '—' }}</td>
                            <td>
                                @if($member->user)
                                    <span class="text-primary"><i class="bi bi-person-check-fill me-1"></i>{{ $member->user->email }}</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-slash-circle me-1"></i>Unlinked</span>
                                @endif
                            </td>
                            <td>
                                @if($member->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $member->id }}"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('admin.complaints.staff.destroy', $member) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        
                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $member->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content" style="border-radius: 12px; border: none;">
                                    <form action="{{ route('admin.complaints.staff.update', $member) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">Edit Staff: {{ $member->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-start">
                                            <div class="mb-3">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $member->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Designation</label>
                                                <select name="designation" class="form-select" required>
                                                    <option value="Maintenance Supervisor" {{ $member->designation === 'Maintenance Supervisor' ? 'selected' : '' }}>Maintenance Supervisor</option>
                                                    <option value="Electrician" {{ $member->designation === 'Electrician' ? 'selected' : '' }}>Electrician</option>
                                                    <option value="Plumber" {{ $member->designation === 'Plumber' ? 'selected' : '' }}>Plumber</option>
                                                    <option value="Civil Technician" {{ $member->designation === 'Civil Technician' ? 'selected' : '' }}>Civil Technician</option>
                                                    <option value="Carpenter" {{ $member->designation === 'Carpenter' ? 'selected' : '' }}>Carpenter</option>
                                                    <option value="Cleaning Staff" {{ $member->designation === 'Cleaning Staff' ? 'selected' : '' }}>Cleaning Staff</option>
                                                    <option value="Security Staff" {{ $member->designation === 'Security Staff' ? 'selected' : '' }}>Security Staff</option>
                                                    <option value="External Contractor" {{ $member->designation === 'External Contractor' ? 'selected' : '' }}>External Contractor</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="phone" class="form-control" value="{{ $member->phone }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Link User Account (Optional)</label>
                                                <select name="user_id" class="form-select">
                                                    <option value="">-- No linked login account --</option>
                                                    @if($member->user)
                                                        <option value="{{ $member->user->id }}" selected>{{ $member->user->name }} ({{ $member->user->email }}) [Current]</option>
                                                    @endif
                                                    @foreach($availableUsers as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text" style="font-size: 11px;">Select a user registered under the 'maintenance_staff' role to allow them to log in.</div>
                                            </div>
                                            <div class="mb-3 form-check form-switch">
                                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active_{{ $member->id }}" value="1" {{ $member->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active_{{ $member->id }}">Active & Available</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success fw-bold">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No maintenance staff members added yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
