<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SystemIntegrityService;

class SystemIntegrityCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:integrity-check {--repair-preview : View preview of repairable vs manual items}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform comprehensive system-wide MIS database integrity audits, health scoring, and repair previews';

    /**
     * Execute the console command.
     */
    public function handle(SystemIntegrityService $service)
    {
        // 1. REPAIR PREVIEW MODE
        if ($this->option('repair-preview')) {
            $this->info("==================================================================================");
            $this->info("                    PHA MIS SYSTEM REPAIR PREVIEW REPORT                          ");
            $this->info("==================================================================================");

            $preview = $service->getRepairPreview();
            $headers = ['Integrity Check Name', 'Affected Count', 'Action Classification', 'Resolution Path Details'];
            $tableData = [];

            foreach ($preview as $item) {
                $classText = $item['repairable'] ? '⚙️ Auto-Repairable' : '🚨 Manual Intervention Required';
                $tableData[] = [
                    $item['name'],
                    $item['count'],
                    $classText,
                    $item['description']
                ];
            }

            $this->table($headers, $tableData);
            $this->info("==================================================================================");
            return 0;
        }

        // 2. STANDARD SCAN MODE
        $this->info("==================================================================================");
        $this->info("                 PHA MIS COMPREHENSIVE SYSTEM INTEGRITY AUDIT                    ");
        $this->info("==================================================================================");

        $report = $service->run();
        
        // Print results to console
        $this->info("\nDetailed Integrity Checks Log:\n");
        $headers = ['Section', 'Check Name', 'Severity', 'Status', 'Expected', 'Actual', 'Affected', 'Action'];
        $tableData = [];

        $hasCriticalFailure = false;
        foreach ($report['results'] as $res) {
            $statusText = $res['status'] === 'PASS' ? '✅ PASS' : ($res['status'] === 'WARNING' ? '⚠️ WARNING' : '❌ FAIL');
            if ($res['status'] === 'FAIL' && $res['severity'] === 'CRITICAL') {
                $hasCriticalFailure = true;
            }
            $tableData[] = [
                $res['section'],
                $res['name'],
                $res['severity'],
                $statusText,
                $res['expected'],
                $res['actual'],
                $res['affected'],
                $res['action']
            ];
        }
        $this->table($headers, $tableData);

        // Section summary bullets
        $this->info("\nSummary Audits Status:\n");
        foreach ($report['section_scores'] as $sName => $sData) {
            $scorePercent = (int) round(($sData['score'] / $sData['weight']) * 100);
            $secIndicator = $scorePercent === 100 ? '✅' : ($scorePercent >= 70 ? '⚠️' : '❌');
            $this->line("  {$secIndicator} {$sName} (Score: {$sData['score']}/{$sData['weight']} - {$scorePercent}%)");
        }

        $healthStatus = $report['overall_status'];
        $healthScore = $report['health_score'];
        $healthIndicator = $healthStatus === 'HEALTHY' ? '🟢' : ($healthStatus === 'WARNING' ? '🟡' : '🔴');

        $this->info("\n==================================================================================");
        $this->line(" SYSTEM HEALTH SCORE: {$healthScore}%  |  Status: {$healthIndicator} {$healthStatus}");
        $this->info("==================================================================================");

        $reportDir = storage_path('app/integrity-reports');
        $jsonFile = $reportDir . "/integrity_report_" . date('Y_m_d_H_i_s') . ".json"; // dummy print target info

        if ($hasCriticalFailure) {
            $this->error("     SYSTEM INTEGRITY COMPROMISED: CRITICAL ERRORS DETECTED                       ");
            $this->comment("     Audit Reports saved to: " . str_replace('\\', '/', $reportDir));
            $this->info("==================================================================================");
            return 1;
        } else {
            $this->info("     SUCCESS: SYSTEM INTEGRITY AUDIT SECURE AND FULLY ALIGNED                     ");
            $this->comment("     Audit Reports saved to: " . str_replace('\\', '/', $reportDir));
            $this->info("==================================================================================");
            return 0;
        }
    }
}
