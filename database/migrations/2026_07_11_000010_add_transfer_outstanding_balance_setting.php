<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'transfer_outstanding_balance'],
            [
                'value' => '1',
                'label' => 'Transfer Outstanding Balance on Property Transfer',
                'type'  => 'boolean',
                'group' => 'billing',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::where('key', 'transfer_outstanding_balance')->delete();
    }
};
