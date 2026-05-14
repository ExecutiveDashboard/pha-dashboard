@extends('layouts.app')
@section('title', 'Block Visual Floor Plan')
@section('page-title', 'Block-wise Visual Floor Plan & Default Status')

@section('content')

<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-12">
        <div class="kpi-card">
            <div class="kpi-icon icon-blue"><i class="bi bi-building"></i></div>
            <div class="kpi-value">{{ $allottees->count() }}</div>
            <div class="kpi-label">Total Units Mapped</div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon icon-green"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-value">{{ number_format($totalPaid) }}</div>
            <div class="kpi-label">Clear Units (No Dues)</div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="kpi-card" style="border: 2px solid #fee2e2;">
            <div class="kpi-icon icon-red"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="kpi-value text-danger">{{ number_format($totalDefaulters) }}</div>
            <div class="kpi-label text-danger">Defaulters (≥ 3 Months)</div>
        </div>
    </div>
</div>

<div class="chart-card mb-4">
    <div class="d-flex align-items-center gap-4 py-2" style="font-size: 13px; font-weight: 600;">
        <span class="text-muted text-uppercase" style="letter-spacing: 1px; font-size: 11px;">Legend:</span>
        <div class="d-flex align-items-center gap-2"><div style="width:20px;height:20px;background:#dcfce7;border:1px solid #166534;border-radius:4px;"></div> Paid / Clear</div>
        <div class="d-flex align-items-center gap-2"><div style="width:20px;height:20px;background:#fef3c7;border:1px solid #d97706;border-radius:4px;"></div> Minor Dues (1-2 mo)</div>
        <div class="d-flex align-items-center gap-2"><div style="width:20px;height:20px;background:#1a2332;border:1px solid #000;border-radius:4px;"></div> Defaulter (≥ 3 mo)</div>
        <div class="d-flex align-items-center gap-2 ms-auto"><span class="badge badge-b border">Cat B</span> <span class="badge badge-e border">Cat E</span></div>
    </div>
</div>

