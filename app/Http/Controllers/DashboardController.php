<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Allottee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fiscalYear = $request->get('fy', '2025-26');
        
        $settings = Setting::all()->keyBy('key');


        // ── BILLING RATES (from settings) ──────────────────────────────
        // NOTE: maintenance_rate_per_sqft from Settings is the authoritative source for the billing rate.
        $maintenanceRate = (float) Setting::getValue('maintenance_rate_per_sqft', 3.07);
        $wwAmount        = (float) Setting::getValue('watch_ward_amount', 10000);
        $wwCutoff        = Setting::getValue('watch_ward_cutoff_date', '2023-07-23');
        $delayPct        = (float) Setting::getValue('delay_charge_percent', 10);
        
        $activeProject = \App\Models\Project::active();
        if ($activeProject) {
            // Project rate override removed to keep Settings as the single source of truth
            $wwAmount        = $activeProject->ww_amount;
            $wwCutoff        = $activeProject->ww_cutoff_date;
            $delayPct        = $activeProject->delay_percent;
        }

        // ── CATEGORY STATS (Dynamic with defaults for B and E from Settings) ──
        $categoryStatsRaw = Allottee::select(
            'category', 
            DB::raw('COUNT(*) as count'), 
            DB::raw('MAX(covered_area) as typical_area'),
            DB::raw('SUM(covered_area) as total_area')
        )->active()->whereNotNull('category')->where('category', '!=', '')->groupBy('category')->orderBy('category')->get();

        $areaB = (float) Setting::getValue('area_b', 1496);
        $areaE = (float) Setting::getValue('area_e', 912);

        $categoryStats = [
            'B' => (object) [
                'name' => 'B',
                'count' => 0,
                'typical_area' => $areaB,
                'monthly_per_unit' => $areaB * $maintenanceRate,
                'yearly_per_unit' => $areaB * $maintenanceRate * 12,
                'total_monthly' => 0
            ],
            'E' => (object) [
                'name' => 'E',
                'count' => 0,
                'typical_area' => $areaE,
                'monthly_per_unit' => $areaE * $maintenanceRate,
                'yearly_per_unit' => $areaE * $maintenanceRate * 12,
                'total_monthly' => 0
            ]
        ];

        $totalMonthlyBilling = 0;
        foreach ($categoryStatsRaw as $cat) {
            $catName = strtoupper(trim($cat->category));
            
            if ($catName === 'B') {
                $typicalArea = $areaB;
            } elseif ($catName === 'E') {
                $typicalArea = $areaE;
            } else {
                $typicalArea = $cat->typical_area > 0 ? $cat->typical_area : 0;
            }

            $catMonthly = $cat->count * $typicalArea * $maintenanceRate;
            $totalMonthlyBilling += $catMonthly;

            $categoryStats[$catName] = (object) [
                'name' => $catName,
                'count' => $cat->count,
                'typical_area' => $typicalArea,
                'monthly_per_unit' => $typicalArea * $maintenanceRate,
                'yearly_per_unit' => $typicalArea * $maintenanceRate * 12,
                'total_monthly' => $catMonthly
            ];
        }

        $totalAllottees = Allottee::active()->count();
        
        // ── YEARLY ACTUAL VS FORECAST ─────────────────────────────────
        $forecastYearly = $totalMonthlyBilling * 12;
        $actualYearly   = (float) \App\Models\PaymentTransaction::whereYear('payment_date', date('Y'))->sum('amount_paid');


        // ── W&W ELIGIBILITY BREAKDOWN ──────────────────────────────────
        $allAllottees = Allottee::where('status', 'active')->select('possession_date')->get();
        $totalWWRecoverable = 0;
        $wwBeforeCount = 0;
        $wwAfterCount = 0;
        $wwNullCount = 0;
        
        $wwStartDate = Carbon::create(2023, 7, 1);
        $now = Carbon::now();

        foreach ($allAllottees as $a) {
            $endDate = $a->possession_date ? clone $a->possession_date : $now;
            if ($a->possession_date) {
                if ($a->possession_date->lt($wwStartDate)) {
                    $wwBeforeCount++;
                } else {
                    $wwAfterCount++;
                }
            } else {
                $wwNullCount++;
            }
            
            $months = 0;
            if ($endDate->gt($wwStartDate)) {
                $months = $wwStartDate->diffInMonths($endDate);
            }
            $totalWWRecoverable += ($months * $wwAmount);
        }
        
        $wwBeforeAmount = 0;
        $wwAfterAmount = 0; // We will omit exact splits for dashboard simplification
        $wwNullAmount = 0;

        // ── BILLING SUMMARY ────────────────────────────────────────────
        $subtotal          = $totalMonthlyBilling + $totalWWRecoverable;
        $totalDelayCharges = $subtotal * $delayPct / 100;
        $grandTotal        = $subtotal + $totalDelayCharges;

        // ── PAYMENT VS PENDING (Filtered by Fiscal Year) ───────────────
        $fyStart = substr($fiscalYear, 0, 4) . '-07'; // e.g. 2025-07
        $fyEnd   = "20" . substr($fiscalYear, -2) . '-06'; // e.g. 2026-06
        
        $fyStartDate = Carbon::createFromFormat('Y-m', $fyStart)->startOfMonth()->format('Y-m-d');
        $fyEndDate   = Carbon::createFromFormat('Y-m', $fyEnd)->endOfMonth()->format('Y-m-d');

        $totalPaid    = (float) \App\Models\PaymentTransaction::whereBetween('payment_date', [$fyStartDate, $fyEndDate])->sum('amount_paid');

        // Sum the pending amounts from the latest bill of each allottee generated within this FY
        $latestBillIds = Bill::withoutGlobalScopes()
            ->select(DB::raw('MAX(id) as id'))
            ->whereBetween('bill_month', [$fyStart, $fyEnd])
            ->groupBy('allottee_id')
            ->pluck('id');

        $totalPending = (float) Bill::withoutGlobalScopes()
            ->whereIn('id', $latestBillIds)
            ->sum(DB::raw('CASE WHEN (total_amount - paid_amount) > 0 THEN (total_amount - paid_amount) ELSE 0 END'));

        // ── DEFAULTERS ─────────────────────────────────────────────────
        $threshold       = (int) Setting::getValue('defaulter_months_threshold', 3);
        $topCount        = (int) Setting::getValue('defaulter_top_count', 10);
        $totalDefaulters = Allottee::active()->where('overdue_months', '>=', $threshold)->count();
        $defaulters      = Allottee::active()
                                    ->with('property')
                                    ->where('overdue_months', '>=', $threshold)
                                    ->orderByDesc('total_maintenance_charges')
                                    ->limit($topCount)->get();

        // ── MONTHLY BILLING TREND (last 6 months) ─────────────────────
        $trendData = [];
        $endDateSetting = Setting::getValue('current_billing_month', '2026-07');
        $endDate = Carbon::createFromFormat('Y-m', $endDateSetting)->endOfMonth();
        for ($i = 5; $i >= 0; $i--) {
            $month    = $endDate->copy()->subMonths($i);
            $monthEnd = $month->copy()->endOfMonth()->format('Y-m-d');

            $monthData = [
                'label' => $month->format('M-y'),
                'total' => 0
            ];
            
            foreach ($categoryStats as $cat) {
                $count = Allottee::active()
                    ->where('category', $cat->name)
                    ->where(function($q) use ($monthEnd) {
                        $q->whereNull('possession_date')
                          ->orWhere('possession_date', '<=', $monthEnd);
                    })->count();
                
                $typicalArea = ($cat->name === 'B') ? $areaB : (($cat->name === 'E') ? $areaE : $cat->typical_area);
                $totalForCat = $count * $typicalArea * $maintenanceRate;
                    
                $monthData[$cat->name] = round($totalForCat);
                $monthData['total'] += round($totalForCat);
            }
            $trendData[] = $monthData;
        }

        // ── CITY-WISE ─────────────────────────────────────────────────
        $cityGroups = Allottee::active()->get()->groupBy('city');
        $cityData = [];
        foreach ($cityGroups as $cityName => $allotteesInCity) {
            $cityName = $cityName ?: 'Unknown';
            $monthlyBilling = 0;
            
            foreach ($allotteesInCity as $allottee) {
                $catName = strtoupper(trim($allottee->category));
                $typicalArea = ($catName === 'B') ? $areaB : (($catName === 'E') ? $areaE : $allottee->covered_area);
                $monthlyBilling += $typicalArea * $maintenanceRate;
            }
            $yearlyBilling = $monthlyBilling * 12;
            
            $cityData[] = (object) [
                'city' => $cityName,
                'count' => $allotteesInCity->count(),
                'monthly_billing' => $monthlyBilling,
                'yearly_billing' => $yearlyBilling
            ];
        }
        $cityData = collect($cityData)->sortByDesc('count')->values();



        // ── SAMPLE ALLOTTEES (bottom table, top 15 by total) ──────────
        $sampleAllottees = Allottee::active()->with('property')->orderByDesc('total_maintenance_charges')->limit(15)->get();

        // ── BPS + DUE MONTHS + POSSESSION (for charts) ───────────────
        $bpsCounts = [];
        for ($i = 1; $i <= 22; $i++) {
            $bpsCounts["BPS $i"] = 0;
        }
        $bpsCounts["General Public (GP)"] = 0;
        $bpsCounts["Federal Govt (FG)"] = 0;
        $bpsCounts["Other Quotas"] = 0;

        $allAllottees = Allottee::active()->select('bps', 'gp')->get();
        foreach ($allAllottees as $a) {
            $bpsRaw = trim($a->bps ?? '');
            $gpRaw = strtoupper(trim($a->gp ?? ''));

            if ($bpsRaw === '') {
                if ($gpRaw === 'YES' || $gpRaw === 'GP') {
                    $bpsCounts["General Public (GP)"]++;
                } else {
                    $bpsCounts["General Public (GP)"]++;
                }
                continue;
            }

            if (strtoupper($bpsRaw) === 'GP' || str_contains(strtoupper($bpsRaw), 'GENERAL')) {
                $bpsCounts["General Public (GP)"]++;
                continue;
            }

            if (in_array(strtoupper($bpsRaw), ['F', 'FG', 'FGE'])) {
                $bpsCounts["Federal Govt (FG)"]++;
                continue;
            }

            // Extract numeric grade
            if (preg_match('/\b(0?[1-9]|1[0-9]|2[0-2])\b/', $bpsRaw, $matches)) {
                $num = (int)$matches[1];
                $bpsCounts["BPS $num"]++;
            } else {
                $bpsCounts["Other Quotas"]++;
            }
        }

        // Sort keys: BPS 1..22, then GP, FG, and Other Quotas
        uksort($bpsCounts, function($a, $b) {
            $aIsBps = preg_match('/^BPS (\d+)$/', $a, $aMatches);
            $bIsBps = preg_match('/^BPS (\d+)$/', $b, $bMatches);

            if ($aIsBps && $bIsBps) {
                return (int)$aMatches[1] <=> (int)$bMatches[1];
            }
            if ($aIsBps) return -1;
            if ($bIsBps) return 1;

            $order = [
                'General Public (GP)' => 1,
                'Federal Govt (FG)' => 2,
                'Other Quotas' => 3
            ];
            $aOrd = $order[$a] ?? 99;
            $bOrd = $order[$b] ?? 99;
            return $aOrd <=> $bOrd;
        });

        $bpsDistribution = [];
        foreach ($bpsCounts as $label => $count) {
            if ($count > 0) {
                $bpsDistribution[] = (object)[
                    'label' => $label,
                    'count' => $count
                ];
            }
        }

        $monthsDistribution = Allottee::select('overdue_months', DB::raw('count(*) as count'))
                                ->active()->whereNotNull('overdue_months')->groupBy('overdue_months')
                                ->orderBy('overdue_months')->get();
        $possessionTimeline = Allottee::select(
                                DB::raw("strftime('%Y', possession_date) as year"),
                                DB::raw("strftime('%Y-%m', possession_date) as month"),
                                DB::raw('count(*) as count')
                                )
                                ->active()
                                ->whereNotNull('possession_date')
                                ->groupBy('month')
                                ->orderBy('month')
                                ->get();

        // ── BLOCK-WISE ANALYTICS ──────────────────────────────────────
        $blockData = Allottee::select(
            'category',
            'block_no',
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN temporary_occupancy IS NOT NULL AND temporary_occupancy != '' AND temporary_occupancy != '0' THEN 1 ELSE 0 END) as temp_occ"),
            DB::raw("SUM(CASE WHEN handed_over IS NOT NULL AND handed_over != '' AND handed_over != '0' THEN 1 ELSE 0 END) as handed_over"),
            DB::raw("SUM(CASE WHEN transfer IS NOT NULL AND transfer != '' AND transfer != '0' THEN 1 ELSE 0 END) as transferred"),
            DB::raw('SUM(covered_area) * ' . $maintenanceRate . ' as monthly_billing')
        )
        ->active()
        ->whereNotNull('block_no')
        ->groupBy('category', 'block_no')
        ->orderBy('category')
        ->orderByRaw('CAST(block_no AS INTEGER) ASC')
        ->get();

        foreach ($blockData as $block) {
            $catName = strtoupper(trim($block->category));
            $typicalArea = ($catName === 'B') ? $areaB : (($catName === 'E') ? $areaE : 0);
            if ($typicalArea > 0) {
                $block->monthly_billing = $block->total * $typicalArea * $maintenanceRate;
            }
        }

        // Block KPIs
        $blockWithMaxAllottees = $blockData->sortByDesc('total')->first();
        $totalHandedOver       = Allottee::active()->whereNotNull('handed_over')
            ->where('handed_over', '!=', '')->where('handed_over', '!=', '0')->count();
        $totalTempOcc          = Allottee::active()->whereNotNull('temporary_occupancy')
            ->where('temporary_occupancy', '!=', '')->where('temporary_occupancy', '!=', '0')->count();
        $totalTransferred      = Allottee::active()->whereNotNull('transfer')
            ->where('transfer', '!=', '')->where('transfer', '!=', '0')->count();

        $parkingRate = (float) Setting::getValue('parking_charges_rate', 500);
        $waterRate = (float) Setting::getValue('water_charges_rate', 1000);

        // Convert categoryStats to a sequential array to ensure JSON encoding outputs a Javascript array for ApexCharts JS mapping
        $categoryStats = array_values($categoryStats);

        // Load latest integrity report and last failure details via service
        $integrityService = app(\App\Services\SystemIntegrityService::class);
        $latestReport = $integrityService->getLatestReport();
        $lastFailure = $integrityService->getLastFailure();

        return view('dashboard.index', compact(
            'totalAllottees', 'categoryStats',
            'maintenanceRate', 'wwAmount', 'wwCutoff', 'delayPct', 'parkingRate', 'waterRate',
            'totalMonthlyBilling', 'forecastYearly', 'actualYearly',
            'wwBeforeCount', 'wwAfterCount', 'wwNullCount',
            'wwBeforeAmount', 'wwAfterAmount', 'wwNullAmount', 'totalWWRecoverable',
            'subtotal', 'totalDelayCharges', 'grandTotal',
            'totalPaid', 'totalPending',
            'threshold', 'topCount', 'totalDefaulters', 'defaulters',
            'trendData', 'cityData',
            'sampleAllottees', 'bpsDistribution', 'monthsDistribution', 'possessionTimeline',
            'settings',
            // block analytics
            'blockData', 'blockWithMaxAllottees',
            'totalHandedOver', 'totalTempOcc', 'totalTransferred',
            'fiscalYear', 'latestReport', 'lastFailure'
        ));
    }

    public function runHealthScan()
    {
        \Illuminate\Support\Facades\Artisan::call('system:integrity-check');
        return redirect()->route('dashboard')->with('success', 'Database health audit scan completed successfully.');
    }
}
