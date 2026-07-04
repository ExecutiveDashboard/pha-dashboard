<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key'   => 'current_billing_month',
                'value' => '2026-07',
                'label' => 'Current Billing Month',
                'type'  => 'text',
                'group' => 'billing_cycle',
            ],
            [
                'key'   => 'allow_future_billing',
                'value' => '0',
                'label' => 'Allow Future Bill Generation',
                'type'  => 'boolean',
                'group' => 'billing_cycle',
            ],
            [
                'key'   => 'max_billing_months_ahead',
                'value' => '1',
                'label' => 'Maximum Months Ahead',
                'type'  => 'number',
                'group' => 'billing_cycle',
            ],
            [
                'key'   => 'billing_month_lock',
                'value' => '0',
                'label' => 'Lock Billing Month',
                'type'  => 'boolean',
                'group' => 'billing_cycle',
            ],
            [
                'key'   => 'billing_admin_override',
                'value' => '0',
                'label' => 'Admin Override Active',
                'type'  => 'boolean',
                'group' => 'billing_cycle',
            ],
        ];

        foreach ($settings as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'current_billing_month',
            'allow_future_billing',
            'max_billing_months_ahead',
            'billing_month_lock',
            'billing_admin_override'
        ])->delete();
    }
};
