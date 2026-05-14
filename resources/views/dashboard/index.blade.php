@extends('layouts.app')
@section('title', 'Overview Dashboard')
@section('page-title')
    {{ \App\Models\Project::active()?->name ?? 'I-16/3' }} — Allottee Financial & Billing Dashboard
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="m-0 text-secondary" style="font-size: 14px; font-weight: 600;"><i class="bi bi-bar-chart-line me-2"></i>Dashboard Overview</h5>
    <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center bg-white border rounded px-2 py-1 shadow-sm">
        <label class="me-2 text-muted fw-bold" style="font-size: 11px;">FISCAL YEAR:</label>
        <select name="fy" class="form-select form-select-sm border-0 fw-bold text-success" style="width: auto; background: transparent; cursor: pointer; padding-left: 5px; padding-right: 25px;" onchange="this.form.submit()">
            <option value="2024-25" {{ $fiscalYear == '2024-25' ? 'selected' : '' }}>FY 2024-25</option>
            <option value="2025-26" {{ $fiscalYear == '2025-26' ? 'selected' : '' }}>FY 2025-26</option>
            <option value="2026-27" {{ $fiscalYear == '2026-27' ? 'selected' : '' }}>FY 2026-27</option>
        </select>
    </form>
    
    <button type="button" class="btn btn-sm text-white fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#generateBillsModal" style="background: linear-gradient(135deg, #C9A84C, #b49133); border-radius: 8px; border: none; padding: 8px 16px; transition: all 0.3s ease;">
        <i class="bi bi-receipt me-1"></i>Generate {{ date('M Y') }} Bills
    </button>
</div>

<!-- Bill Generation Modal -->
<div class="modal fade" id="generateBillsModal" tabindex="-1" aria-labelledby="generateBillsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background: linear-gradient(135deg, #1B6B35, #0f4423); color: white; border-top-left-radius: 16px; border-top-right-radius: 16px; padding: 20px;">
        <h5 class="modal-title fw-bold" id="generateBillsModalLabel"><i class="bi bi-lightning-charge-fill me-2" style="color: #C9A84C;"></i>Generate Monthly Bills</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <h6 class="fw-bold text-dark mb-3">What happens when you generate bills?</h6>
        <div class="d-flex mb-3">
            <div class="me-3 text-success fs-3"><i class="bi bi-1-circle-fill"></i></div>
            <div>
                <strong class="text-dark">Due Months Incremented</strong>
                <p class="text-muted mb-0" style="font-size: 13px;">Every allottee's "Due Months" will automatically increase by 1.</p>
            </div>
        </div>
        <div class="d-flex mb-3">
            <div class="me-3 text-success fs-3"><i class="bi bi-2-circle-fill"></i></div>
            <div>
                <strong class="text-dark">Arrears Updated</strong>
                <p class="text-muted mb-0" style="font-size: 13px;">The monthly maintenance rate (Cat B or E) will be added to their total outstanding arrears.</p>
            </div>
        </div>
        <div class="d-flex">
            <div class="me-3 text-success fs-3"><i class="bi bi-3-circle-fill"></i></div>
            <div>
                <strong class="text-dark">Defaulter Status Re-evaluated</strong>
                <p class="text-muted mb-0" style="font-size: 13px;">If their due months cross the threshold, they will be marked as a defaulter.</p>
            </div>
        </div>
        
        <div class="alert alert-warning mt-4 mb-0" style="font-size: 13px; border-radius: 8px;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Warning:</strong> This action cannot be easily undone. Only generate bills once per month.
        </div>
      </div>
      <div class="modal-footer bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
        <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
        <form action="{{ route('monthly-bills.generate') }}" method="POST" class="m-0">
            @csrf
            <input type="hidden" name="month" value="{{ date('Y-m') }}">
            <button type="submit" class="btn btn-success fw-bold px-4" style="background: #1B6B35; border: none; border-radius: 8px;">Confirm Generation</button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