<style>
.block-pills .nav-link {
    color: #475569;
    border-radius: 8px;
    margin: 2px;
    font-weight: 600;
    font-size: 13px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    padding: 6px 14px;
}
.block-pills .nav-link:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.cat-tabs .nav-link {
    color: #475569;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.cat-tabs .nav-link.active[data-bs-target="#cat-B"],
.block-tabs-B .nav-link.active {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: #fff;
    border-color: #1e40af;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    transform: translateY(-1px);
}
.cat-tabs .nav-link.active[data-bs-target="#cat-E"],
.block-tabs-E .nav-link.active {
    background: linear-gradient(135deg, #16a34a, #166534);
    color: #fff;
    border-color: #166534;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
    transform: translateY(-1px);
}

/* Building Map Layout */
.building-frame {
    background: #e2e8f0;
    border: 4px solid #94a3b8;
    border-radius: 8px 8px 0 0;
    padding: 24px 12px 12px 12px;
    margin: 0 auto;
    width: fit-content;
    min-width: 400px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1), inset 0 0 40px rgba(0,0,0,0.03);
}
.building-roof {
    position: absolute;
    top: -20px;
    left: -16px;
    right: -16px;
    height: 24px;
    background: linear-gradient(180deg, #64748b 0%, #475569 100%);
    border-radius: 6px 6px 0 0;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.building-roof::before {
    content: ''; position: absolute; top: -10px; left: 10%; width: 15px; height: 10px; background: #64748b;
}
.building-roof::after {
    content: ''; position: absolute; top: -15px; right: 15%; width: 10px; height: 15px; background: #475569;
}
.building-foundation {
    height: 12px;
    background: #475569;
    margin: 0 -20px -12px -20px;
    border-radius: 2px;
}
.floor-row {
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 4px solid #cbd5e1;
}
.floor-row:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}
</style>

<ul class="nav nav-pills cat-tabs mb-4 justify-content-center" id="categoryTabs" role="tablist">
    @foreach($categorizedBlocks as $category => $blocks)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->first ? 'active' : '' }} fw-bold px-5 py-2 rounded-pill mx-2 shadow-sm" 
                id="cat-{{ $category }}-tab" data-bs-toggle="pill" data-bs-target="#cat-{{ $category }}" 
                type="button" role="tab" style="font-size: 15px;">
                <i class="bi bi-layers-fill me-2"></i> Category {{ $category }}
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content" id="categoryTabsContent">
    @foreach($categorizedBlocks as $category => $blocks)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="cat-{{ $category }}" role="tabpanel">
            
            <div class="chart-card mb-4" style="background: rgba(255,255,255,0.6); backdrop-filter: blur(8px);">
                <div class="d-flex align-items-center mb-2">
                    <h6 class="mb-0 fw-bold text-muted"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Select Block</h6>
                </div>
                <ul class="nav nav-pills block-pills block-tabs-{{ $category }} flex-wrap gap-1" id="blockTabs-{{ $category }}" role="tablist">
                    @foreach($blocks as $blockName => $floors)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                id="block-{{ $category }}-{{ $blockName }}-tab" data-bs-toggle="pill" 
                                data-bs-target="#block-{{ $category }}-{{ $blockName }}" type="button" role="tab">
                                Block {{ $blockName }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="tab-content" id="blockTabsContent-{{ $category }}">
                @foreach($blocks as $blockName => $floors)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="block-{{ $category }}-{{ $blockName }}" role="tabpanel">
                        
                        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
                            <div class="card-header border-0 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, {{ $category === 'B' ? '#2563eb, #1e40af' : '#16a34a, #166534' }}); padding: 12px 20px;">
                                <h5 class="mb-0 fw-bold" style="font-size: 16px; letter-spacing: 0.5px;">
                                    <i class="bi bi-building me-2"></i> Category {{ $category }} — Block {{ $blockName }}
                                </h5>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-white text-dark rounded-pill shadow-sm" style="font-size: 11px;">{{ count($floors) }} Floors</span>
                                    <span class="badge bg-white text-dark rounded-pill shadow-sm" style="font-size: 11px;">
                                        {{ collect($floors)->flatten()->count() }} Units
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4 bg-light d-flex justify-content-center" style="overflow-x: auto;">
                                
                                <!-- Building Elevation Map -->
                                <div class="building-frame position-relative mt-3">
                                    <!-- Roof -->
                                    <div class="building-roof"></div>
                                    
                                    @foreach($floors as $floorName => $flats)
                                        <div class="floor-row d-flex">
                                            <div class="floor-label text-center px-2 d-flex align-items-center justify-content-center" style="width: 40px;">
                                                <span style="writing-mode: vertical-rl; transform: rotate(180deg); font-weight: 800; font-size: 11px; letter-spacing: 1.5px; color: #64748b;">
                                                    {{ str_replace(' Floor', '', $floorName) }}
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex gap-2 flex-grow-1 p-2 bg-white rounded shadow-sm border border-secondary border-opacity-10" style="min-width: 200px;">
                                                @foreach($flats as $flat)
                                                    @php
                                                        $bgClass = 'bg-white';
                                                        $borderClass = 'border-light-subtle';
                                                        $textClass = 'text-dark';
                                                        $icon = 'bi-house-door';
                                                        $shadowClass = 'shadow-sm';
                                                        
                                                        if($flat->due_months >= 3) {
                                                            $bgClass = 'bg-dark';
                                                            $borderClass = 'border-dark';
                                                            $textClass = 'text-white';
                                                            $icon = 'bi-exclamation-triangle-fill text-danger';
                                                            $shadowClass = 'shadow';
                                                        } elseif($flat->due_months > 0) {
                                                            $bgClass = 'bg-warning bg-opacity-10';
                                                            $borderClass = 'border-warning border-opacity-50';
                                                            $icon = 'bi-exclamation-circle-fill text-warning';
                                                        } elseif($flat->payment_status_computed === 'paid') {
                                                            $bgClass = 'bg-success bg-opacity-10';
                                                            $borderClass = 'border-success border-opacity-50';
                                                            $icon = 'bi-check-circle-fill text-success';
                                                        }

                                                        // Occupancy Logic
                                                        $occStatus = 'Unoccupied';
                                                        $occColor = '#94a3b8';
                                                        if ($flat->handed_over && (stripos($flat->handed_over, 'handed') !== false || stripos($flat->handed_over, 'possession') !== false)) {
                                                            $occStatus = 'Handed Over';
                                                            $occColor = '#16a34a';
                                                        }
                                                        if ($flat->temporary_occupancy && stripos($flat->temporary_occupancy, 'temporary') !== false) {
                                                            $occStatus = 'Temp Occupied';
                                                            $occColor = '#d97706';
                                                        }

                                                        $fName = $flat->name ? htmlspecialchars($flat->name, ENT_QUOTES) : 'Unknown';
                                                        $fTotal = number_format($flat->total_maintenance_charges);
                                                        
                                                        $tooltipHtml = "
                                                            <div style='text-align:left; min-width:180px; padding:2px;'>
                                                                <div style='border-bottom:1px solid rgba(255,255,255,0.2); padding-bottom:8px; margin-bottom:8px;'>
                                                                    <div style='font-size:12.5px; font-weight:700; color:#fff; line-height:1.2; margin-bottom:2px;'>{$fName}</div>
                                                                    <div style='font-size:10.5px; color:#cbd5e1; font-weight:600;'>Flat {$flat->flat_no}</div>
                                                                </div>
                                                                <div style='display:flex; align-items:center; gap:8px; margin-bottom:8px; background:rgba(0,0,0,0.2); padding:6px 8px; border-radius:6px;'>
                                                                    <span style='display:inline-block; width:10px; height:10px; border-radius:50%; background:{$occColor}; box-shadow:0 0 8px {$occColor};'></span>
                                                                    <span style='font-size:11px; color:#f8fafc; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;'>{$occStatus}</span>
                                                                </div>
                                                                <div style='display:flex; justify-content:space-between; font-size:11px; margin-bottom:4px;'>
                                                                    <span style='color:#94a3b8;'>Arrears:</span>
                                                                    <span style='color:#fff; font-weight:700;'>{$flat->due_months} Mo</span>
                                                                </div>
                                                                <div style='display:flex; justify-content:space-between; font-size:11px;'>
                                                                    <span style='color:#94a3b8;'>Total Dues:</span>
                                                                    <span style='color:#fff; font-weight:700;'>Rs. {$fTotal}</span>
                                                                </div>
                                                            </div>
                                                        ";
                                                    @endphp
                                                    
                                                    <a href="{{ route('allottees.show', $flat->id) }}" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-html="true" 
                                                       title="{{ $tooltipHtml }}">
                                                        <div class="position-relative p-2 rounded {{ $bgClass }} {{ $shadowClass }} flat-unit" style="width: 70px; height: 85px; border: 2px solid; border-color: var(--bs-border-color); display: flex; flex-direction: column; justify-content: flex-end; align-items: center; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='';">
                                                            <!-- Window/Balcony Graphic -->
                                                            <div class="position-absolute top-0 w-100 start-0 d-flex justify-content-center pt-2">
                                                                <div style="width: 40px; height: 30px; background: rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.1); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="bi {{ $icon }}" style="font-size: 14px; opacity: 0.8;"></i>
                                                                </div>
                                                            </div>
                                                            
                                                            <span style="font-size: 14px; font-weight: 800; letter-spacing: -0.5px; z-index: 2;" class="{{ $textClass }} mt-auto">{{ $flat->flat_no }}</span>
                                                            @if($flat->due_months >= 3)
                                                                <span style="font-size: 9px; opacity: 0.9; z-index: 2;" class="text-danger fw-bold mt-1">{{ $flat->due_months }} Mo</span>
                                                            @else
                                                                <span style="font-size: 9px; opacity: 0.6; z-index: 2;" class="{{ $textClass }} mt-1">Clear</span>
                                                            @endif
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    <!-- Building Foundation -->
                                    <div class="building-foundation mt-2"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endpush
