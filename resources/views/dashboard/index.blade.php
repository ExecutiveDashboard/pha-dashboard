@extends('layouts.app')
@section('title', 'Overview Dashboard')
@section('page-title', 'I-16/3 Apartments — Allottee Financial & Billing Dashboard')

@section('content')

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
        <div class="pill-val">Rs. {{ number_format($totalYearlyBilling/1000000,2) }}M</div>
        <div class="pill-lbl">Yearly Billing (Est.)</div>
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
            <h6 class="section-title"><i class="bi bi-table me-2"></i>Standard Charges <span class="badge-policy">As Per Policy</span></h6>
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
            <h6 class="section-title"><i class="bi bi-shield-check me-2"></i>Watch &amp; Ward Eligibility <span class="badge-policy">Rs. {{ number_format($wwAmount) }}/-</span></h6>
            @php $wwDate = date('d-m-Y', strtotime($wwCutoff)); @endphp
            <div class="row g-2 mt-1">
                <div class="col-4">
                    <div style="border-radius:10px;padding:10px 8px;text-align:center;background:#fef9c3;border:2px solid #f59e0b;color:#92400e;">
                        <div style="font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px;">BEFORE {{ $wwDate }}</div>
                        <div style="font-size:10px;margin-bottom:6px;">No W&amp;W Applicable</div>
                        <div style="font-size:22px;font-weight:900;">{{ number_format($wwBeforeCount) }}</div>
                        <div style="font-size:12px;font-weight:600;">{{ $totalAllottees > 0 ? round($wwBeforeCount/$totalAllottees*100,1) : 0 }}%</div>
                        <div style="font-size:10px;font-weight:700;margin-top:4px;">Rs. 0</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="border-radius:10px;padding:10px 8px;text-align:center;background:#dcfce7;border:2px solid #1B6B35;color:#1B6B35;">
                        <div style="font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px;">ON/AFTER {{ $wwDate }}</div>
                        <div style="font-size:10px;margin-bottom:6px;">W&amp;W Applicable</div>
                        <div style="font-size:22px;font-weight:900;">{{ number_format($wwAfterCount) }}</div>
                        <div style="font-size:12px;font-weight:600;">{{ $totalAllottees > 0 ? round($wwAfterCount/$totalAllottees*100,1) : 0 }}%</div>
                        <div style="font-size:10px;font-weight:700;margin-top:4px;">Rs. {{ number_format($wwAfterAmount) }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="border-radius:10px;padding:10px 8px;text-align:center;background:#ede9fe;border:2px solid #7c3aed;color:#5b21b6;">
                        <div style="font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px;">DATE NULL</div>
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
            <h6 class="section-title"><i class="bi bi-pie-chart-fill me-2" style="color:#1B6B35;"></i>Monthly Billing by Category</h6>
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
            <h6 class="section-title"><i class="bi bi-graph-up me-2" style="color:#7c3aed;"></i>Monthly Billing Trend (Estimated)</h6>
            <p class="chart-sub">Cumulative monthly billing — Cat B &amp; E over last 6 months</p>
            <div id="trendChart"></div>
        </div>
    </div>

    {{-- COL 3: Billing Summary + Policy Logic --}}
    <div class="col-lg-4 col-md-12">
        <div class="chart-card mb-3">
            <h6 class="section-title"><i class="bi bi-calculator me-2" style="color:#d97706;"></i>Billing Summary <span class="badge-policy">Estimated</span></h6>
            <table class="table table-sm mb-0" style="font-size:12px;">
                <tr><td class="text-muted">Total Monthly Maintenance (All)</td><td class="text-end fw-600">Rs. {{ number_format($totalMonthlyBilling) }}</td></tr>
                <tr><td class="text-muted">Total Yearly Maintenance (All)</td><td class="text-end fw-600">Rs. {{ number_format($totalYearlyBilling) }}</td></tr>
                <tr><td class="text-muted">Total W&W Recoverable</td><td class="text-end fw-600">Rs. {{ number_format($totalWWRecoverable) }}</td></tr>
                <tr style="background:#f0f4f8;"><td class="fw-700">Subtotal (Maint. + W&W)</td><td class="text-end fw-700">Rs. {{ number_format($subtotal) }}</td></tr>
                <tr><td class="text-muted" style="color:#dc2626;">Total Delay Charges ({{ $delayPct }}%)</td><td class="text-end" style="color:#dc2626;font-weight:700;">Rs. {{ number_format($totalDelayCharges) }}</td></tr>
            </table>
            <div class="grand-total-box mt-2">
                <div style="font-size:11px;font-weight:600;letter-spacing:1px;color:#fff;opacity:0.8;">GRAND TOTAL RECEIVABLE (EST.)</div>
                <div style="font-size:22px;font-weight:900;color:#fff;">Rs. {{ number_format($grandTotal) }}</div>
            </div>

            {{-- Payment vs Pending --}}
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <div style="background:#dcfce7;border-radius:10px;padding:10px;text-align:center;">
                        <div style="font-size:10px;font-weight:600;color:#166534;">AMOUNT COLLECTED</div>
                        <div style="font-size:16px;font-weight:800;color:#1B6B35;">Rs. {{ number_format($totalPaid/1000000,2) }}M</div>
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#fee2e2;border-radius:10px;padding:10px;text-align:center;">
                        <div style="font-size:10px;font-weight:600;color:#991b1b;">PENDING RECOVERY</div>
                        <div style="font-size:16px;font-weight:800;color:#dc2626;">Rs. {{ number_format($totalPending/1000000,2) }}M</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-file-text me-2" style="color:#475569;"></i>Policy & Calculation Logic</h6>
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

{{-- ===== ROW 2: CITY-WISE ===== --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7 col-md-12">
        <div class="chart-card">
            <h6 class="section-title"><i class="bi bi-geo-alt-fill me-2" style="color:#d97706;"></i>City-wise Allottee Distribution</h6>
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
            <h6 class="section-title"><i class="bi bi-table me-2"></i>City-wise Billing Table</h6>
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
                            <td class="text-end">{{ number_format($totalYearlyBilling) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
            <h6 class="section-title"><i class="bi bi-bar-chart-fill me-2" style="color:#7c3aed;"></i>Block-wise Allottee Distribution</h6>
            <p class="chart-sub">Number of allottees per block — showing occupancy, hand-over, and transfer status</p>
            <div id="blockBar"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card" style="height:100%;">
            <h6 class="section-title"><i class="bi bi-table me-2"></i>Block-wise Summary Table</h6>
            <div class="table-responsive" style="max-height:340px;overflow-y:auto;">
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
                    <tbody>
                        @foreach($blockData as $blk)
                        <tr>
                            <td><strong>Block {{ $blk->block_no }}</strong></td>
                            <td class="text-end">{{ number_format($blk->total) }}</td>
                            <td class="text-end">{{ number_format($blk->handed_over) }}</td>
                            <td class="text-end">{{ number_format($blk->temp_occ) }}</td>
                            <td class="text-end">{{ number_format($blk->transferred) }}</td>
                        </tr>
                        @endforeach
                        <tr style="background:#0f4423;color:#fff;font-weight:800;">
                            <td>TOTAL</td>
                            <td class="text-end">{{ number_format($totalAllottees) }}</td>
                            <td class="text-end">{{ number_format($totalHandedOver) }}</td>
                            <td class="text-end">{{ number_format($totalTempOcc) }}</td>
                            <td class="text-end">{{ number_format($totalTransferred) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ===== POLICY LOGIC SUMMARY TABLE ===== --}}
<div class="section-heading"><i class="bi bi-clipboard-data-fill text-success me-2"></i>Summary by Policy Logic <small style="font-weight:400;font-size:12px;">(Based on Possession Date)</small></div>
<div class="chart-card mb-3">
    <div class="table-responsive">
        <table class="table data-table mb-0" style="font-size:13px;">
            <thead>
                <tr>
                    <th>Policy Logic</th><th class="text-end">No. of Allottees</th><th class="text-end">%</th>
                    <th class="text-end">Monthly Billing (Rs.)</th><th class="text-end">Watch & Ward (Rs.)</th><th class="text-end">Total (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge" style="background:#fef3c7;color:#92400e;">Before {{ date('d-m-Y', strtotime($wwCutoff)) }}</span> — No W&W</td>
                    <td class="text-end">{{ number_format($policyBefore['count']) }}</td>
                    <td class="text-end">{{ $totalAllottees > 0 ? round($policyBefore['count']/$totalAllottees*100,2) : 0 }}%</td>
                    <td class="text-end">{{ number_format($policyBefore['monthly']) }}</td>
                    <td class="text-end">—</td>
                    <td class="text-end fw-600">{{ number_format($policyBefore['monthly']) }}</td>
                </tr>
                <tr>
                    <td><span class="badge" style="background:#dcfce7;color:#166534;">On / After {{ date('d-m-Y', strtotime($wwCutoff)) }}</span> — W&W Applicable</td>
                    <td class="text-end">{{ number_format($policyAfter['count']) }}</td>
                    <td class="text-end">{{ $totalAllottees > 0 ? round($policyAfter['count']/$totalAllottees*100,2) : 0 }}%</td>
                    <td class="text-end">{{ number_format($policyAfter['monthly']) }}</td>
                    <td class="text-end">{{ number_format($policyAfter['ww']) }}</td>
                    <td class="text-end fw-600">{{ number_format($policyAfter['monthly'] + $policyAfter['ww']) }}</td>
                </tr>
                <tr>
                    <td><span class="badge" style="background:#ede9fe;color:#5b21b6;">Possession Date NULL</span> — W&W Applicable</td>
                    <td class="text-end">{{ number_format($policyNull['count']) }}</td>
                    <td class="text-end">{{ $totalAllottees > 0 ? round($policyNull['count']/$totalAllottees*100,2) : 0 }}%</td>
                    <td class="text-end">{{ number_format($policyNull['monthly']) }}</td>
                    <td class="text-end">{{ number_format($policyNull['ww']) }}</td>
                    <td class="text-end fw-600">{{ number_format($policyNull['monthly'] + $policyNull['ww']) }}</td>
                </tr>
                <tr style="background:#0f4423;color:#fff;font-weight:800;">
                    <td>TOTAL</td>
                    <td class="text-end">{{ number_format($policyTotal['count']) }}</td>
                    <td class="text-end">100%</td>
                    <td class="text-end">{{ number_format($policyTotal['monthly']) }}</td>
                    <td class="text-end">{{ number_format($policyTotal['ww']) }}</td>
                    <td class="text-end">{{ number_format($policyTotal['monthly'] + $policyTotal['ww']) }}</td>
                </tr>
            </tbody>
        </table>
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
                    <td><span class="badge bg-danger">{{ $d->due_months }} mo</span></td>
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
    <div class="table-responsive">
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
    <div class="mt-2" style="font-size:11px;color:#64748b;">
        <i class="bi bi-info-circle me-1"></i>Note: All amounts are estimated as per current data. Actual amounts may vary based on final cutoff date and recoveries.
        <a href="{{ route('allottees.index') }}" class="ms-3 btn btn-sm" style="background:#1B6B35;color:#fff;font-size:11px;">View All {{ number_format($totalAllottees) }} Allottees</a>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const phaGreen='#1B6B35', phaBlue='#2563eb', phaAmber='#d97706';

    // 1. Category Donut
    new ApexCharts(document.querySelector('#donutCategory'), {
        chart: { type: 'donut', height: 240 },
        series: [{{ round($billingByCategory['B']) }}, {{ round($billingByCategory['E']) }}],
        labels: ['Cat B ({{ $areaB }} Sq Ft)', 'Cat E ({{ $areaE }} Sq Ft)'],
        colors: [phaBlue, phaGreen],
        legend: { position: 'bottom', fontSize: '11px' },
        dataLabels: { enabled: true, formatter: v => v.toFixed(1)+'%' },
        plotOptions: { pie: { donut: { size: '68%' } } },
        tooltip: { y: { formatter: v => 'Rs. '+v.toLocaleString() } }
    }).render();

    // 2. Monthly Billing Trend
    const trendData = @json($trendData);
    if (trendData && trendData.length > 0) {
        new ApexCharts(document.querySelector('#trendChart'), {
            chart: { type: 'line', height: 200, toolbar: { show: false } },
            series: [
                { name: 'Cat B', data: trendData.map(d => d.B) },
                { name: 'Cat E', data: trendData.map(d => d.E) },
                { name: 'Total', data: trendData.map(d => d.total) },
            ],
            xaxis: { categories: trendData.map(d => d.label), labels: { style: { fontSize: '10px' } } },
            colors: [phaBlue, phaGreen, phaAmber],
            stroke: { curve: 'smooth', width: [2,2,3] },
            markers: { size: 3 },
            legend: { position: 'top', fontSize: '11px', height: 30 },
            grid: { borderColor: '#f1f5f9', padding: { left: 0, right: 0 } },
            yaxis: { labels: { formatter: v => 'Rs.'+(v/1000000).toFixed(1)+'M', style: { fontSize: '10px' } } },
            tooltip: { y: { formatter: v => 'Rs. '+v.toLocaleString() } },
            dataLabels: { enabled: false }
        }).render();
    }

    // 3. City Horizontal Bar
    const cityData = @json($cityData);
    if (cityData && cityData.length > 0) {
        new ApexCharts(document.querySelector('#cityBar'), {
            chart: { type: 'bar', height: cityData.length * 28 + 60, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '70%' } },
            series: [{ name: 'Allottees', data: cityData.map(c => c.count) }],
            xaxis: { categories: cityData.map(c => c.city || 'Unknown'), labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            colors: [phaGreen],
            dataLabels: { enabled: true, style: { fontSize: '10px', colors: ['#fff'] } },
            grid: { borderColor: '#f1f5f9', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
            tooltip: { y: { formatter: v => v.toLocaleString() + ' allottees' } }
        }).render();
    }

    // 4. Block-wise Grouped Bar Chart
    const blockData = @json($blockData);
    if (blockData && blockData.length > 0) {
        new ApexCharts(document.querySelector('#blockBar'), {
            chart: { type: 'bar', height: Math.max(300, blockData.length * 32 + 60), toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '60%', dataLabels: { position: 'top' } } },
            series: [
                { name: 'Total Allottees', data: blockData.map(b => b.total) },
                { name: 'Handed Over',     data: blockData.map(b => b.handed_over) },
                { name: 'Temp. Occupancy', data: blockData.map(b => b.temp_occ) },
                { name: 'Transferred',     data: blockData.map(b => b.transferred) },
            ],
            xaxis: { categories: blockData.map(b => 'Block ' + b.block_no), labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            colors: [phaGreen, phaBlue, phaAmber, '#7c3aed'],
            legend: { position: 'top', fontSize: '11px' },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f5f9', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
            tooltip: { y: { formatter: v => v.toLocaleString() + ' units' } }
        }).render();
    }
});
</script>
@endpush

