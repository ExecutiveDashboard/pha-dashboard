<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintCategoryController extends Controller
{
    public function index()
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $categories = ComplaintCategory::orderBy('name')->get();
        return view('admin.complaints.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:complaint_categories,name',
        ]);

        ComplaintCategory::create([
            'name' => trim($request->name),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('admin.complaints.categories.index')->with('success', 'Complaint category added successfully.');
    }

    public function update(Request $request, ComplaintCategory $category)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('complaint_categories', 'name')->ignore($category->id)
            ],
        ]);

        $category->update([
            'name' => trim($request->name),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('admin.complaints.categories.index')->with('success', 'Complaint category updated successfully.');
    }

    public function destroy(ComplaintCategory $category)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        // Check if category has any complaints associated
        if ($category->complaints()->count() > 0) {
            return redirect()->route('admin.complaints.categories.index')->with('error', 'Cannot delete category because it has associated complaints. Deactivate it instead.');
        }

        $category->delete();
        return redirect()->route('admin.complaints.categories.index')->with('success', 'Complaint category deleted successfully.');
    }
}
