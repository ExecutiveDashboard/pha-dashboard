<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\MaintenanceStaff;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplaintReportController extends Controller
{
    public function dashboard()
    {
        // ── KPIs ───────────────────────────────────────────────────────
        $counts = [
            'total' => Complaint::count(),
            'new' => Complaint::where('status', 'new')->count(),
            'assigned' => Complaint::where('status', 'assigned')->count(),
            'in_progress' => Complaint::where('status', 'in_progress')->count(),
            'pending' => Complaint::whereIn('status', ['waiting_for_material', 'pending_external_vendor'])->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
            'closed' => Complaint::where('status', 'closed')->count(),
            'reopened' => Complaint::where('status', 'reopened')->count(),
        ];

        // Average Resolution Time (in hours)
        $resolved = Complaint::whereNotNull('resolved_at')->get();
        $totalHours = 0;
        foreach ($resolved as $c) {
            $totalHours += $c->created_at->diffInHours($c->resolved_at);
        }
        $avgHours = $resolved->count() > 0 ? round($totalHours / $resolved->count(), 1) : 0;
        
        $avgResolutionTime = '—';
        if ($avgHours > 0) {
            if ($avgHours < 24) {
                $avgResolutionTime = $avgHours . ' Hours';
            } else {
                $avgResolutionTime = round($avgHours / 24, 1) . ' Days';
            }
        }

        // ── CHART DATA ─────────────────────────────────────────────────
        // 1. Complaints by Category
        $byCategory = Complaint::select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function($c) {
                return (object)[
                    'label' => $c->category->name ?? 'Deleted Category',
                    'count' => $c->total
                ];
            });

        // 2. Complaints by Project
        // We use withoutGlobalScope here so we can see other projects for general overview
        $byProject = Complaint::withoutGlobalScope('project')
            ->select('project_id', DB::raw('count(*) as total'))
            ->groupBy('project_id')
            ->with('project')
            ->get()
            ->map(function($c) {
                return (object)[
                    'label' => $c->project->name ?? 'Unknown Project',
                    'count' => $c->total
                ];
            });

        // 3. Complaints by Staff
        $byStaff = Complaint::whereNotNull('assigned_staff_id')
            ->select('assigned_staff_id', DB::raw('count(*) as total'))
            ->groupBy('assigned_staff_id')
            ->with('assignedStaff')
            ->get()
            ->map(function($c) {
                return (object)[
                    'label' => $c->assignedStaff->name ?? 'Unassigned',
                    'count' => $c->total
                ];
            });

        // 4. Monthly trends (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $monthlyTrend[] = (object)[
                'label' => $monthStart->format('M Y'),
                'total' => Complaint::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'resolved' => Complaint::whereBetween('resolved_at', [$monthStart, $monthEnd])->count(),
            ];
        }

        return view('admin.complaints.dashboard', compact('counts', 'avgResolutionTime', 'byCategory', 'byProject', 'byStaff', 'monthlyTrend'));
    }

    public function reports(Request $request)
    {
        $query = Complaint::with(['allottee', 'category', 'assignedStaff', 'project']);

        // Filtering
        if ($request->filled('project_id')) {
            $query->withoutGlobalScope('project')->where('project_id', $request->project_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(), 
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $complaints = $query->orderByDesc('created_at')->get();

        $projects = Project::orderBy('name')->get();
        $categories = ComplaintCategory::active()->orderBy('name')->get();

        return view('admin.complaints.reports', compact('complaints', 'projects', 'categories'));
    }

    public function export(Request $request)
    {
        $query = Complaint::with(['allottee', 'category', 'assignedStaff', 'project']);

        // Apply filters
        if ($request->filled('project_id')) {
            $query->withoutGlobalScope('project')->where('project_id', $request->project_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(), 
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $complaints = $query->orderByDesc('created_at')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=complaints_report_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Complaint No', 'Date', 'Project', 'Block', 'Flat No', 'Allottee Name', 'Category', 'Priority', 'Assigned Staff', 'Status', 'Resolved Date', 'Closed Date'];

        $callback = function() use($complaints, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($complaints as $c) {
                fputcsv($file, [
                    $c->complaint_number,
                    $c->created_at->format('Y-m-d H:i:s'),
                    $c->project->name ?? 'N/A',
                    $c->allottee->block_no ?? 'N/A',
                    $c->allottee->flat_no ?? 'N/A',
                    $c->allottee->name ?? 'N/A',
                    $c->category->name ?? 'N/A',
                    strtoupper($c->priority),
                    $c->assignedStaff->name ?? 'Unassigned',
                    strtoupper($c->status),
                    $c->resolved_at ? $c->resolved_at->format('Y-m-d H:i:s') : 'N/A',
                    $c->closed_at ? $c->closed_at->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
