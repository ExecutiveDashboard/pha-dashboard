<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');

        // ── BILLING RATES (from settings) ──────────────────────────────
        $maintenanceRate = (float) Setting::getValue('maintenance_rate_per_sqft', 3.07);
        $wwAmount        = (float) Setting::getValue('watch_ward_amount', 10000);
        $wwCutoff        = Setting::getValue('watch_ward_cutoff_date', '2023-07-23');
        $delayPct        = (float) Setting::getValue('delay_charge_percent', 10);
        $areaB           = (int)   Setting::getValue('area_b', 1496);
        $areaE           = (int)   Setting::getValue('area_e', 972);

        // ── ALLOTTEE COUNTS ────────────────────────────────────────────
        $totalAllottees = Allottee::count();
        $totalB         = Allottee::where('category', 'B')->count();
        $totalE         = Allottee::where('category', 'E')->count();

        // ── STANDARD CHARGES TABLE (from settings) ────────────────────
        $monthlyB  = $areaB * $maintenanceRate;
        $monthlyE  = $areaE * $maintenanceRate;
        $yearlyB   = $monthlyB * 12;
        $yearlyE   = $monthlyE * 12;

        // ── TOTAL MONTHLY BILLING (estimated) ─────────────────────────
        $totalMonthlyB       = (float) DB::table('allottees')->where('category','B')->sum(DB::raw('covered_area * ' . $maintenanceRate));
        $totalMonthlyE       = (float) DB::table('allottees')->where('category','E')->sum(DB::raw('covered_area * ' . $maintenanceRate));
        $totalMonthlyBilling = $totalMonthlyB + $totalMonthlyE;
        $totalYearlyBilling  = $totalMonthlyBilling * 12;

        // ── W&W ELIGIBILITY BREAKDOWN ──────────────────────────────────
        $wwBeforeCount  = Allottee::whereNotNull('possession_date')
                                   ->where('possession_date', '<', $wwCutoff)->count();
        $wwAfterCount   = Allottee::where('possession_date', '>=', $wwCutoff)->count();
        $wwNullCount    = Allottee::whereNull('possession_date')->count();

        $wwBeforeAmount     = 0;
        $wwAfterAmount      = $wwAfterCount * $wwAmount;
        $wwNullAmount       = $wwNullCount  * $wwAmount;
        $totalWWRecoverable = $wwAfterAmount + $wwNullAmount;

        // ── BILLING SUMMARY ────────────────────────────────────────────
        $subtotal          = $totalMonthlyBilling + $totalWWRecoverable;
        $totalDelayCharges = $subtotal * $delayPct / 100;
        $grandTotal        = $subtotal + $totalDelayCharges;

        // ── PAYMENT VS PENDING ─────────────────────────────────────────
        $totalPaid    = (float) Allottee::sum('amount_paid');
        $totalPending = (float) Allottee::sum('total_maintenance_charges') - $totalPaid;

        // ── DEFAULTERS ─────────────────────────────────────────────────
        $threshold       = (int) Setting::getValue('defaulter_months_threshold', 3);
        $topCount        = (int) Setting::getValue('defaulter_top_count', 10);
        $totalDefaulters = Allottee::where('due_months', '>=', $threshold)->count();
        $defaulters      = Allottee::where('due_months', '>=', $threshold)
                                    ->orderByDesc('total_maintenance_charges')
                                    ->limit($topCount)->get();

        // ── MONTHLY BILLING TREND (last 6 months) ─────────────────────
        $trendData = [];
        $endDate   = Carbon::create(2025, 5, 31);
        for ($i = 5; $i >= 0; $i--) {
            $month    = $endDate->copy()->subMonths($i);
            $monthEnd = $month->copy()->endOfMonth()->format('Y-m-d');

            $bTotal = DB::selectOne(
                "SELECT COALESCE(SUM(covered_area),0)*? as total FROM allottees
                 WHERE category='B' AND (possession_date IS NULL OR possession_date <= ?)",
                [$maintenanceRate, $monthEnd]
            );
            $eTotal = DB::selectOne(
                "SELECT COALESCE(SUM(covered_area),0)*? as total FROM allottees
                 WHERE category='E' AND (possession_date IS NULL OR possession_date <= ?)",
                [$maintenanceRate, $monthEnd]
            );
            $b = round((float)$bTotal->total);
            $e = round((float)$eTotal->total);
            $trendData[] = [
                'label' => $month->format('M-y'),
                'B'     => $b,
                'E'     => $e,
                'total' => $b + $e,
            ];
        }

        // ── CATEGORY BILLING SPLIT (donut) ────────────────────────────
        $billingByCategory = [
            'B' => $totalMonthlyB,
            'E' => $totalMonthlyE,
        ];

        // ── CITY-WISE ─────────────────────────────────────────────────
        $cityData = Allottee::select(
            'city',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(covered_area) * ' . $maintenanceRate . ' as monthly_billing'),
            DB::raw('SUM(covered_area) * ' . $maintenanceRate . ' * 12 as yearly_billing')
        )->groupBy('city')->orderByDesc('count')->get();

        // ── POLICY LOGIC SUMMARY ───────────────────────────────────────
        $policyBefore = [
            'count'   => $wwBeforeCount,
            'monthly' => round(Allottee::whereNotNull('possession_date')
                ->where('possession_date', '<', $wwCutoff)
                ->sum(DB::raw('covered_area')) * $maintenanceRate),
            'ww'      => 0,
        ];
        $policyAfter = [
            'count'   => $wwAfterCount,
            'monthly' => round(Allottee::where('possession_date', '>=', $wwCutoff)
                ->sum(DB::raw('covered_area')) * $maintenanceRate),
            'ww'      => $wwAfterAmount,
        ];
        $policyNull = [
            'count'   => $wwNullCount,
            'monthly' => round(Allottee::whereNull('possession_date')
                ->sum(DB::raw('covered_area')) * $maintenanceRate),
            'ww'      => $wwNullAmount,
        ];
        $policyTotal = [
            'count'   => $totalAllottees,
            'monthly' => $policyBefore['monthly'] + $policyAfter['monthly'] + $policyNull['monthly'],
            'ww'      => $wwAfterAmount + $wwNullAmount,
        ];

        // ── SAMPLE ALLOTTEES (bottom table, top 15 by total) ──────────
        $sampleAllottees = Allottee::orderByDesc('total_maintenance_charges')->limit(15)->get();

        // ── BPS + DUE MONTHS (for charts) ────────────────────────────
        $bpsDistribution    = Allottee::select('bps', DB::raw('count(*) as count'))
                                ->whereNotNull('bps')->groupBy('bps')->orderBy('bps')->get();
        $monthsDistribution = Allottee::select('due_months', DB::raw('count(*) as count'))
                                ->whereNotNull('due_months')->groupBy('due_months')
                                ->orderBy('due_months')->get();

        // ── BLOCK-WISE ANALYTICS ──────────────────────────────────────
        $blockData = Allottee::select(
            'block_no',
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN temporary_occupancy IS NOT NULL AND temporary_occupancy != '' AND temporary_occupancy != '0' THEN 1 ELSE 0 END) as temp_occ"),
            DB::raw("SUM(CASE WHEN handed_over IS NOT NULL AND handed_over != '' AND handed_over != '0' THEN 1 ELSE 0 END) as handed_over"),
            DB::raw("SUM(CASE WHEN transfer IS NOT NULL AND transfer != '' AND transfer != '0' THEN 1 ELSE 0 END) as transferred"),
            DB::raw('SUM(covered_area) * ' . $maintenanceRate . ' as monthly_billing')
        )
        ->whereNotNull('block_no')
        ->groupBy('block_no')
        ->orderBy('block_no')
        ->get();

        // Block KPIs
        $blockWithMaxAllottees = $blockData->sortByDesc('total')->first();
        $totalHandedOver       = Allottee::whereNotNull('handed_over')
            ->where('handed_over', '!=', '')->where('handed_over', '!=', '0')->count();
        $totalTempOcc          = Allottee::whereNotNull('temporary_occupancy')
            ->where('temporary_occupancy', '!=', '')->where('temporary_occupancy', '!=', '0')->count();
        $totalTransferred      = Allottee::whereNotNull('transfer')
            ->where('transfer', '!=', '')->where('transfer', '!=', '0')->count();

        return view('dashboard.index', compact(
            'totalAllottees', 'totalB', 'totalE',
            'areaB', 'areaE', 'maintenanceRate', 'wwAmount', 'wwCutoff', 'delayPct',
            'monthlyB', 'monthlyE', 'yearlyB', 'yearlyE',
            'totalMonthlyB', 'totalMonthlyE', 'totalMonthlyBilling', 'totalYearlyBilling',
            'wwBeforeCount', 'wwAfterCount', 'wwNullCount',
            'wwBeforeAmount', 'wwAfterAmount', 'wwNullAmount', 'totalWWRecoverable',
            'subtotal', 'totalDelayCharges', 'grandTotal',
            'totalPaid', 'totalPending',
            'threshold', 'topCount', 'totalDefaulters', 'defaulters',
            'trendData', 'billingByCategory', 'cityData',
            'policyBefore', 'policyAfter', 'policyNull', 'policyTotal',
            'sampleAllottees', 'bpsDistribution', 'monthsDistribution',
            'settings',
            // block analytics
            'blockData', 'blockWithMaxAllottees',
            'totalHandedOver', 'totalTempOcc', 'totalTransferred'
        ));
    }
}