/* Premium Dashboard Styling Enhancements - Gold & Green Focus */
.chart-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 20px -2px rgba(27, 107, 53, 0.05), 0 0 3px rgba(27, 107, 53, 0.02);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.chart-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px -5px rgba(27, 107, 53, 0.12), 0 8px 10px -6px rgba(27, 107, 53, 0.05);
    border-color: rgba(201, 168, 76, 0.4);
}
.kpi-strip { display: flex; flex-wrap: wrap; gap: 12px; }
.kpi-pill {
    flex: 1; min-width: 140px; border-radius: 14px;
    padding: 14px 16px; color: #fff; text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative; overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
}
.kpi-pill::after {
    content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
    background: linear-gradient(to bottom right, rgba(255,255,255,0.25), rgba(255,255,255,0));
    transform: rotate(30deg); pointer-events: none;
}
.kpi-pill:hover { 
    transform: translateY(-4px); 
    box-shadow: 0 12px 20px rgba(0,0,0,0.15); 
}
.section-title { 
    font-size: 16px; font-weight: 800; color: #0f4423; margin-bottom: 6px; 
    display: flex; align-items: center; letter-spacing: -0.3px; 
}
.chart-sub { font-size: 12px; color: #64748b; margin-bottom: 16px; font-weight: 500; }
.premium-section-heading {
    margin: 32px 0 20px; padding-bottom: 10px; border-bottom: 2px solid rgba(201, 168, 76, 0.3);
    font-size: 18px; font-weight: 800; color: #0f4423; display: flex; align-items: center;
}
.table-scrollable-container {
    max-height: 280px; 
    overflow-y: auto; 
    border-radius: 8px; 
    border: 1px solid #e2e8f0;
}
.table-scrollable-container thead th {
    position: sticky; top: 0; z-index: 10; 
    background: #1B6B35; color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.legend-box {
    background: #f8fafc; border-radius: 8px; padding: 12px; font-size: 11px;
    border: 1px dashed #cbd5e1;
}
.legend-item { display: flex; align-items: center; margin-bottom: 4px; }
.legend-color { width: 12px; height: 12px; border-radius: 3px; margin-right: 8px; }
</style>

{{-- ===== KPI STRIP ===== --}}
<div class="kpi-strip mb-4">
    <div class="kpi-pill pill-blue">
        <div class="pill-val">{{ number_format($totalAllottees) }}</div>
        <div class="pill-lbl">Total Allottees</div>
    </div>
    <div class="kpi-pill pill-green">
        <div class="pill-val">{{ number_format($totalB) }}</div>
        <div class="pill-lbl">Category B <small>({{ number_format($areaB) }} Sq Ft)</small></div>
    </div>
    <div class="kpi-pill pill-teal">
        <div class="pill-val">{{ number_format($totalE) }}</div>
        <div class="pill-lbl">Category E <small>({{ number_format($areaE) }} Sq Ft)</small></div>
    </div>
    <div class="kpi-pill pill-orange">
        <div class="pill-val">Rs. {{ number_format($totalMonthlyBilling) }}</div>
        <div class="pill-lbl">Monthly Billing (Est.)</div>
    </div>
    <div class="kpi-pill pill-purple">
        <div class="pill-val">Rs. {{ number_format($actualYearly/1000000,2) }}M</div>
        <div class="pill-lbl">Yearly Collection (Actual) <br><small>vs Forecast: Rs. {{ number_format($forecastYearly/1000000,2) }}M</small></div>
    </div>
    <div class="kpi-pill pill-indigo">
        <div class="pill-val">Rs. {{ number_format($totalWWRecoverable) }}</div>
        <div class="pill-lbl">W&W Recoverable</div>
    </div>
    <div class="kpi-pill pill-red">
        <div class="pill-val">Rs. {{ number_format($totalDelayCharges) }}</div>
        <div class="pill-lbl">Delay Charges (10%)</div>
    </div>
</div>

{{-- ===== ROW 1: 3 COLUMNS ===== --}}
<div class="row g-3 mb-3">

    {{-- COL 1: Standard Charges + W&W Eligibility --}}
    <div class="col-lg-4 col-md-6">

        {{-- Standard Charges Table --}}
        <div class="chart-card mb-3">
            <h6 class="section-title"><i class="bi bi-table me-2"></i>Standard Charges <span class="badge-policy ms-2">As Per Policy</span> <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Shows the fixed monthly and yearly maintenance rates for Category B and E apartments."></i></h6>
            <table class="table table-sm table-bordered mb-2" style="font-size:12px;">
                <thead style="background:#1B6B35;color:#fff;">
                    <tr>
                        <th>Category</th><th>Size (Sq Ft)</th><th>Rate (Rs./Sq Ft)</th>
                        <th>Monthly / Unit</th><th>Yearly / Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge badge-b">B-Type</span></td>
                        <td>{{ number_format($areaB) }}</td><td>Rs. {{ number_format($maintenanceRate,2) }}</td>
                        <td><strong>Rs. {{ number_format($monthlyB,2) }}</strong></td>
                        <td>Rs. {{ number_format($yearlyB,2) }}</td>
                    </tr>
                    <tr>
                        <td><span class="badge badge-e">E-Type</span></td>
                        <td>{{ number_format($areaE) }}</td><td>Rs. {{ number_format($maintenanceRate,2) }}</td>
                        <td><strong>Rs. {{ number_format($monthlyE,2) }}</strong></td>
                        <td>Rs. {{ number_format($yearlyE,2) }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="formula-box">
                <strong>Formula:</strong> Maintenance = Rs. {{ $maintenanceRate }} × Area (Sq Ft) × No. of Months
            </div>
        </div>

        {{-- W&W Eligibility --}}
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-shield-check me-2"></i>Watch &amp; Ward Eligibility <span class="badge-policy ms-2">Rs. {{ number_format($wwAmount) }}/-</span> <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Displays the number of allottees eligible for Watch & Ward charges based on their possession dates."></i></h6>
            @php $wwDate = date('d-m-Y', strtotime($wwCutoff)); @endphp
            <div class="row g-2 mt-1">
                <div class="col-4">
                    <div class="h-100 d-flex flex-column justify-content-center" style="border-radius:10px;padding:10px 8px;text-align:center;background:#fef9c3;border:2px solid #f59e0b;color:#92400e;">
                        <div style="font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px;">BEFORE {{ $wwDate }}</div>
                        <div style="font-size:10px;margin-bottom:6px;">No W&amp;W Applicable</div>
                        <div style="font-size:22px;font-weight:900;">{{ number_format($wwBeforeCount) }}</div>
                        <div style="font-size:12px;font-weight:600;">{{ $totalAllottees > 0 ? round($wwBeforeCount/$totalAllottees*100,1) : 0 }}%</div>
                        <div style="font-size:10px;font-weight:700;margin-top:4px;">Rs. 0</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="h-100 d-flex flex-column justify-content-center" style="border-radius:10px;padding:10px 8px;text-align:center;background:#dcfce7;border:2px solid #1B6B35;color:#1B6B35;">
                        <div style="font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px;">ON/AFTER {{ $wwDate }}</div>
                        <div style="font-size:10px;margin-bottom:6px;">W&amp;W Applicable</div>
                        <div style="font-size:22px;font-weight:900;">{{ number_format($wwAfterCount) }}</div>
                        <div style="font-size:12px;font-weight:600;">{{ $totalAllottees > 0 ? round($wwAfterCount/$totalAllottees*100,1) : 0 }}%</div>
                        <div style="font-size:10px;font-weight:700;margin-top:4px;">Rs. {{ number_format($wwAfterAmount) }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="h-100 d-flex flex-column justify-content-center" style="border-radius:10px;padding:10px 8px;text-align:center;background:#ede9fe;border:2px solid #7c3aed;color:#5b21b6;">
                        <div style="font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px;">DATE NOT RECORDED</div>
                        <div style="font-size:10px;margin-bottom:6px;">W&amp;W Applicable</div>
                        <div style="font-size:22px;font-weight:900;">{{ number_format($wwNullCount) }}</div>
                        <div style="font-size:12px;font-weight:600;">{{ $totalAllottees > 0 ? round($wwNullCount/$totalAllottees*100,1) : 0 }}%</div>
                        <div style="font-size:10px;font-weight:700;margin-top:4px;">Rs. {{ number_format($wwNullAmount) }}</div>
                    </div>
                </div>
            </div>
            <div class="mt-2 p-2" style="background:#f0f9f4;border-radius:8px;font-size:11px;color:#1B6B35;">
                <i class="bi bi-info-circle me-1"></i>
                Eligible for W&amp;W: <strong>{{ number_format($wwAfterCount + $wwNullCount) }}</strong>
                ({{ $totalAllottees > 0 ? round(($wwAfterCount+$wwNullCount)/$totalAllottees*100,1) : 0 }}%) —
                Total Recoverable: <strong>Rs. {{ number_format($totalWWRecoverable) }}</strong>
            </div>
        </div>
    </div>

    {{-- COL 2: Monthly Billing Donut + Trend --}}
    <div class="col-lg-4 col-md-6">
        <div class="chart-card mb-3">
            <h6 class="section-title"><i class="bi bi-pie-chart-fill me-2" style="color:#1B6B35;"></i>Monthly Billing by Category <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="A breakdown of expected monthly revenue divided between Category B and Category E."></i></h6>
            <p class="chart-sub">Total monthly charges — Cat B vs Cat E</p>
            <div id="donutCategory"></div>
            <div class="d-flex justify-content-around mt-2">
                <div class="text-center">
                    <div style="font-size:16px;font-weight:800;color:#2563eb;">Rs. {{ number_format($totalMonthlyB/1000000,3) }}M</div>
                    <div style="font-size:11px;color:#94a3b8;">Category B</div>
                </div>
                <div class="text-center">
                    <div style="font-size:16px;font-weight:800;color:#1B6B35;">Rs. {{ number_format($totalMonthlyE/1000000,3) }}M</div>
                    <div style="font-size:11px;color:#94a3b8;">Category E</div>
                </div>
            </div>
        </div>
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-graph-up me-2" style="color:#7c3aed;"></i>Monthly Billing Trend (Estimated) <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Tracks the estimated cumulative billing over the last 6 months."></i></h6>
            <p class="chart-sub">Cumulative monthly billing — Cat B &amp; E over last 6 months</p>
            <div id="trendChart"></div>
        </div>
    </div>

    {{-- COL 3: Billing Summary + Policy Logic --}}
    <div class="col-lg-4 col-md-12">
        <div class="chart-card mb-3">
            <h6 class="section-title"><i class="bi bi-calculator me-2" style="color:#d97706;"></i>Billing Summary <span class="badge-policy ms-2">Estimated</span> <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Calculates the total expected revenue including maintenance, Watch & Ward, and delay charges."></i></h6>
            <table class="table table-sm mb-0" style="font-size:12px;">
                <tr><td class="text-muted">Total Monthly Maintenance (All)</td><td class="text-end fw-600">Rs. {{ number_format($totalMonthlyBilling) }}</td></tr>
                <tr><td class="text-muted">Total Yearly Maintenance (All)</td><td class="text-end fw-600">Rs. {{ number_format($forecastYearly) }}</td></tr>
                <tr><td class="text-muted">Total W&W Recoverable</td><td class="text-end fw-600">Rs. {{ number_format($totalWWRecoverable) }}</td></tr>
                <tr style="background:#f0f4f8;"><td class="fw-700">Subtotal (Maint. + W&W)</td><td class="text-end fw-700">Rs. {{ number_format($subtotal) }}</td></tr>
                <tr><td class="text-muted" style="color:#dc2626;">Total Delay Charges ({{ $delayPct }}%)</td><td class="text-end" style="color:#dc2626;font-weight:700;">Rs. {{ number_format($totalDelayCharges) }}</td></tr>
            </table>
            <div class="grand-total-box mt-2">
                <div style="font-size:11px;font-weight:600;letter-spacing:1px;color:#fff;opacity:0.8;">GRAND TOTAL RECEIVABLE (EST.)</div>
                <div style="font-size:22px;font-weight:900;color:#fff;">Rs. {{ number_format($grandTotal) }}</div>
            </div>

            {{-- Payment vs Pending --}}
            <div class="mt-3 mb-1" style="font-size:11px;font-weight:700;color:#64748b;letter-spacing:1px;text-transform:uppercase;">Revenue Collection ({{ $fiscalYear }})</div>
            <div class="row g-2">
                <div class="col-6">
                    <div style="background:#dcfce7;border-radius:10px;padding:10px;text-align:center;">
                        <div style="font-size:10px;font-weight:600;color:#166534;">PAID / SETTLED</div>
                        <div style="font-size:16px;font-weight:800;color:#1B6B35;">Rs. {{ number_format($totalPaid/1000000,2) }}M</div>
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#fee2e2;border-radius:10px;padding:10px;text-align:center;">
                        <div style="font-size:10px;font-weight:600;color:#991b1b;">PENDING / UNPAID</div>
                        <div style="font-size:16px;font-weight:800;color:#dc2626;">Rs. {{ number_format($totalPending/1000000,2) }}M</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-file-text me-2" style="color:#475569;"></i>Policy & Calculation Logic <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Explains the exact mathematical formulas used to calculate the bills."></i></h6>
            <div class="policy-box" style="font-size:11px;line-height:1.7;">
                <p><strong>1. Watch & Ward (Rs. {{ number_format($wwAmount) }}/-) applies if:</strong><br>
                   &nbsp;&nbsp;• Possession Date ≥ {{ date('d M Y', strtotime($wwCutoff)) }}<br>
                   &nbsp;&nbsp;• OR Possession Date is NULL</p>
                <p><strong>2. Maintenance Charges Formula:</strong><br>
                   &nbsp;&nbsp;Maintenance = Rs. {{ $maintenanceRate }} × Area (Sq Ft) × Months<br>
                   &nbsp;&nbsp;B-Type: 1496 Sq Ft | E-Type: 972 Sq Ft</p>
                <p class="mb-0"><strong>3. Delay Charges (Fine):</strong><br>
                   &nbsp;&nbsp;{{ $delayPct }}% of (Maintenance + W&W Charges)</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== ROW 1.5: NEW FINANCIAL VISUALIZATIONS ===== --}}
<div class="row g-3 mb-3">
    <div class="col-lg-4 col-md-6">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-speedometer2 me-2" style="color:#059669;"></i>Financial Recovery Rate <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Shows the percentage of the total billed amount that has been successfully collected."></i></h6>
            <p class="chart-sub">Percentage of total billed amount recovered</p>
            <div id="recoveryGauge" class="d-flex justify-content-center"></div>
            <div class="text-center mt-2">
                <div style="font-size:11px;color:#64748b;">Target: 100% Recovery</div>
                <div style="font-size:12px;font-weight:700;color:#1B6B35;">Rs. {{ number_format($totalPaid/1000000, 2) }}M Recovered</div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 col-md-6">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-bar-chart-line-fill me-2" style="color:#dc2626;"></i>Due Months Risk Histogram <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Categorizes allottees based on how many months they are overdue on their payments."></i></h6>
            <p class="chart-sub">Distribution of allottees by number of months overdue (Defaulter Curve)</p>
            <div id="dueMonthsHistogram"></div>
        </div>
    </div>
</div>

<div class="premium-section-heading"><i class="bi bi-people-fill me-2 text-primary"></i>Demographics & Analytics</div>

{{-- ===== ROW 2: CITY-WISE & NEW ANALYTICS ===== --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7 col-md-12">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-geo-alt-fill me-2" style="color:#d97706;"></i>City-wise Allottee Distribution <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Shows the geographical distribution of allottees based on their registered mailing addresses."></i></h6>
            <p class="chart-sub">Number of allottees per city</p>
            <div id="cityBar"></div>
            <div class="mt-2 p-2" style="background:#fffbeb;border-radius:8px;font-size:11px;color:#92400e;">
                <i class="bi bi-envelope me-1"></i>
                These cities will be used to dispatch monthly bills on allottees' addresses as per records.
            </div>
        </div>
    </div>
    <div class="col-lg-5 col-md-12">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-table me-2"></i>City-wise Billing Table <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Breaks down total expected monthly and yearly billing by city."></i></h6>
            <div class="table-responsive">
                <table class="table table-sm data-table mb-0" style="font-size:12px;">
                    <thead>
                        <tr><th>City</th><th class="text-end">Allottees</th><th class="text-end">Monthly (Rs.)</th><th class="text-end">Yearly (Rs.)</th></tr>
                    </thead>
                    <tbody>
                        @foreach($cityData as $c)
                        <tr>
                            <td>{{ $c->city ?? 'Unknown' }}</td>
                            <td class="text-end">{{ number_format($c->count) }}</td>
                            <td class="text-end">{{ number_format($c->monthly_billing) }}</td>
                            <td class="text-end">{{ number_format($c->yearly_billing) }}</td>
                        </tr>
                        @endforeach
                        <tr style="background:#f0f4f8;font-weight:700;">
                            <td>TOTAL</td>
                            <td class="text-end">{{ number_format($totalAllottees) }}</td>
                            <td class="text-end">{{ number_format($totalMonthlyBilling) }}</td>
                            <td class="text-end">{{ number_format($forecastYearly) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ===== ROW 2.5: BPS & POSSESSION TIMELINE ===== --}}
<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-radar me-2" style="color:#8b5cf6;"></i>BPS Demographic Distribution <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Shows the breakdown of allottees by their Basic Pay Scale (BPS) grade."></i></h6>
            <p class="chart-sub">Spread of allottees across Basic Pay Scales</p>
            <div id="bpsChart"></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-graph-up-arrow me-2" style="color:#0ea5e9;"></i>Historical Possession Timeline <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Tracks the cumulative number of apartments handed over over time."></i></h6>
            <p class="chart-sub">Cumulative trend of flats handed over</p>
            <div id="possessionChart"></div>
        </div>
    </div>
</div>

{{-- ===== BLOCK-WISE OCCUPANCY ANALYTICS ===== --}}
<div class="section-heading"><i class="bi bi-building me-2" style="color:#7c3aed;"></i>Block-wise Occupancy & Status Analytics</div>

{{-- Block KPI Strip --}}
<div class="row g-3 mb-3">
    <div class="col-lg-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-purple"><i class="bi bi-building"></i></div>
            <div class="kpi-value">{{ $blockData->count() }}</div>
            <div class="kpi-label">Total Blocks</div>
            <div class="kpi-sub">In I-16/3 Apartments</div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-green"><i class="bi bi-house-check-fill"></i></div>
            <div class="kpi-value">{{ number_format($totalHandedOver) }}</div>
            <div class="kpi-label">Handed Over</div>
            <div class="kpi-sub">Units with possession handed over</div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-amber"><i class="bi bi-clock-fill"></i></div>
            <div class="kpi-value">{{ number_format($totalTempOcc) }}</div>
            <div class="kpi-label">Temporary Occupancy</div>
            <div class="kpi-sub">Units with temporary access</div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-blue"><i class="bi bi-arrow-left-right"></i></div>
            <div class="kpi-value">{{ number_format($totalTransferred) }}</div>
            <div class="kpi-label">Transferred</div>
            <div class="kpi-sub">Units with ownership transfer</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h6 class="section-title mb-0 w-100 d-flex"><i class="bi bi-bar-chart-fill me-2" style="color:#7c3aed;"></i>Block-wise Allottee Distribution <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Visualizes occupancy and transfer statuses across different apartment blocks."></i></h6>
                    <p class="chart-sub mb-0 mt-1">Number of allottees per block — showing occupancy, hand-over, and transfer status</p>
                </div>
                <select id="blockCategoryFilter" class="form-select form-select-sm" style="width: 150px; font-weight: 600;">
                    <option value="B">Category B (24 Blocks)</option>
                    <option value="E">Category E (19 Blocks)</option>
                </select>
            </div>
            
            <div class="legend-box mt-3 mb-2">
                <div class="row g-2">
                    <div class="col-md-3 col-6 legend-item"><div class="legend-color" style="background:#1B6B35;"></div> <strong>Total Units:</strong> All apartments</div>
                    <div class="col-md-3 col-6 legend-item"><div class="legend-color" style="background:#2563eb;"></div> <strong>Handed Over:</strong> Keys given</div>
                    <div class="col-md-3 col-6 legend-item"><div class="legend-color" style="background:#d97706;"></div> <strong>Temp. Occ:</strong> Temporary access</div>
                    <div class="col-md-3 col-6 legend-item"><div class="legend-color" style="background:#7c3aed;"></div> <strong>Transferred:</strong> Ownership transferred</div>
                </div>
            </div>

            <div id="blockBar"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-table me-2"></i>Block-wise Summary Table <i class="bi bi-info-circle ms-auto text-muted" style="font-size: 14px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Provides a tabular summary of the data shown in the block distribution chart."></i></h6>
            <div class="table-responsive">
                <table class="table table-sm data-table mb-0" style="font-size:11px;">
                    <thead>
                        <tr>
                            <th>Block</th>
                            <th class="text-end">Allottees</th>
                            <th class="text-end">Handed Over</th>
                            <th class="text-end">Temp. Occ.</th>
                            <th class="text-end">Transferred</th>
                        </tr>
                    </thead>
                    <tbody id="blockTableBody">
                        <!-- Rendered via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



{{-- ===== TOP DEFAULTERS ===== --}}
<div class="section-heading"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Top {{ $defaulters->count() }} Defaulters <small style="font-weight:400;font-size:12px;">≥ {{ $threshold }} months overdue</small></div>
<div class="chart-card mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <p class="chart-sub mb-0">Ranked by total amount owed — <strong>{{ number_format($totalDefaulters) }}</strong> total defaulters ({{ $totalAllottees > 0 ? round($totalDefaulters/$totalAllottees*100,1) : 0 }}%)</p>
        <a href="{{ route('settings.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:12px;"><i class="bi bi-sliders2 me-1"></i>Change Criteria</a>
    </div>
    <div class="table-responsive">
        <table class="table data-table mb-0" style="font-size:12px;">
            <thead>
                <tr><th>#</th><th>Name</th><th>File No.</th><th>Cat</th><th>Block/Flat</th><th>Due Months</th><th>Maintenance</th><th>W&W</th><th>Fine (10%)</th><th>Total Payable</th><th>City</th></tr>
            </thead>
            <tbody>
                @foreach($defaulters as $i => $d)
                <tr>
                    <td><div class="defaulter-rank {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'')) }}">{{ $i+1 }}</div></td>
                    <td>
                        <a href="{{ route('allottees.show', $d) }}" style="text-decoration:none;color:inherit;font-weight:600;">{{ $d->name ?? 'N/A' }}</a>
                        <div style="font-size:10px;color:#94a3b8;">{{ $d->cnic ?? '' }}</div>
                    </td>
                    <td>{{ $d->file_no }}</td>
                    <td><span class="badge {{ $d->category==='B'?'badge-b':'badge-e' }}">{{ $d->category }}</span></td>
                    <td>Blk {{ $d->block_no }} / Flat {{ $d->flat_no }}</td>
                    <td><span class="badge bg-danger">{{ $d->due_months }} mo</span>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ min(100, ($d->due_months / 12) * 100) }}%;"></div>
                        </div>
                    </td>
                    <td>Rs. {{ number_format($d->maintenance_charges) }}</td>
                    <td>Rs. {{ number_format($d->watch_ward_charges) }}</td>
                    <td style="color:#dc2626;">Rs. {{ number_format($d->fine) }}</td>
                    <td style="font-weight:800;color:#1B6B35;">Rs. {{ number_format($d->total_maintenance_charges) }}</td>
                    <td>{{ $d->city }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ===== ALLOTTEE SAMPLE TABLE ===== --}}
