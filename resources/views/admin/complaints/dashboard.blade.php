@extends('layouts.app')
@section('title', 'Complaints Dashboard')
@section('page-title', 'Complaint Management Dashboard')

@section('content')
<!-- KPI Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-blue"><i class="bi bi-chat-left-text-fill"></i></div>
            <div class="kpi-value">{{ number_format($counts['total']) }}</div>
            <div class="kpi-label">Total Registered</div>
            <div class="kpi-sub">Lifetime complaints submitted</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-amber"><i class="bi bi-bell-fill animate-pulse"></i></div>
            <div class="kpi-value">{{ number_format($counts['new']) }}</div>
            <div class="kpi-label">New Complaints</div>
            <div class="kpi-sub">Awaiting initial review</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-purple"><i class="bi bi-person-fill-gear"></i></div>
            <div class="kpi-value">{{ number_format($counts['assigned'] + $counts['in_progress']) }}</div>
            <div class="kpi-label">Active / Work In Progress</div>
            <div class="kpi-sub">Assigned to maintenance staff</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-green"><i class="bi bi-patch-check-fill"></i></div>
            <div class="kpi-value">{{ number_format($counts['resolved']) }}</div>
            <div class="kpi-label">Resolved (Pending Close)</div>
            <div class="kpi-sub">Marked resolved, awaiting closure</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-teal"><i class="bi bi-lock-fill"></i></div>
            <div class="kpi-value">{{ number_format($counts['closed']) }}</div>
            <div class="kpi-label">Successfully Closed</div>
            <div class="kpi-sub">Resolved and confirmed by allottee</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-red"><i class="bi bi-arrow-counterclockwise"></i></div>
            <div class="kpi-value">{{ number_format($counts['reopened']) }}</div>
            <div class="kpi-label">Reopened Issues</div>
            <div class="kpi-sub">Satisfaction not met</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-amber"><i class="bi bi-tools"></i></div>
            <div class="kpi-value">{{ number_format($counts['pending']) }}</div>
            <div class="kpi-label">Pending (Vendor/Material)</div>
            <div class="kpi-sub">Blocked due to materials or vendor</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card" style="background: linear-gradient(135deg, #1B6B35, #0f4423); color: white;">
            <div class="kpi-icon" style="background: rgba(255,255,255,0.2); color: #C9A84C;"><i class="bi bi-stopwatch-fill"></i></div>
            <div class="kpi-value" style="color: white;">{{ $avgResolutionTime }}</div>
            <div class="kpi-label" style="color: rgba(255,255,255,0.8);">Avg. Resolution Time</div>
            <div class="kpi-sub" style="color: rgba(255,255,255,0.6);">From submission to resolution</div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-3 mb-4">
    <!-- Monthly Trend Chart -->
    <div class="col-lg-8">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-graph-up me-2 text-primary"></i>Complaint Activity Trends</h6>
            <p class="chart-sub">Monthly comparison of submitted vs resolved complaints (last 6 months)</p>
            <div id="monthlyTrendChart"></div>
        </div>
    </div>
    <!-- Category Breakdown Chart -->
    <div class="col-lg-4">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-pie-chart-fill me-2 text-success"></i>By Category</h6>
            <p class="chart-sub">Distribution of complaints by trade category</p>
            <div id="categoryChart"></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Project Breakdown Chart -->
    <div class="col-lg-6">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-building me-2 text-warning"></i>By Housing Project</h6>
            <p class="chart-sub">Active complaints distribution across all projects</p>
            <div id="projectChart"></div>
        </div>
    </div>
    <!-- Staff Load Chart -->
    <div class="col-lg-6">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-people-fill me-2 text-info"></i>Staff Workload</h6>
            <p class="chart-sub">Number of active complaints assigned to each maintenance member</p>
            <div id="staffChart"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const phaGreen = '#1B6B35', phaGold = '#C9A84C', phaBlue = '#2563eb', phaRed = '#dc2626';

    // 1. Monthly Trend Chart
    const trendData = @json($monthlyTrend);
    new ApexCharts(document.querySelector('#monthlyTrendChart'), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        series: [
            { name: 'Submitted', data: trendData.map(d => d.total) },
            { name: 'Resolved', data: trendData.map(d => d.resolved) }
        ],
        xaxis: { categories: trendData.map(d => d.label), labels: { style: { fontSize: '10px', fontWeight: 600 } } },
        colors: [phaGold, phaGreen],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.05 } },
        legend: { position: 'top', fontWeight: 600 },
        grid: { borderColor: '#f1f5f9' },
        tooltip: { y: { formatter: v => v + ' complaints' } }
    }).render();

    // 2. Category Donut Chart
    const categoryData = @json($byCategory);
    new ApexCharts(document.querySelector('#categoryChart'), {
        chart: { type: 'donut', height: 280 },
        series: categoryData.map(c => c.count),
        labels: categoryData.map(c => c.label),
        colors: ['#2563eb', '#1B6B35', '#d97706', '#7c3aed', '#0d9488', '#0284c7', '#db2777', '#475569'],
        legend: { position: 'bottom', fontSize: '11px', fontWeight: 600 },
        plotOptions: { pie: { donut: { size: '65%' } } },
        tooltip: { y: { formatter: v => v + ' complaints' } }
    }).render();

    // 3. Project Bar Chart
    const projectData = @json($byProject);
    new ApexCharts(document.querySelector('#projectChart'), {
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
        series: [{ name: 'Complaints', data: projectData.map(p => p.count) }],
        xaxis: { categories: projectData.map(p => p.label) },
        colors: [phaGold],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
        grid: { borderColor: '#f1f5f9' },
        dataLabels: { enabled: true },
        tooltip: { y: { formatter: v => v + ' complaints' } }
    }).render();

    // 4. Staff Load Chart
    const staffData = @json($byStaff);
    new ApexCharts(document.querySelector('#staffChart'), {
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
        series: [{ name: 'Assigned Complaints', data: staffData.map(s => s.count) }],
        xaxis: { categories: staffData.map(s => s.label) },
        colors: [phaGreen],
        plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '50%' } },
        grid: { borderColor: '#f1f5f9' },
        dataLabels: { enabled: true },
        tooltip: { y: { formatter: v => v + ' active complaints' } }
    }).render();
});
</script>
@endpush
