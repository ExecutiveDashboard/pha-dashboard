<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemIntegrityService
{
    /**
     * Get the latest integrity report.
     *
     * @return array|null
     */
    public function getLatestReport()
    {
        $reportDir = storage_path('app/integrity-reports');
        if (file_exists($reportDir)) {
            $files = glob($reportDir . '/integrity_report_*.json');
            if (!empty($files)) {
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                return json_decode(file_get_contents($files[0]), true);
            }
        }
        return null;
    }

    /**
     * Get the last recorded system health failure metadata.
     *
     * @return array|null
     */
    public function getLastFailure()
    {
        $file = storage_path('app/integrity-reports/last_failure.json');
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }

    /**
     * Run all system integrity audits and output reports.
     *
     * @return array
     */
    public function run()
    {
        $startTime = microtime(true);
        $results = [];
        $hasCriticalFailure = false;

        $sections = [
            'Property Integrity' => ['weight' => 20, 'score' => 20, 'deduction' => 0],
            'Ownership Integrity' => ['weight' => 20, 'score' => 20, 'deduction' => 0],
            'Billing Integrity' => ['weight' => 20, 'score' => 20, 'deduction' => 0],
            'Payment Integrity' => ['weight' => 15, 'score' => 15, 'deduction' => 0],
            'Tenant Integrity' => ['weight' => 10, 'score' => 10, 'deduction' => 0],
            'Complaint Integrity' => ['weight' => 5, 'score' => 5, 'deduction' => 0],
            'Dashboard Integrity' => ['weight' => 5, 'score' => 5, 'deduction' => 0],
            'Block Visual Integrity' => ['weight' => 5, 'score' => 5, 'deduction' => 0],
        ];

        // 1. DYNAMIC LEGACY DETECTION
        $allProjects = DB::table('projects')->get();
        $legacyProjectIds = [];
        $normalizedProjectIds = [];

        foreach ($allProjects as $project) {
            $propCount = DB::table('properties')->where('project_id', $project->id)->count();
            if ($propCount <= 1) {
                $legacyProjectIds[] = $project->id;
            } else {
                $normalizedProjectIds[] = $project->id;
            }
        }

        $legacyPlaceholder = empty($legacyProjectIds) ? '0' : implode(',', $legacyProjectIds);

        // log function
        $logCheck = function($section, $name, $severity, $status, $expected, $actual, $affected, $action = '') use (&$results, &$hasCriticalFailure, &$sections) {
            $results[] = [
                'section' => $section,
                'name' => $name,
                'severity' => $severity,
                'status' => $status,
                'expected' => $expected,
                'actual' => $actual,
                'affected' => $affected,
                'action' => $action
            ];
            
            if ($status === 'FAIL') {
                if ($severity === 'CRITICAL') {
                    $hasCriticalFailure = true;
                }
                
                $deduction = 0;
                if ($severity === 'CRITICAL') {
                    $deduction = $sections[$section]['weight'];
                } elseif ($severity === 'HIGH') {
                    $deduction = $sections[$section]['weight'] * 0.5;
                } elseif ($severity === 'MEDIUM') {
                    $deduction = $sections[$section]['weight'] * 0.25;
                } elseif ($severity === 'LOW') {
                    $deduction = $sections[$section]['weight'] * 0.1;
                }
                $sections[$section]['deduction'] += $deduction;
            }
        };

        // PROPERTY INTEGRITY (Weight: 20)
        // 1.1 Property Coordinate Uniqueness
        $dupCoords = DB::select("
            SELECT project_id, block_no, flat_no, category, COUNT(*) as dup_count
            FROM properties
            GROUP BY project_id, block_no, flat_no, category
            HAVING dup_count > 1
        ");
        $dupCount = count($dupCoords);
        $logCheck('Property Integrity', 'Duplicate Property Coordinates', 'CRITICAL', $dupCount > 0 ? 'FAIL' : 'PASS', 0, $dupCount, $dupCount, $dupCount > 0 ? 'Resolve duplicate records in properties table.' : '');

        // 1.2 Allotted Properties Without Active Owners
        $unownedProps = DB::select("
            SELECT id FROM properties
            WHERE status = 'Allotted' AND project_id NOT IN ({$legacyPlaceholder})
              AND id NOT IN (SELECT DISTINCT property_id FROM allottees WHERE status = 'active' AND property_id IS NOT NULL)
        ");
        $unownedCount = count($unownedProps);
        $logCheck('Property Integrity', 'Allotted Properties Without Active Owners', 'HIGH', $unownedCount > 0 ? 'FAIL' : 'PASS', 0, $unownedCount, $unownedCount, $unownedCount > 0 ? 'Assign active allottee or set status to Available.' : '');

        // 1.3 Property Project Reference Validity
        $orphanProps = DB::select("SELECT id FROM properties WHERE project_id NOT IN (SELECT id FROM projects)");
        $orphanPropsCount = count($orphanProps);
        $logCheck('Property Integrity', 'Property Linked to Invalid Project ID', 'CRITICAL', $orphanPropsCount > 0 ? 'FAIL' : 'PASS', 0, $orphanPropsCount, $orphanPropsCount, $orphanPropsCount > 0 ? 'Map properties to valid project IDs.' : '');

        // OWNERSHIP INTEGRITY (Weight: 20)
        // 2.1 Multiple active allottees linked to one property
        $multiActiveOwners = DB::select("
            SELECT property_id, COUNT(*) as active_count
            FROM allottees
            WHERE status = 'active' AND property_id IS NOT NULL AND project_id NOT IN ({$legacyPlaceholder})
            GROUP BY property_id HAVING active_count > 1
        ");
        $multiOwnerCount = count($multiActiveOwners);
        $logCheck('Ownership Integrity', 'Multiple Active Owners per Property', 'CRITICAL', $multiOwnerCount > 0 ? 'FAIL' : 'PASS', 0, $multiOwnerCount, $multiOwnerCount, $multiOwnerCount > 0 ? 'Mark previous historical owners as inactive.' : '');

        // 2.2 Active allottee without a valid property_id
        $orphanAllottees = DB::select("
            SELECT id FROM allottees
            WHERE status = 'active' AND project_id NOT IN ({$legacyPlaceholder})
              AND (property_id IS NULL OR property_id NOT IN (SELECT id FROM properties))
        ");
        $orphanCount = count($orphanAllottees);
        $logCheck('Ownership Integrity', 'Active Allottee Without Valid Property ID', 'CRITICAL', $orphanCount > 0 ? 'FAIL' : 'PASS', 0, $orphanCount, $orphanCount, $orphanCount > 0 ? 'Re-link active owner to correct physical property ID.' : '');

        // 2.3 Transfer Chain Deactivation Compliance
        $transferViolations = DB::select("
            SELECT h.id FROM property_ownership_history h
            JOIN allottees a ON h.previous_owner_id = a.id
            WHERE a.status = 'active'
        ");
        $transferViolationCount = count($transferViolations);
        $logCheck('Ownership Integrity', 'Transfer Chain Deactivation Compliance', 'HIGH', $transferViolationCount > 0 ? 'FAIL' : 'PASS', 0, $transferViolationCount, $transferViolationCount, $transferViolationCount > 0 ? 'Deactivate previous owners in transfer chain.' : '');

        // 2.4 Active owner counts matching physical property inventory
        $inventoryMismatches = DB::select("
            SELECT p.project_id FROM (
                SELECT project_id, category, COUNT(*) as prop_count FROM properties GROUP BY project_id, category
            ) p
            LEFT JOIN (
                SELECT prop.project_id, prop.category, COUNT(a.id) as allottee_count 
                FROM allottees a
                INNER JOIN properties prop ON a.property_id = prop.id
                WHERE a.status = 'active' 
                GROUP BY prop.project_id, prop.category
            ) a ON p.project_id = a.project_id AND (p.category = a.category OR (p.category IS NULL AND a.category IS NULL))
            WHERE p.project_id NOT IN ({$legacyPlaceholder}) AND p.prop_count != COALESCE(a.allottee_count, 0)
        ");
        $mismatchCount = count($inventoryMismatches);
        $logCheck('Ownership Integrity', 'Allottee vs. Property Inventory Match', 'CRITICAL', $mismatchCount > 0 ? 'FAIL' : 'PASS', 0, $mismatchCount, $mismatchCount, $mismatchCount > 0 ? 'Realign active owners with property counts.' : '');

        // BILLING INTEGRITY (Weight: 20)
        // 3.1 Duplicate active bills for the same property/month/year
        $dupBills = DB::select("
            SELECT a.property_id FROM bills b
            JOIN allottees a ON b.allottee_id = a.id
            WHERE a.status = 'active' AND a.property_id IS NOT NULL AND a.project_id NOT IN ({$legacyPlaceholder})
            GROUP BY a.property_id, b.bill_month HAVING COUNT(*) > 1
        ");
        $dupBillCount = count($dupBills);
        $logCheck('Billing Integrity', 'Duplicate Active Bills per Month', 'CRITICAL', $dupBillCount > 0 ? 'FAIL' : 'PASS', 0, $dupBillCount, $dupBillCount, $dupBillCount > 0 ? 'Audit billing generation; remove duplicate entries.' : '');

        // 3.2 Bills without valid allottee/property references
        $orphanBills = DB::select("
            SELECT b.id FROM bills b
            LEFT JOIN allottees a ON b.allottee_id = a.id
            WHERE a.id IS NULL
               OR (a.property_id IS NULL AND a.project_id NOT IN ({$legacyPlaceholder}))
               OR (a.property_id NOT IN (SELECT id FROM properties) AND a.project_id NOT IN ({$legacyPlaceholder}))
        ");
        $orphanBillCount = count($orphanBills);
        $logCheck('Billing Integrity', 'Bills Without Valid Allottee/Property Link', 'CRITICAL', $orphanBillCount > 0 ? 'FAIL' : 'PASS', 0, $orphanBillCount, $orphanBillCount, $orphanBillCount > 0 ? 'Clean up billing records with invalid targets.' : '');

        // 3.3 Bills belonging to inactive owners
        $inactiveOwnersBills = DB::select("
            SELECT b.id FROM bills b
            JOIN allottees a ON b.allottee_id = a.id
            WHERE a.status != 'active'
        ");
        $inactiveBillsCount = count($inactiveOwnersBills);
        $logCheck('Billing Integrity', 'Bills Linked to Inactive Owner Accounts', 'HIGH', $inactiveBillsCount > 0 ? 'FAIL' : 'PASS', 0, $inactiveBillsCount, $inactiveBillsCount, $inactiveBillsCount > 0 ? 'Link or re-generate bills only for current active owners.' : '');

        // PAYMENT INTEGRITY (Weight: 15)
        // 4.1 Payments <= Bill Amount
        $excessPayments = DB::select("SELECT id FROM bills WHERE paid_amount > total_amount");
        $excessCount = count($excessPayments);
        $logCheck('Payment Integrity', 'Payments Exceeding Bill Amount', 'HIGH', $excessCount > 0 ? 'FAIL' : 'PASS', 0, $excessCount, $excessCount, $excessCount > 0 ? 'Adjust ledger mappings or credit balances.' : '');

        // 4.2 Payments linked to nonexistent bills
        $orphanPayments = DB::select("SELECT id FROM payment_transactions WHERE bill_id IS NOT NULL AND bill_id NOT IN (SELECT id FROM bills)");
        $orphanPaymentCount = count($orphanPayments);
        $logCheck('Payment Integrity', 'Payments Linked to Non-Existent Bills', 'HIGH', $orphanPaymentCount > 0 ? 'FAIL' : 'PASS', 0, $orphanPaymentCount, $orphanPaymentCount, $orphanPaymentCount > 0 ? 'Link payment records to valid billing items.' : '');

        // TENANT INTEGRITY (Weight: 10)
        // 5.1 Active tenants without valid allottee/property
        $invalidTenants = DB::select("
            SELECT t.id FROM tenant_records t
            LEFT JOIN properties p ON t.property_id = p.id
            LEFT JOIN allottees a ON t.allottee_id = a.id
            WHERE t.is_active = 1
              AND (a.id IS NULL OR a.status != 'active' OR (p.id IS NULL AND t.project_id NOT IN ({$legacyPlaceholder})))
        ");
        $invalidTenantCount = count($invalidTenants);
        $logCheck('Tenant Integrity', 'Active Tenants Without Valid Allottee/Property', 'HIGH', $invalidTenantCount > 0 ? 'FAIL' : 'PASS', 0, $invalidTenantCount, $invalidTenantCount, $invalidTenantCount > 0 ? 'Deactivate or re-associate orphaned tenant profiles.' : '');

        // 5.2 Multiple active tenants per property
        $multiActiveTenants = DB::select("
            SELECT property_id FROM tenant_records
            WHERE is_active = 1 AND property_id IS NOT NULL
            GROUP BY property_id HAVING COUNT(*) > 1
        ");
        $multiTenantCount = count($multiActiveTenants);
        $logCheck('Tenant Integrity', 'Multiple Active Tenants per Property', 'HIGH', $multiTenantCount > 0 ? 'FAIL' : 'PASS', 0, $multiTenantCount, $multiTenantCount, $multiTenantCount > 0 ? 'Deactivate historical tenant records.' : '');

        // COMPLAINT INTEGRITY (Weight: 5)
        // 6.1 Complaints referencing invalid allottee/property
        $invalidComplaints = DB::select("
            SELECT c.id FROM complaints c
            LEFT JOIN allottees a ON c.allottee_id = a.id
            WHERE a.id IS NULL
               OR (a.property_id IS NULL AND c.project_id NOT IN ({$legacyPlaceholder}))
               OR (a.property_id NOT IN (SELECT id FROM properties) AND c.project_id NOT IN ({$legacyPlaceholder}))
        ");
        $invalidComplaintCount = count($invalidComplaints);
        $logCheck('Complaint Integrity', 'Complaints Referencing Invalid Owner/Property', 'MEDIUM', $invalidComplaintCount > 0 ? 'FAIL' : 'PASS', 0, $invalidComplaintCount, $invalidComplaintCount, $invalidComplaintCount > 0 ? 'Map complaint tickets to valid owners and properties.' : '');

        // DASHBOARD INTEGRITY (Weight: 5)
        $actualB = DB::table('allottees')
            ->join('properties', 'allottees.property_id', '=', 'properties.id')
            ->where('allottees.project_id', 1)
            ->where('properties.category', 'B')
            ->where('allottees.status', 'active')
            ->count();
        $actualE = DB::table('allottees')
            ->join('properties', 'allottees.property_id', '=', 'properties.id')
            ->where('allottees.project_id', 1)
            ->where('properties.category', 'E')
            ->where('allottees.status', 'active')
            ->count();
        $actualTotal = DB::table('allottees')->where('project_id', 1)->where('status', 'active')->count();

        $logCheck('Dashboard Integrity', 'Project 1 Category B Active Count', 'HIGH', $actualB != 672 ? 'FAIL' : 'PASS', 672, $actualB, abs(672 - $actualB), $actualB != 672 ? 'Execute properties/allottees realignment sync.' : '');
        $logCheck('Dashboard Integrity', 'Project 1 Category E Active Count', 'HIGH', $actualE != 912 ? 'FAIL' : 'PASS', 912, $actualE, abs(912 - $actualE), $actualE != 912 ? 'Execute properties/allottees realignment sync.' : '');
        $logCheck('Dashboard Integrity', 'Project 1 Total Active Owners Count', 'HIGH', $actualTotal != 1584 ? 'FAIL' : 'PASS', 1584, $actualTotal, abs(1584 - $actualTotal), $actualTotal != 1584 ? 'Execute properties/allottees realignment sync.' : '');

        // 7.4 Category B Billing Accounts Match
        $latestBMonth = DB::table('bills')
            ->join('allottees', 'bills.allottee_id', '=', 'allottees.id')
            ->join('properties', 'allottees.property_id', '=', 'properties.id')
            ->where('allottees.project_id', 1)
            ->where('properties.category', 'B')
            ->max('bills.bill_month');
            
        $billCountB = 0;
        if ($latestBMonth) {
            $billCountB = DB::table('bills')
                ->join('allottees', 'bills.allottee_id', '=', 'allottees.id')
                ->join('properties', 'allottees.property_id', '=', 'properties.id')
                ->where('allottees.project_id', 1)
                ->where('properties.category', 'B')
                ->where('bills.bill_month', $latestBMonth)
                ->count();
        }
        $logCheck('Dashboard Integrity', 'Category B Billing Accounts Match', 'HIGH', ($latestBMonth && $billCountB != $actualB) ? 'FAIL' : 'PASS', $actualB, $billCountB, ($latestBMonth && $billCountB != $actualB) ? abs($actualB - $billCountB) : 0, 'Re-generate missing Category B bills for active owners.');

        // 7.5 Category E Billing Accounts Match
        $latestEMonth = DB::table('bills')
            ->join('allottees', 'bills.allottee_id', '=', 'allottees.id')
            ->join('properties', 'allottees.property_id', '=', 'properties.id')
            ->where('allottees.project_id', 1)
            ->where('properties.category', 'E')
            ->max('bills.bill_month');
            
        $billCountE = 0;
        if ($latestEMonth) {
            $billCountE = DB::table('bills')
                ->join('allottees', 'bills.allottee_id', '=', 'allottees.id')
                ->join('properties', 'allottees.property_id', '=', 'properties.id')
                ->where('allottees.project_id', 1)
                ->where('properties.category', 'E')
                ->where('bills.bill_month', $latestEMonth)
                ->count();
        }
        $logCheck('Dashboard Integrity', 'Category E Billing Accounts Match', 'HIGH', ($latestEMonth && $billCountE != $actualE) ? 'FAIL' : 'PASS', $actualE, $billCountE, ($latestEMonth && $billCountE != $actualE) ? abs($actualE - $billCountE) : 0, 'Re-generate missing Category E bills for active owners.');

        // BLOCK VISUAL INTEGRITY (Weight: 5)
        $blockVisualMismatches = DB::select("
            SELECT p.project_id FROM (
                SELECT project_id, block_no, category, COUNT(*) as prop_count FROM properties GROUP BY project_id, block_no, category
            ) p
            LEFT JOIN (
                SELECT prop.project_id, prop.block_no, prop.category, COUNT(a.id) as allottee_count 
                FROM allottees a
                INNER JOIN properties prop ON a.property_id = prop.id
                WHERE a.status = 'active' 
                GROUP BY prop.project_id, prop.block_no, prop.category
            ) a ON p.project_id = a.project_id AND p.block_no = a.block_no AND (p.category = a.category OR (p.category IS NULL AND a.category IS NULL))
            WHERE p.project_id NOT IN ({$legacyPlaceholder}) AND p.prop_count != COALESCE(a.allottee_count, 0)
        ");
        $blockMismatchCount = count($blockVisualMismatches);
        $logCheck('Block Visual Integrity', 'Rendered Units vs Physical Properties Balance', 'HIGH', $blockMismatchCount > 0 ? 'FAIL' : 'PASS', 0, $blockMismatchCount, $blockMismatchCount, $blockMismatchCount > 0 ? 'Align block-visual coordinates mapping.' : '');

        // CALCULATE HEALTH SCORE
        $totalWeight = 0;
        $totalScore = 0;
        foreach ($sections as $sName => $sData) {
            $score = max(0, $sData['weight'] - $sData['deduction']);
            $sections[$sName]['score'] = $score;
            $totalWeight += $sData['weight'];
            $totalScore += $score;
        }
        $healthScore = $totalWeight > 0 ? round(($totalScore / $totalWeight) * 100, 1) : 0;

        $healthStatus = 'HEALTHY';
        $healthIndicator = '🟢';
        if ($healthScore < 70) {
            $healthStatus = 'CRITICAL';
            $healthIndicator = '🔴';
        } elseif ($healthScore < 90) {
            $healthStatus = 'WARNING';
            $healthIndicator = '🟡';
        }

        // PERFORMANCE METRICS
        $endTime = microtime(true);
        $durationMs = round(($endTime - $startTime) * 1000, 2);
        
        $dbPath = config('database.connections.sqlite.database');
        $dbSize = file_exists($dbPath) ? filesize($dbPath) : 0;

        $performance = [
            'scan_duration_ms' => $durationMs,
            'database_size_bytes' => $dbSize,
            'peak_memory_usage_bytes' => memory_get_peak_usage(true),
        ];

        // VERSION METADATA
        $versionMetadata = [
            'application_version' => 'v2.4.0-production',
            'integrity_rules_version' => 'v2.2.0',
            'database_engine' => 'SQLite 3',
            'laravel_version' => app()->version(),
        ];

        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'legacy_projects' => $legacyProjectIds,
            'normalized_projects' => $normalizedProjectIds,
            'overall_status' => $healthStatus,
            'health_score' => $healthScore,
            'section_scores' => $sections,
            'performance' => $performance,
            'metadata' => $versionMetadata,
            'results' => $results
        ];

        // -----------------------------------------------------------------
        // WRITE REPORTS
        // -----------------------------------------------------------------
        $timestamp = date('Y_m_d_H_i_s');
        $reportDir = storage_path('app/integrity-reports');
        if (!file_exists($reportDir)) {
            mkdir($reportDir, 0755, true);
        }

        $jsonFile = $reportDir . "/integrity_report_{$timestamp}.json";
        $mdFile = $reportDir . "/integrity_report_{$timestamp}.md";

        file_put_contents($jsonFile, json_encode($reportData, JSON_PRETTY_PRINT));

        // MD formatting
        $mdContent = "# System Integrity Audit Report - " . date('Y-m-d H:i:s') . "\n\n";
        $mdContent .= "## System Health Score: **{$healthScore}%**  ({$healthIndicator} {$healthStatus})\n\n";
        
        $mdContent .= "### Operational Performance\n";
        $mdContent .= "* **Scan Duration**: {$durationMs} ms\n";
        $mdContent .= "* **Database File Size**: " . round($dbSize / 1024 / 1024, 2) . " MB\n";
        $mdContent .= "* **Peak Memory Usage**: " . round($performance['peak_memory_usage_bytes'] / 1024 / 1024, 2) . " MB\n\n";

        $mdContent .= "### Version Framework Metadata\n";
        $mdContent .= "* **Application Release**: {$versionMetadata['application_version']}\n";
        $mdContent .= "* **Integrity Rules Standard**: {$versionMetadata['integrity_rules_version']}\n";
        $mdContent .= "* **Framework Engine**: Laravel {$versionMetadata['laravel_version']} ({$versionMetadata['database_engine']})\n\n";

        $mdContent .= "### Project Partitioning\n";
        $mdContent .= "* **Normalized Projects**: " . (empty($normalizedProjectIds) ? 'None' : implode(', ', $normalizedProjectIds)) . "\n";
        $mdContent .= "* **Legacy Projects (Excluded)**: " . (empty($legacyProjectIds) ? 'None' : implode(', ', $legacyProjectIds)) . "\n\n";
        
        $mdContent .= "### Section-Wise Health Scores\n";
        foreach ($sections as $sName => $sData) {
            $scorePercent = (int) round(($sData['score'] / $sData['weight']) * 100);
            $secIndicator = $scorePercent === 100 ? '✅' : ($scorePercent >= 70 ? '⚠️' : '❌');
            $mdContent .= "* {$secIndicator} **{$sName}**: {$sData['score']}/{$sData['weight']} ({$scorePercent}%)\n";
        }
        $mdContent .= "\n## Audit Verification Results Table\n\n";
        $mdContent .= "| Section | Check Name | Severity | Status | Expected | Actual | Affected | Corrective Action |\n";
        $mdContent .= "| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :--- |\n";
        foreach ($results as $res) {
            $statusText = $res['status'] === 'PASS' ? '✅ PASS' : ($res['status'] === 'WARNING' ? '⚠️ WARNING' : '❌ FAIL');
            $mdContent .= "| {$res['section']} | {$res['name']} | {$res['severity']} | {$statusText} | {$res['expected']} | {$res['actual']} | {$res['affected']} | " . ($res['action'] ?: 'None') . " |\n";
        }
        file_put_contents($mdFile, $mdContent);

        // Track last failure details if anything fails
        $failedChecks = array_filter($results, function($r) { return $r['status'] === 'FAIL'; });
        if (!empty($failedChecks)) {
            $firstFailed = reset($failedChecks);
            $failureData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'failed_check' => $firstFailed['section'] . ': ' . $firstFailed['name'],
                'score' => $healthScore
            ];
            file_put_contents($reportDir . '/last_failure.json', json_encode($failureData, JSON_PRETTY_PRINT));

            // Log error
            Log::error("System Health Critical Failure Alert: [{$firstFailed['section']}] {$firstFailed['name']} failed integrity check. Current Health Score: {$healthScore}%.");
        }

        // ROTATION (keep last 30 reports)
        $files = glob($reportDir . '/integrity_report_*');
        if (count($files) > 60) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            $toDelete = count($files) - 60;
            for ($i = 0; $i < $toDelete; $i++) {
                @unlink($files[$i]);
            }
        }

        return $reportData;
    }

    /**
     * Get preview of auto-repairable items vs manual intervention required.
     *
     * @return array
     */
    public function getRepairPreview()
    {
        $preview = [];

        // 1. DYNAMIC LEGACY DETECTION
        $allProjects = DB::table('projects')->get();
        $legacyProjectIds = [];
        foreach ($allProjects as $project) {
            $propCount = DB::table('properties')->where('project_id', $project->id)->count();
            if ($propCount <= 1) {
                $legacyProjectIds[] = $project->id;
            }
        }
        $legacyPlaceholder = empty($legacyProjectIds) ? '0' : implode(',', $legacyProjectIds);

        // Auto-Repairable 1: Duplicate Active Bills
        $dupBills = DB::select("
            SELECT COUNT(*) as count FROM (
                SELECT a.property_id FROM bills b
                JOIN allottees a ON b.allottee_id = a.id
                WHERE a.status = 'active' AND a.property_id IS NOT NULL AND a.project_id NOT IN ({$legacyPlaceholder})
                GROUP BY a.property_id, b.bill_month HAVING COUNT(*) > 1
            )
        ");
        $preview[] = [
            'name' => 'Duplicate Active Bills per Month',
            'count' => $dupBills[0]->count ?? 0,
            'repairable' => true,
            'description' => 'System can safely keep the latest bill and delete the older duplicate bill entries.'
        ];

        // Auto-Repairable 2: Bills without valid references
        $orphanBills = DB::select("
            SELECT COUNT(*) as count FROM bills b
            LEFT JOIN allottees a ON b.allottee_id = a.id
            WHERE a.id IS NULL
               OR (a.property_id IS NULL AND a.project_id NOT IN ({$legacyPlaceholder}))
               OR (a.property_id NOT IN (SELECT id FROM properties) AND a.project_id NOT IN ({$legacyPlaceholder}))
        ");
        $preview[] = [
            'name' => 'Bills Without Valid Allottee/Property Link',
            'count' => $orphanBills[0]->count ?? 0,
            'repairable' => true,
            'description' => 'System can safely remove these orphaned billing records to restore accounting balance.'
        ];

        // Auto-Repairable 3: Payments linked to nonexistent bills
        $orphanPayments = DB::select("
            SELECT COUNT(*) as count FROM payment_transactions
            WHERE bill_id IS NOT NULL AND bill_id NOT IN (SELECT id FROM bills)
        ");
        $preview[] = [
            'name' => 'Payments Linked to Non-Existent Bills',
            'count' => $orphanPayments[0]->count ?? 0,
            'repairable' => true,
            'description' => 'System can break the invalid bill_id relationship and keep them as general unallocated payments.'
        ];

        // Auto-Repairable 4: Allotted properties without active owners
        $unownedProps = DB::select("
            SELECT COUNT(*) as count FROM properties
            WHERE status = 'Allotted' AND project_id NOT IN ({$legacyPlaceholder})
              AND id NOT IN (SELECT DISTINCT property_id FROM allottees WHERE status = 'active' AND property_id IS NOT NULL)
        ");
        $preview[] = [
            'name' => 'Allotted Properties Without Active Owners',
            'count' => $unownedProps[0]->count ?? 0,
            'repairable' => true,
            'description' => 'System can automatically reset these property status columns to Available.'
        ];

        // Manual Intervention 1: Duplicate coordinates
        $dupCoords = DB::select("
            SELECT COUNT(*) as count FROM (
                SELECT id FROM properties
                GROUP BY project_id, block_no, flat_no, category HAVING COUNT(*) > 1
            )
        ");
        $preview[] = [
            'name' => 'Duplicate Property Coordinates',
            'count' => $dupCoords[0]->count ?? 0,
            'repairable' => false,
            'description' => 'Requires database administrator intervention to merge coordinates and migrate associated allottees.'
        ];

        // Manual Intervention 2: Multiple active owners
        $multiActive = DB::select("
            SELECT COUNT(*) as count FROM (
                SELECT property_id FROM allottees
                WHERE status = 'active' AND property_id IS NOT NULL AND project_id NOT IN ({$legacyPlaceholder})
                GROUP BY property_id HAVING COUNT(*) > 1
            )
        ");
        $preview[] = [
            'name' => 'Multiple Active Owners per Property',
            'count' => $multiActive[0]->count ?? 0,
            'repairable' => false,
            'description' => 'Requires manually auditing ownership history and deactivating old entries.'
        ];

        // Manual Intervention 3: Active allottees without valid property_id
        $orphanAllottees = DB::select("
            SELECT COUNT(*) as count FROM allottees
            WHERE status = 'active' AND project_id NOT IN ({$legacyPlaceholder})
              AND (property_id IS NULL OR property_id NOT IN (SELECT id FROM properties))
        ");
        $preview[] = [
            'name' => 'Active Allottee Without Valid Property ID',
            'count' => $orphanAllottees[0]->count ?? 0,
            'repairable' => false,
            'description' => 'Requires manually setting correct property linkages for the active owner profiles.'
        ];

        return $preview;
    }
}
