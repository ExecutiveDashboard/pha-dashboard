@extends('layouts.app')
@section('title', 'User Management')
@section('page-title', 'Manage Admins & Roles')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Add New Admin</h6>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Password</label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Assign Role</label>
                    <select name="role" class="form-select" required>
                        <option value="viewer">Viewer (Dashboard Only)</option>
                        <option value="data_entry">Data Entry (Allottees & Bills)</option>
                        <option value="whatsapp_sender">WhatsApp Sender (Communications)</option>
                        <option value="maintenance_supervisor">Maintenance Supervisor (CMS & Staff HR)</option>
                        <option value="super_admin">Super Admin (Full Access)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold"><i class="bi bi-plus-circle me-1"></i>Create Admin</button>
            </form>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-people-fill me-2 text-success"></i>Existing Admins</h6>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="fw-bold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'super_admin')
                                    <span class="badge bg-danger">Super Admin</span>
                                @elseif($user->role === 'data_entry')
                                    <span class="badge bg-primary">Data Entry</span>
                                @elseif($user->role === 'whatsapp_sender')
                                    <span class="badge bg-success">WhatsApp Sender</span>
                                @elseif($user->role === 'maintenance_supervisor')
                                    <span class="badge bg-warning text-dark">Maintenance Supervisor</span>
                                @else
                                    <span class="badge bg-secondary">Viewer</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $user->id }}"><i class="bi bi-pencil"></i></button>
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        
                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content" style="border-radius: 12px; border: none;">
                                    <form action="{{ route('users.update', $user) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">Edit Admin: {{ $user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-start">
                                            <div class="mb-3">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email Address</label>
                                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">New Password (leave blank to keep current)</label>
                                                <input type="password" name="password" class="form-control" minlength="6">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Assign Role</label>
                                                <select name="role" class="form-select" required>
                                                    <option value="viewer" {{ $user->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                    <option value="data_entry" {{ $user->role === 'data_entry' ? 'selected' : '' }}>Data Entry</option>
                                                    <option value="whatsapp_sender" {{ $user->role === 'whatsapp_sender' ? 'selected' : '' }}>WhatsApp Sender</option>
                                                    <option value="maintenance_supervisor" {{ $user->role === 'maintenance_supervisor' ? 'selected' : '' }}>Maintenance Supervisor</option>
                                                    <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                                </select>
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
