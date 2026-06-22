@extends('layouts.app')
@section('title', 'Complaint Categories')
@section('page-title', 'Manage Complaint Categories')

@section('content')
<div class="row g-3">
    <!-- Left: Add New Category -->
    <div class="col-lg-4">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-tag-fill me-2 text-primary"></i>Add Category</h6>
            <form action="{{ route('admin.complaints.categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Category Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Electrical, Sewerage" required>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active_new" value="1" checked>
                    <label class="form-check-label" for="is_active_new" style="font-size: 12px; font-weight: 600;">Active</label>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold"><i class="bi bi-plus-circle me-1"></i>Create Category</button>
            </form>
        </div>
    </div>
    
    <!-- Right: Existing Categories List -->
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-tags-fill me-2 text-success"></i>Existing Categories</h6>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Status</th>
                            <th>Total Complaints</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td class="fw-bold">{{ $category->name }}</td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $category->complaints()->count() }}</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('admin.complaints.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        
                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content" style="border-radius: 12px; border: none;">
                                    <form action="{{ route('admin.complaints.categories.update', $category) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">Edit Category: {{ $category->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-start">
                                            <div class="mb-3">
                                                <label class="form-label">Category Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                            </div>
                                            <div class="mb-3 form-check form-switch">
                                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active_{{ $category->id }}" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active_{{ $category->id }}">Active</label>
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
                            <td colspan="4" class="text-center py-4 text-muted">No complaint categories created yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
