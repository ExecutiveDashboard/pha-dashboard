<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceStaff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffPayrollController extends Controller
{
    /**
     * List all active staff with their payroll data for the selected month.
     */
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));

        // Sanitise input
        try {
            $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Exception $e) {
            $monthStart    = Carbon::now()->startOfMonth();
            $selectedMonth = $monthStart->format('Y-m');
        }
        $monthLabel = $monthStart->format('F Y');

        // All active staff with their payroll eager-loaded for this month only
        $staff = MaintenanceStaff::with(['payrolls' => function ($q) use ($monthStart) {
            $q->whereDate('payroll_month', $monthStart->format('Y-m-d'));
        }])->active()->orderBy('name')->get();

        // Summary statistics
        $payrolls         = StaffPayroll::whereDate('payroll_month', $monthStart->format('Y-m-d'))->get();
        $payrollGenerated = $payrolls->count() > 0;
        $totalNet         = $payrolls->sum('net_salary');
        $paidNet          = $payrolls->where('payment_status', 'paid')->sum('net_salary');
        $pendingNet        = $payrolls->where('payment_status', 'pending')->sum('net_salary');

        return view('admin.staff.payroll.index', compact(
            'staff', 'selectedMonth', 'monthLabel', 'monthStart',
            'payrolls', 'payrollGenerated', 'totalNet', 'paidNet', 'pendingNet'
        ));
    }

    /**
     * Generate (or idempotently re-generate) payroll for the selected month.
     *
     * Salary logic:
     *   Monthly: gross = basic + allowances; deductions per absent/half-day based on daily rate.
     *   Daily:   gross = daily_rate × effective_days (present + 0.5×half) + allowances; no deductions.
     *
     * Already-PAID records are skipped entirely (attendance is locked for those).
     * Already-PENDING records are re-calculated from fresh attendance data.
     */
    public function generate(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate(['payroll_month' => 'required|date_format:Y-m']);

        $monthStart = Carbon::createFromFormat('Y-m', $request->payroll_month)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();
        $totalDays  = $monthStart->daysInMonth;
        $monthLabel = $monthStart->format('F Y');

        $staff     = MaintenanceStaff::active()->get();
        $generated = 0;
        $skipped   = 0;

        foreach ($staff as $member) {
            // Load attendance records for this member for this month
            $attendanceRecords = StaffAttendance::where('maintenance_staff_id', $member->id)
                ->whereBetween('attendance_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                ->get();

            $presentDays = $attendanceRecords->where('status', 'present')->count();
            $absentDays  = $attendanceRecords->where('status', 'absent')->count();
            $halfDays    = $attendanceRecords->where('status', 'half_day')->count();
            $leaveDays   = $attendanceRecords->where('status', 'on_leave')->count();
            $holidayDays = $attendanceRecords->where('status', 'holiday')->count();

            $basicSalary = (float) ($member->basic_salary ?? 0);
            $dailyRate   = (float) ($member->daily_rate   ?? 0);
            $allowances  = (float) ($member->allowances   ?? 0);

            $grossSalary = 0.0;
            $deductions  = 0.0;

            if ($member->salary_type === 'monthly') {
                $grossSalary = $basicSalary + $allowances;
                // Effective absent count: full absent + 0.5 per half-day
                $effectiveAbsent = $absentDays + ($halfDays * 0.5);
                $workingDays     = $totalDays - $holidayDays;
                if ($workingDays > 0 && $effectiveAbsent > 0) {
                    $perDayRate  = $basicSalary / $workingDays;
                    $deductions  = round($perDayRate * $effectiveAbsent, 2);
                }
            } else { // daily
                $effectiveDays = $presentDays + ($halfDays * 0.5);
                $grossSalary   = ($dailyRate * $effectiveDays) + $allowances;
            }

            $netSalary = max(0.0, $grossSalary - $deductions);

            $calcFields = [
                'salary_type'           => $member->salary_type,
                'total_days'            => $totalDays,
                'present_days'          => $presentDays,
                'absent_days'           => $absentDays,
                'half_days'             => $halfDays,
                'leave_days'            => $leaveDays,
                'holiday_days'          => $holidayDays,
                'basic_salary_snapshot' => $basicSalary,
                'allowances_snapshot'   => $allowances,
                'gross_salary'          => round($grossSalary, 2),
                'deductions'            => round($deductions, 2),
                'net_salary'            => round($netSalary, 2),
                'generated_by'          => Auth::id(),
            ];

            $existing = StaffPayroll::where('maintenance_staff_id', $member->id)
                ->whereDate('payroll_month', $monthStart->format('Y-m-d'))
                ->first();

            if ($existing) {
                if ($existing->isPaid()) {
                    // PAID → skip. Attendance is locked; no re-calculation allowed.
                    $skipped++;
                    continue;
                }
                // PENDING → update calculation fields only; keep payment_status = pending
                $existing->update($calcFields);
            } else {
                // First time → create with pending status
                StaffPayroll::create(array_merge($calcFields, [
                    'maintenance_staff_id' => $member->id,
                    'payroll_month'        => $monthStart->format('Y-m-d'),
                    'payment_status'       => 'pending',
                ]));
            }
            $generated++;
        }

        $msg = "Payroll generated/updated for {$generated} staff member(s) — {$monthLabel}.";
        if ($skipped > 0) {
            $msg .= " {$skipped} staff skipped (payroll already PAID).";
        }

        return redirect()
            ->route('admin.staff.payroll.index', ['month' => $request->payroll_month])
            ->with('success', $msg);
    }

    /**
     * Show the printable payslip for a single payroll record.
     */
    public function show(StaffPayroll $payroll)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'data_entry', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        $payroll->load(['staff', 'generatedBy']);
        return view('admin.staff.payroll.show', compact('payroll'));
    }

    /**
     * Mark a payroll record as PAID. This locks the attendance for that month.
     * Only super_admin can mark payments.
     */
    public function markPaid(Request $request, StaffPayroll $payroll)
    {
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Only Super Admin can mark salaries as paid.');
        }

        if ($payroll->isPaid()) {
            return back()->with('error', 'This payroll is already marked as paid.');
        }

        $request->validate([
            'payment_date'    => 'required|date',
            'payment_remarks' => 'nullable|string|max:500',
        ]);

        $payroll->update([
            'payment_status'  => 'paid',
            'payment_date'    => $request->payment_date,
            'payment_remarks' => $request->payment_remarks ?? null,
        ]);

        $monthLabel = Carbon::parse($payroll->payroll_month)->format('Y-m');

        return redirect()
            ->route('admin.staff.payroll.index', ['month' => $monthLabel])
            ->with('success', "Salary marked as PAID for {$payroll->staff->name}. Attendance for {$payroll->monthLabel} is now LOCKED.");
    }
}
