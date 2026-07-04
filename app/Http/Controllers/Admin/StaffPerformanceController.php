<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\MaintenanceStaff;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffPerformanceController extends Controller
{
    /**
     * Overview: all staff performance in a date range.
     */
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'data_entry', 'viewer', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        try {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfDay();
        }

        $staff = MaintenanceStaff::with([
            'complaints' => function ($q) use ($startDate, $endDate) {
                $q->withoutGlobalScope('project')
                  ->whereBetween('created_at', [$startDate, $endDate]);
            },
        ])->active()->orderBy('name')->get();

        $performanceData = $staff->map(function ($member) use ($startDate, $endDate) {
            $complaints  = $member->complaints;
            $total       = $complaints->count();
            $resolved    = $complaints->whereIn('status', ['resolved', 'closed'])->count();
            $pending     = $complaints->whereIn('status', ['assigned', 'in_progress'])->count();
            $resolutionRate = $total > 0 ? round($resolved / $total * 100, 1) : 0;

            // Average resolution time in hours
            $resolvedWithTime = $complaints->whereNotNull('resolved_at');
            $avgHours = 0;
            if ($resolvedWithTime->count() > 0) {
                $totalHours = $resolvedWithTime->sum(fn ($c) => $c->created_at->diffInHours($c->resolved_at));
                $avgHours   = round($totalHours / $resolvedWithTime->count(), 1);
            }

            // Satisfaction rate
            $closedComplaints = $complaints->where('status', 'closed');
            $satisfiedCount   = $closedComplaints->where('satisfaction_confirmed', true)->count();
            $satisfactionRate = $closedComplaints->count() > 0
                ? round($satisfiedCount / $closedComplaints->count() * 100, 1)
                : 0;

            return [
                'staff'           => $member,
                'staff_name'      => $member->name,
                'designation'     => $member->designation,
                'total'           => $total,
                'resolved'        => $resolved,
                'pending'         => $pending,
                'resolution_rate' => $resolutionRate,
                'avg_hours'       => $avgHours,
                'satisfaction'    => $satisfactionRate,
            ];
        });

        // Slim chart data (no Eloquent models — safe for @json)
        $chartData = $performanceData->map(fn ($d) => [
            'name'     => $d['staff_name'],
            'total'    => $d['total'],
            'resolved' => $d['resolved'],
            'rate'     => $d['resolution_rate'],
        ]);

        return view('admin.staff.performance.index', compact(
            'performanceData', 'chartData', 'startDate', 'endDate'
        ));
    }

    /**
     * Individual staff performance detail.
     */
    public function show(MaintenanceStaff $staff, Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'data_entry', 'viewer', 'maintenance_supervisor'])) {
            abort(403, 'Unauthorized.');
        }

        try {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfDay();
        }

        // Complaints assigned to this staff in the date range
        $complaints = Complaint::withoutGlobalScope('project')
            ->where('assigned_staff_id', $staff->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['allottee', 'category', 'project'])
            ->orderByDesc('created_at')
            ->get();

        $total    = $complaints->count();
        $resolved = $complaints->whereIn('status', ['resolved', 'closed'])->count();
        $pending  = $complaints->whereIn('status', ['assigned', 'in_progress'])->count();
        $resolutionRate = $total > 0 ? round($resolved / $total * 100, 1) : 0;

        $resolvedWithTime = $complaints->whereNotNull('resolved_at');
        $avgHours = 0;
        if ($resolvedWithTime->count() > 0) {
            $totalHours = $resolvedWithTime->sum(fn ($c) => $c->created_at->diffInHours($c->resolved_at));
            $avgHours   = round($totalHours / $resolvedWithTime->count(), 1);
        }

        $closedComplaints = $complaints->where('status', 'closed');
        $satisfactionRate = $closedComplaints->count() > 0
            ? round($closedComplaints->where('satisfaction_confirmed', true)->count() / $closedComplaints->count() * 100, 1)
            : 0;

        // Monthly trend for the last 6 months (always full 6 months regardless of filter)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd   = Carbon::now()->subMonths($i)->endOfMonth();
            $monthlyTrend[] = [
                'label'    => $mStart->format('M Y'),
                'assigned' => Complaint::withoutGlobalScope('project')
                    ->where('assigned_staff_id', $staff->id)
                    ->whereBetween('created_at', [$mStart, $mEnd])
                    ->count(),
                'resolved' => Complaint::withoutGlobalScope('project')
                    ->where('assigned_staff_id', $staff->id)
                    ->whereIn('status', ['resolved', 'closed'])
                    ->whereBetween('resolved_at', [$mStart, $mEnd])
                    ->count(),
            ];
        }

        // Current-month attendance summary
        $currentMonthStart = Carbon::now()->startOfMonth();
        $attendanceSummary = StaffAttendance::where('maintenance_staff_id', $staff->id)
            ->whereBetween('attendance_date', [$currentMonthStart->format('Y-m-d'), Carbon::now()->format('Y-m-d')])
            ->get();

        return view('admin.staff.performance.show', compact(
            'staff', 'complaints', 'total', 'resolved', 'pending',
            'resolutionRate', 'avgHours', 'satisfactionRate',
            'monthlyTrend', 'startDate', 'endDate', 'attendanceSummary'
        ));
    }
}