<div class="section-heading"><i class="bi bi-people-fill text-primary me-2"></i>Allottee Billing Data <small style="font-weight:400;font-size:12px;">(Top 15 by Total Payable)</small></div>
<div class="chart-card mb-3">
    <div class="table-scrollable-container">
        <table class="table data-table mb-0" style="font-size:11px;">
            <thead>
                <tr>
                    <th>File No.</th><th>Allottee Name</th><th>Cat</th><th>Size</th>
                    <th>Possession Date</th><th>Due Months</th><th>Monthly (Rs.)</th>
                    <th>Maintenance (Rs.)</th><th>W&W (Rs.)</th><th>Delay 10% (Rs.)</th>
                    <th>Total Payable (Rs.)</th><th>City</th><th>Billing Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sampleAllottees as $a)
                <tr>
                    <td>{{ $a->file_no }}</td>
                    <td style="font-weight:600;">{{ $a->name ?? '—' }}</td>
                    <td><span class="badge {{ $a->category==='B'?'badge-b':'badge-e' }}">{{ $a->category }}</span></td>
                    <td>{{ $a->covered_area }} Sq Ft</td>
                    <td>{{ $a->possession_date?->format('d-m-Y') ?? 'NULL' }}</td>
                    <td><span class="badge {{ $a->due_months >= 3 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $a->due_months }}</span></td>
                    <td>{{ number_format($a->covered_area * $maintenanceRate, 2) }}</td>
                    <td>{{ number_format($a->maintenance_charges) }}</td>
                    <td>{{ $a->watch_ward_charges > 0 ? number_format($a->watch_ward_charges) : '—' }}</td>
                    <td style="color:#dc2626;">{{ number_format($a->fine) }}</td>
                    <td style="font-weight:700;color:#1B6B35;">{{ number_format($a->total_maintenance_charges) }}</td>
                    <td>{{ $a->city ?? '—' }}</td>
                    <td style="max-width:160px;white-space:normal;">{{ Str::limit($a->mailing_address ?? '—', 50) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex justify-content-between align-items-center" style="font-size:11px;color:#64748b;">
        <div><i class="bi bi-info-circle me-1"></i>Note: All amounts are estimated. Actuals vary based on recoveries.</div>
        <a href="{{ route('allottees.index') }}" class="btn btn-sm" style="background:#1B6B35;color:#fff;font-size:12px; font-weight: 600; padding: 6px 16px; border-radius: 8px;">View All {{ number_format($totalAllottees) }} Allottees <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    const phaGreen='#1B6B35', phaBlue='#2563eb', phaAmber='#d97706';

    // 1. Category Donut
    new ApexCharts(document.querySelector('#donutCategory'), {
        chart: { 
            type: 'donut', 
            height: 250,
            dropShadow: { enabled: true, top: 4, left: 0, blur: 5, color: '#000', opacity: 0.1 }
        },
        series: [{{ round($billingByCategory['B']) }}, {{ round($billingByCategory['E']) }}],
        labels: ['Cat B ({{ $areaB }} Sq Ft)', 'Cat E ({{ $areaE }} Sq Ft)'],
        colors: [phaBlue, phaGreen],
        legend: { position: 'bottom', fontSize: '12px', fontWeight: 600, markers: { radius: 12 } },
        dataLabels: { 
            enabled: true, 
            formatter: v => v.toFixed(1)+'%',
            style: { fontSize: '12px', fontWeight: 'bold', colors: ['#fff'] },
            dropShadow: { enabled: true, top: 1, left: 1, blur: 1, color: '#000', opacity: 0.4 }
        },
        plotOptions: { 
            pie: { 
                donut: { 
                    size: '72%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '11px', color: '#64748b' },
                        value: { show: true, fontSize: '16px', fontWeight: 800, formatter: v => 'Rs. '+(v/1000000).toFixed(1)+'M' },
                        total: { show: true, showAlways: true, label: 'Total Billed', fontSize: '11px', fontWeight: 600, formatter: function (w) { return 'Rs. '+((w.globals.seriesTotals.reduce((a, b) => a + b, 0))/1000000).toFixed(1)+'M' } }
                    }
                } 
            } 
        },
        stroke: { show: true, colors: '#fff', width: 2 },
        tooltip: { y: { formatter: v => 'Rs. '+v.toLocaleString() } }
    }).render();

    // 2. Monthly Billing Trend
    const trendData = @json($trendData);
    if (trendData && trendData.length > 0) {
        new ApexCharts(document.querySelector('#trendChart'), {
            chart: { type: 'area', height: 210, toolbar: { show: false } },
            series: [
                { name: 'Total', data: trendData.map(d => d.total) },
                { name: 'Cat B', data: trendData.map(d => d.B) },
                { name: 'Cat E', data: trendData.map(d => d.E) },
            ],
            xaxis: { categories: trendData.map(d => d.label), labels: { style: { fontSize: '10px', fontWeight: 600, colors: '#64748b' } } },
            colors: [phaAmber, phaBlue, phaGreen],
            stroke: { curve: 'smooth', width: [3, 2, 2] },
            fill: { 
                type: 'gradient', 
                gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] } 
            },
            markers: { size: 4, colors: ['#fff'], strokeColors: [phaAmber, phaBlue, phaGreen], strokeWidth: 2, hover: { size: 6 } },
            legend: { position: 'top', fontSize: '12px', fontWeight: 600, markers: { radius: 12 } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { left: 10, right: 10 } },
            yaxis: { labels: { formatter: v => 'Rs.'+(v/1000000).toFixed(1)+'M', style: { fontSize: '10px', fontWeight: 600, colors: '#64748b' } } },
            tooltip: { y: { formatter: v => 'Rs. '+v.toLocaleString() } },
            dataLabels: { enabled: false }
        }).render();
    }

    // 3. City Radar (Spider Web) Chart
    const cityData = @json($cityData);
    if (cityData && cityData.length > 0) {
        // Limit to top 8 cities for better spider web readability
        const topCities = cityData.slice(0, 8);
        new ApexCharts(document.querySelector('#cityBar'), {
            chart: { type: 'radar', height: 320, toolbar: { show: false } },
            series: [{ name: 'Allottees', data: topCities.map(c => c.count) }],
            labels: topCities.map(c => c.city || 'Unknown'),
            stroke: { width: 2, colors: ['#d97706'] },
            fill: { opacity: 0.3, colors: ['#fbbf24'] },
            markers: { size: 5, colors: ['#fff'], strokeColors: '#d97706', strokeWidth: 2 },
            yaxis: { show: false },
            plotOptions: {
                radar: {
                    polygons: {
                        strokeColors: '#e2e8f0',
                        connectorColors: '#e2e8f0'
                    }
                }
            },
            dataLabels: { enabled: true, style: { fontSize: '10px', colors: ['#1e293b'] } },
            tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString() + ' allottees' } }
        }).render();
    }

    // 4. Block-wise Status Donut Chart (Category-wise)
    const blockDataAll = @json($blockData);
    let blockChart = null;

    function renderBlockChart(category) {
        const blockData = blockDataAll.filter(b => b.category === category);
        let sTotal = 0, sHanded = 0, sTemp = 0, sTrans = 0;
        
        blockData.forEach(b => {
            sTotal += parseInt(b.total);
            sHanded += parseInt(b.handed_over);
            sTemp += parseInt(b.temp_occ);
            sTrans += parseInt(b.transferred);
        });

        // 1. Render Circular Chart (Donut) instead of Bar
        const options = {
            chart: { type: 'donut', height: 300, toolbar: { show: false } },
            series: [sHanded, sTemp, sTrans, (sTotal - sHanded - sTemp - sTrans)],
            labels: ['Handed Over', 'Temp. Occupancy', 'Transferred', 'Unoccupied/Other'],
            colors: [phaBlue, phaAmber, '#7c3aed', '#94a3b8'],
            legend: { position: 'bottom', fontSize: '11px', fontWeight: 600, markers: { radius: 12 } },
            dataLabels: { enabled: true, formatter: (val, opts) => opts.w.config.series[opts.seriesIndex] },
            plotOptions: { 
                pie: { 
                    donut: { 
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '10px' },
                            value: { show: true, fontSize: '16px', fontWeight: 800 },
                            total: { show: true, showAlways: true, label: 'Total Units', formatter: () => sTotal }
                        }
                    } 
                } 
            },
            stroke: { width: 2, colors: ['#fff'] },
            tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString() + ' units' } }
        };

        if (blockChart) {
            blockChart.destroy();
        }
        blockChart = new ApexCharts(document.querySelector('#blockBar'), options);
        blockChart.render();
        
        // 2. Render Table
        const tbody = document.getElementById('blockTableBody');
        let html = '';
        
        blockData.forEach(b => {
            html += `<tr>
                <td><strong>Cat-${b.category} Blk ${b.block_no}</strong></td>
                <td class="text-end">${parseInt(b.total).toLocaleString()}</td>
                <td class="text-end">${parseInt(b.handed_over).toLocaleString()}</td>
                <td class="text-end">${parseInt(b.temp_occ).toLocaleString()}</td>
                <td class="text-end">${parseInt(b.transferred).toLocaleString()}</td>
            </tr>`;
        });
        
        // Add Total Row
        html += `<tr style="background:#0f4423;color:#fff;font-weight:800;">
            <td>TOTAL</td>
            <td class="text-end">${sTotal.toLocaleString()}</td>
            <td class="text-end">${sHanded.toLocaleString()}</td>
            <td class="text-end">${sTemp.toLocaleString()}</td>
            <td class="text-end">${sTrans.toLocaleString()}</td>
        </tr>`;
        
        tbody.innerHTML = html;
    }

    if (blockDataAll && blockDataAll.length > 0) {
        // Init with Cat B
        renderBlockChart('B');

        document.getElementById('blockCategoryFilter').addEventListener('change', function(e) {
            renderBlockChart(e.target.value);
        });
    }

    // 5. Financial Recovery Gauge
    const totalBilled = {{ $totalMonthlyBilling * 12 }}; // Using estimated yearly as base for example
    const totalPaid = {{ $totalPaid }};
    const recoveryPct = totalBilled > 0 ? (totalPaid / totalBilled * 100) : 0;
    
    new ApexCharts(document.querySelector('#recoveryGauge'), {
        chart: { type: 'radialBar', height: 260 },
        series: [recoveryPct.toFixed(1)],
        plotOptions: {
            radialBar: {
                startAngle: -135, endAngle: 135,
                hollow: { size: '65%' },
                track: { background: '#e2e8f0', strokeWidth: '100%' },
                dataLabels: {
                    name: { show: true, fontSize: '13px', color: '#64748b', offsetY: -10 },
                    value: { show: true, fontSize: '28px', fontWeight: 800, color: '#1B6B35', formatter: v => v + '%' }
                }
            }
        },
        fill: { type: 'gradient', gradient: { shade: 'dark', type: 'horizontal', colorStops: [[{offset: 0, color: '#dc2626'}, {offset: 50, color: '#d97706'}, {offset: 100, color: '#059669'}]] } },
        stroke: { lineCap: 'round' },
        labels: ['Recovery Rate']
    }).render();

    // 6. Due Months Risk Histogram
    const monthsData = @json($monthsDistribution);
    if (monthsData && monthsData.length > 0) {
        new ApexCharts(document.querySelector('#dueMonthsHistogram'), {
            chart: { type: 'bar', height: 220, toolbar: { show: false } },
            series: [{ name: 'Allottees', data: monthsData.map(d => d.count) }],
            xaxis: { categories: monthsData.map(d => d.due_months + (d.due_months == 1 ? ' Mo' : ' Mos')), title: { text: 'Months Overdue' } },
            yaxis: { title: { text: 'Number of Allottees' } },
            colors: ['#dc2626'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
            dataLabels: { enabled: true, style: { fontSize: '10px' } },
            grid: { borderColor: '#f1f5f9' },
            tooltip: { y: { formatter: v => v + ' allottees' } }
        }).render();
    }

    // 7. BPS Demographic Pie Chart
    const bpsData = @json($bpsDistribution);
    if (bpsData && bpsData.length > 0) {
        new ApexCharts(document.querySelector('#bpsChart'), {
            chart: { type: 'pie', height: 280, toolbar: { show: false } },
            series: bpsData.map(d => d.count),
            labels: bpsData.map(d => 'BPS ' + d.bps),
            colors: ['#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#f97316', '#eab308', '#84cc16', '#22c55e', '#06b6d4', '#3b82f6', '#6366f1'],
            legend: { position: 'right', fontSize: '10px' },
            dataLabels: { enabled: true, formatter: (val, opts) => opts.w.config.series[opts.seriesIndex] },
            stroke: { width: 1, colors: ['#fff'] },
            tooltip: { y: { formatter: v => v + ' allottees' } }
        }).render();
    }

    // 8. Historical Possession Timeline
    const possessionData = @json($possessionTimeline);
    if (possessionData && possessionData.length > 0) {
        // Calculate cumulative
        let cumulative = 0;
        const cumulativeData = possessionData.map(d => {
            cumulative += d.count;
            return cumulative;
        });
        
        new ApexCharts(document.querySelector('#possessionChart'), {
            chart: { type: 'area', height: 260, toolbar: { show: false } },
            series: [
                { name: 'Cumulative Handovers', data: cumulativeData },
                { name: 'Monthly Handovers', data: possessionData.map(d => d.count) }
            ],
            xaxis: { categories: possessionData.map(d => d.month), tickAmount: 10 },
            colors: ['#0ea5e9', '#38bdf8'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: [3, 2] },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: { x: { format: 'MMM yyyy' } }
        }).render();
    }
});
</script>
@endpush

