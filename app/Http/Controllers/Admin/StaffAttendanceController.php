<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceStaff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffAttendanceController extends Controller
{
    /**
     * Check whether attendance for a specific staff + month is locked.
     * Attendance is locked when the staff member's payroll for that month
     * has been marked as PAID.
     */
    private function isLocked(int $staffId, Carbon $monthStart): bool
    {
        return StaffPayroll::where('maintenance_staff_id', $staffId)
            ->whereDate('payroll_month', $monthStart->format('Y-m-d'))
            ->where('payment_status', 'paid')
            ->exists();
    }

    /**
     * Display the daily attendance entry form.
     * Defaults to today. Navigated via ?date=YYYY-MM-DD.
     */
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        // Resolve selected date
        try {
            $selectedDate = $request->filled('date')
                ? Carbon::parse($request->input('date'))
                : Carbon::today();
        } catch (\Exception $e) {
            $selectedDate = Carbon::today();
        }

        $monthStart = $selectedDate->copy()->startOfMonth();
        $monthLabel = $selectedDate->format('F Y');

        // All active staff ordered by name
        $staff = MaintenanceStaff::active()->orderBy('name')->get();

        // IDs of staff whose attendance is locked (payroll paid) for this month
        $lockedStaffIds = StaffPayroll::whereDate('payroll_month', $monthStart->format('Y-m-d'))
            ->where('payment_status', 'paid')
            ->pluck('maintenance_staff_id')
            ->toArray();

        // Already-saved attendance records for the selected date, keyed by staff ID
        $existingAttendance = StaffAttendance::whereDate('attendance_date', $selectedDate->format('Y-m-d'))
            ->whereIn('maintenance_staff_id', $staff->pluck('id'))
            ->get()
            ->keyBy('maintenance_staff_id');

        // Monthly summary for the month of the selected date (up to the end of month)
        $monthlySummary = StaffAttendance::whereBetween('attendance_date', [
                $monthStart->format('Y-m-d'),
                $selectedDate->copy()->endOfMonth()->format('Y-m-d'),
            ])
            ->whereIn('maintenance_staff_id', $staff->pluck('id'))
            ->get()
            ->groupBy('maintenance_staff_id');

        return view('admin.staff.attendance.index', compact(
            'staff', 'selectedDate', 'monthLabel',
            'lockedStaffIds', 'existingAttendance', 'monthlySummary'
        ));
    }

    /**
     * Save attendance for all staff for a given date.
     * Skips staff whose attendance is locked (payroll paid).
     */
    public function save(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate(['date' => 'required|date']);

        $date      = Carbon::parse($request->input('date'));
        $monthStart = $date->copy()->startOfMonth();
        $records    = $request->input('attendance', []);

        if (empty($records)) {
            return redirect()
                ->route('admin.staff.attendance.index', ['date' => $date->format('Y-m-d')])
                ->with('error', 'No attendance was submitted. Please select a status for at least one staff member.');
        }

        $saved  = 0;
        $locked = 0;

        foreach ($records as $staffId => $data) {
            $staffId = (int) $staffId;
            $status  = $data['status'] ?? null;

            if (!$status || !in_array($status, ['present', 'absent', 'half_day', 'on_leave', 'holiday'])) {
                continue;
            }

            // Attendance locked when payroll is paid for this month
            if ($this->isLocked($staffId, $monthStart)) {
                $locked++;
                continue;
            }

            StaffAttendance::updateOrCreate(
                ['maintenance_staff_id' => $staffId, 'attendance_date' => $date],
                [
                    'status'      => $status,
                    'remarks'     => trim($data['remarks'] ?? ''),
                    'recorded_by' => Auth::id(),
                ]
            );
            $saved++;
        }

        $msg = "Attendance saved for {$saved} staff member(s) on {$date->format('d F Y')}.";
        if ($locked > 0) {
            $msg .= " {$locked} staff skipped — payroll already PAID and attendance is locked.";
        }

        return redirect()
            ->route('admin.staff.attendance.index', ['date' => $date->format('Y-m-d')])
            ->with('success', $msg);
    }
}
