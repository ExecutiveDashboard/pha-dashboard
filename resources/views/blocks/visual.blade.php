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

@foreach($blocks as $blockName => $floors)
    <div class="section-heading mt-4"><i class="bi bi-building me-2 text-primary"></i>Block {{ $blockName }}</div>
    <div class="chart-card mb-4">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center" style="table-layout: fixed; min-width: 800px;">
                <tbody>
                    @foreach($floors as $floorName => $flats)
                        <tr>
                            <td class="bg-light align-middle fw-bold border-end" style="width: 100px;">{{ $floorName }}</td>
                            <td class="p-2">
                                <div class="d-flex flex-wrap gap-2 justify-content-start">
                                    @foreach($flats as $flat)
                                        @php
                                            $bgClass = 'bg-white';
                                            $borderClass = 'border-secondary';
                                            $textClass = 'text-dark';
                                            
                                            if($flat->due_months >= 3) {
                                                $bgClass = 'bg-dark'; // Black for defaulter
                                                $borderClass = 'border-dark';
                                                $textClass = 'text-white';
                                            } elseif($flat->due_months > 0) {
                                                $bgClass = 'bg-warning bg-opacity-25'; // Yellow
                                                $borderClass = 'border-warning';
                                            } elseif($flat->payment_status_computed === 'paid') {
                                                $bgClass = 'bg-success bg-opacity-25'; // Green
                                                $borderClass = 'border-success';
                                            }
                                        @endphp
                                        
                                        <a href="{{ route('allottees.show', $flat->id) }}" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-html="true" 
                                           title="<b>{{ $flat->name }}</b><br>Flat: {{ $flat->flat_no }}<br>Due: {{ $flat->due_months }} months<br>Amt: Rs. {{ number_format($flat->total_maintenance_charges) }}">
                                            <div class="p-2 border rounded shadow-sm {{ $bgClass }} {{ $borderClass }} {{ $textClass }}" style="width: 65px; height: 65px; display: flex; flex-direction: column; justify-content: center; align-items: center; transition: transform 0.2s;">
                                                <span style="font-size: 14px; font-weight: 800;">{{ $flat->flat_no }}</span>
                                                <span style="font-size: 9px; opacity: 0.8;" class="mt-1 badge {{ $flat->category==='B'?'bg-primary':'bg-success' }} py-1">{{ $flat->category }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

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
