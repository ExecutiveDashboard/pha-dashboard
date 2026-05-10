<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Project;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Short: I-16/3
            $table->string('full_name');                     // Full: I-16/3 Islamabad Apartments
            $table->string('code', 20);                      // PHAF-I163
            $table->string('city')->default('Islamabad');
            $table->decimal('maintenance_rate', 8, 2)->default(3.07); // Rs per sq ft
            $table->decimal('ww_amount', 10, 2)->default(10000);
            $table->date('ww_cutoff_date')->default('2023-07-23');
            $table->decimal('delay_percent', 5, 2)->default(10);
            $table->string('bank_account_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->integer('total_units')->default(0);
            $table->boolean('is_active')->default(false);    // only 1 active at a time
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed demo projects
        $projects = [
            [
                'name'               => 'I-16/3 Islamabad',
                'full_name'          => 'I-16/3 Islamabad Apartments',
                'code'               => 'PHAF-I163',
                'city'               => 'Islamabad',
                'maintenance_rate'   => 3.07,
                'ww_amount'          => 10000,
                'ww_cutoff_date'     => '2023-07-23',
                'delay_percent'      => 10,
                'bank_account_no'    => 'PHA-001-NBP-001',
                'bank_name'          => 'National Bank of Pakistan',
                'bank_branch'        => 'Islamabad Main Branch',
                'total_units'        => 1584,
                'is_active'          => true,
                'description'        => 'Active project — 1,584 allottees, Cat-B & Cat-E apartments',
            ],
            [
                'name'               => 'I-10/1 Islamabad',
                'full_name'          => 'I-10/1 Islamabad Flats',
                'code'               => 'PHAF-I101',
                'city'               => 'Islamabad',
                'maintenance_rate'   => 3.50,
                'ww_amount'          => 12000,
                'ww_cutoff_date'     => '2022-01-01',
                'delay_percent'      => 10,
                'bank_account_no'    => 'PHA-002-NBP-002',
                'bank_name'          => 'National Bank of Pakistan',
                'bank_branch'        => 'I-10 Branch',
                'total_units'        => 960,
                'is_active'          => false,
                'description'        => 'Demo project — configuration pending',
            ],
            [
                'name'               => 'Lahore Housing',
                'full_name'          => 'PHAF Lahore Housing Scheme',
                'code'               => 'PHAF-LHR',
                'city'               => 'Lahore',
                'maintenance_rate'   => 2.80,
                'ww_amount'          => 8000,
                'ww_cutoff_date'     => '2024-01-01',
                'delay_percent'      => 10,
                'bank_account_no'    => 'PHA-003-HBL-001',
                'bank_name'          => 'Habib Bank Limited',
                'bank_branch'        => 'Lahore Main Branch',
                'total_units'        => 720,
                'is_active'          => false,
                'description'        => 'Demo project — Lahore sector',
            ],
        ];

        foreach ($projects as $p) {
            Project::create($p);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
