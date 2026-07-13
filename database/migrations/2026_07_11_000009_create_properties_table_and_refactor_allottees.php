<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create properties table
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('block_no');
            $table->string('floor')->nullable();
            $table->string('flat_no');
            $table->string('category')->nullable();
            $table->string('type')->default('apartment'); // apartment, plot, house
            $table->integer('covered_area')->nullable()->default(0);
            $table->integer('open_area')->nullable()->default(0);
            $table->string('plot_size')->nullable();
            $table->decimal('maintenance_rate', 8, 2)->default(3.07);
            $table->decimal('ww_amount', 10, 2)->default(10000.00);
            $table->string('status', 50)->default('Allotted'); // Available, Allotted, Cancelled, Under Maintenance
            $table->boolean('has_parking')->default(false);
            $table->boolean('has_water')->default(false);
            $table->decimal('parking_charges', 10, 2)->default(0.00);
            $table->decimal('water_charges', 10, 2)->default(0.00);
            $table->timestamps();

            // Unique property unit identifier
            $table->unique(['project_id', 'block_no', 'flat_no']);
        });

        // 2. Populate properties from existing allottees
        $existingAllottees = DB::table('allottees')->get();
        $insertedProperties = [];

        foreach ($existingAllottees as $allottee) {
            $key = $allottee->project_id . '-' . $allottee->block_no . '-' . $allottee->flat_no;
            
            // Skip duplicates to treat the flat as a single permanent entity
            if (isset($insertedProperties[$key])) {
                continue;
            }

            // Fetch default rates from project settings if available
            $project = DB::table('projects')->where('id', $allottee->project_id)->first();
            $rate = $project ? $project->maintenance_rate : 3.07;
            $ww = $project ? $project->ww_amount : 10000.00;

            $propertyId = DB::table('properties')->insertGetId([
                'project_id'       => $allottee->project_id,
                'block_no'         => $allottee->block_no ?? 'Unknown',
                'floor'            => $allottee->floor,
                'flat_no'          => $allottee->flat_no ?? 'Unknown',
                'category'         => $allottee->category,
                'type'             => 'apartment',
                'covered_area'     => $allottee->covered_area ?? 0,
                'open_area'        => 0,
                'plot_size'        => null,
                'maintenance_rate' => $rate,
                'ww_amount'        => $ww,
                'status'           => 'Allotted',
                'has_parking'      => $allottee->has_parking ?? false,
                'has_water'        => $allottee->has_water ?? false,
                'parking_charges'  => $allottee->parking_charges ?? 0.00,
                'water_charges'    => $allottee->water_charges ?? 0.00,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $insertedProperties[$key] = $propertyId;
        }

        // 3. Add ownership tracking columns to allottees table
        Schema::table('allottees', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->constrained('properties')->onDelete('set null');
            $table->date('ownership_start_date')->nullable();
            $table->date('ownership_end_date')->nullable();
            $table->string('transfer_type', 50)->nullable();
            $table->string('transfer_ref_no', 100)->nullable();
            $table->string('status', 50)->default('active'); // active, inactive
            $table->text('remarks')->nullable();
        });

        // 4. Update allottees' property_id link and ownership dates
        foreach ($existingAllottees as $allottee) {
            $key = $allottee->project_id . '-' . $allottee->block_no . '-' . $allottee->flat_no;
            $propertyId = $insertedProperties[$key] ?? null;

            DB::table('allottees')->where('id', $allottee->id)->update([
                'property_id'          => $propertyId,
                'ownership_start_date' => $allottee->possession_date ?? $allottee->created_at,
                'status'               => 'active',
            ]);
        }

        // 5. Populate property_id for existing tenant records
        $existingTenants = DB::table('tenant_records')->get();
        foreach ($existingTenants as $tenant) {
            $allottee = DB::table('allottees')->where('id', $tenant->allottee_id)->first();
            if ($allottee) {
                DB::table('tenant_records')->where('id', $tenant->id)->update([
                    'property_id' => $allottee->property_id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn([
                'property_id', 'ownership_start_date', 'ownership_end_date',
                'transfer_type', 'transfer_ref_no', 'status', 'remarks'
            ]);
        });

        Schema::dropIfExists('properties');
    }
};
