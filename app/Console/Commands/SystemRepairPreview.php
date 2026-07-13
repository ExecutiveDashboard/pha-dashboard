<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SystemIntegrityService;

class SystemRepairPreview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:repair-preview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a detailed diagnostic report on auto-repairable items versus items requiring manual intervention';

    /**
     * Execute the console command.
     */
    public function handle(SystemIntegrityService $service)
    {
        $this->info("==================================================================================");
        $this->info("                    PHA MIS SYSTEM REPAIR PREVIEW REPORT                          ");
        $this->info("==================================================================================");

        $preview = $service->getRepairPreview();
        $headers = ['Integrity Check Name', 'Affected Count', 'Action Classification', 'Suggested Resolution / Impact'];
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
}
