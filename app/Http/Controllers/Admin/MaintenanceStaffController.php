<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceStaffController extends Controller
{
    public function index()
    {
        $staff = MaintenanceStaff::with('user')->orderBy('name')->get();
        // Load users with 'maintenance_staff' role who are not already linked to another staff,
        // plus any user currently linked to the staff being edited.
        $availableUsers = User::where('role', 'maintenance_staff')
            ->where(function($query) {
                $query->whereNotExists(function($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('maintenance_staff')
                      ->whereColumn('maintenance_staff.user_id', 'users.id');
                });
            })
            ->orderBy('name')
            ->get();

        return view('admin.complaints.staff', compact('staff', 'availableUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'user_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('maintenance_staff')
            ],
        ]);

        MaintenanceStaff::create([
            'name' => $request->name,
            'designation' => $request->designation,
            'phone' => $request->phone,
            'user_id' => $request->user_id,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('admin.complaints.staff.index')->with('success', 'Maintenance staff member added successfully.');
    }

    public function update(Request $request, MaintenanceStaff $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'user_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('maintenance_staff')->ignore($staff->id)
            ],
        ]);

        $staff->update([
            'name' => $request->name,
            'designation' => $request->designation,
            'phone' => $request->phone,
            'user_id' => $request->user_id,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('admin.complaints.staff.index')->with('success', 'Staff details updated successfully.');
    }

    public function destroy(MaintenanceStaff $staff)
    {
        $staff->delete();
        return redirect()->route('admin.complaints.staff.index')->with('success', 'Staff member removed successfully.');
    }
}
