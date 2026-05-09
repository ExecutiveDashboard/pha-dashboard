<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Insert bank/payment settings if not already there
        $settings = [
            [
                'key'   => 'bank_account_no',
                'value' => 'PHA-001-NBP-001',
                'label' => 'Bank Account Number (for bill)',
                'type'  => 'text',
                'group' => 'payment',
            ],
            [
                'key'   => 'bank_name',
                'value' => 'National Bank of Pakistan',
                'label' => 'Bank Name',
                'type'  => 'text',
                'group' => 'payment',
            ],
            [
                'key'   => 'bank_branch',
                'value' => 'Islamabad Main Branch',
                'label' => 'Bank Branch Name',
                'type'  => 'text',
                'group' => 'payment',
            ],
            [
                'key'   => 'project_name',
                'value' => 'I-16/3 Islamabad Apartments',
                'label' => 'Project / Sector Name',
                'type'  => 'text',
                'group' => 'general',
            ],
        ];

        foreach ($settings as $s) {
            Setting::firstOrCreate(
                ['key' => $s['key']],
                $s
            );
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', ['bank_account_no', 'bank_name', 'bank_branch', 'project_name'])->delete();
    }
};
