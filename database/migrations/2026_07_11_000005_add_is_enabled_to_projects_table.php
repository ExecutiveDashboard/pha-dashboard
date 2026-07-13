<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(false)->after('is_active');
        });

        // Ensure Project 1 (I-16/3 Islamabad) is enabled and default seeded projects exist
        \Illuminate\Support\Facades\DB::table('projects')->where('id', 1)->update(['is_enabled' => true]);

        // Insert new projects requested if they do not exist
        $existing = \Illuminate\Support\Facades\DB::table('projects')->pluck('name')->toArray();

        $newProjects = [
            [
                'name'               => 'I-12',
                'full_name'          => 'PHA Apartments I-12',
                'code'               => 'PHAF-I12',
                'city'               => 'Islamabad',
                'maintenance_rate'   => 3.00,
                'ww_amount'          => 10000,
                'ww_cutoff_date'     => '2026-01-01',
                'delay_percent'      => 10,
                'total_units'        => 0,
                'is_active'          => false,
                'is_enabled'         => false,
                'description'        => 'Housing Scheme in Sector I-12 Islamabad',
                'created_at'         => now(),
                'updated_at'         => now()
            ],
            [
                'name'               => 'Kurri',
                'full_name'          => 'PHA Officers Residencia Kurri',
                'code'               => 'PHAF-KURRI',
                'city'               => 'Islamabad',
                'maintenance_rate'   => 4.00,
                'ww_amount'          => 12000,
                'ww_cutoff_date'     => '2026-01-01',
                'delay_percent'      => 10,
                'total_units'        => 0,
                'is_active'          => false,
                'is_enabled'         => false,
                'description'        => 'Housing Scheme in Kurri Islamabad',
                'created_at'         => now(),
                'updated_at'         => now()
            ],
            [
                'name'               => 'PHA Residencia Peshawar',
                'full_name'          => 'PHA Residencia Peshawar Scheme',
                'code'               => 'PHAF-PESHAWAR',
                'city'               => 'Peshawar',
                'maintenance_rate'   => 3.50,
                'ww_amount'          => 8000,
                'ww_cutoff_date'     => '2026-01-01',
                'delay_percent'      => 10,
                'total_units'        => 0,
                'is_active'          => false,
                'is_enabled'         => false,
                'description'        => 'Housing Scheme in Peshawar',
                'created_at'         => now(),
                'updated_at'         => now()
            ]
        ];

        foreach ($newProjects as $proj) {
            // Check if name or code already exists in db
            $exists = \Illuminate\Support\Facades\DB::table('projects')
                ->where('name', $proj['name'])
                ->orWhere('code', $proj['code'])
                ->exists();

            if (!$exists) {
                \Illuminate\Support\Facades\DB::table('projects')->insert($proj);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
        });
    }
};
