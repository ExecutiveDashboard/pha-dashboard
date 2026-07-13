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
        // 1. Add fields to allottees table
        Schema::table('allottees', function (Blueprint $table) {
            $table->string('father_spouse_name')->nullable()->after('name');
            $table->string('email')->nullable()->after('cnic');
        });

        // 2. Add fields to property_ownership_history table
        Schema::table('property_ownership_history', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->nullable()->after('allottee_id');
            $table->unsignedBigInteger('previous_owner_id')->nullable()->after('property_id');
            $table->unsignedBigInteger('new_owner_id')->nullable()->after('previous_owner_id');
            $table->date('transfer_approval_date')->nullable()->after('transfer_ref_no');
            $table->date('possession_handover_date')->nullable()->after('transfer_approval_date');
            $table->decimal('outstanding_balance_at_transfer', 12, 2)->default(0.00)->after('possession_handover_date');
            $table->string('balance_transfer_status', 50)->nullable()->after('outstanding_balance_at_transfer');
            $table->unsignedBigInteger('created_by')->nullable()->after('balance_transfer_status');
        });

        // 3. Clean up duplicate active allottees for the same property in restored database
        $properties = \Illuminate\Support\Facades\DB::table('properties')->get();
        $conflictsLogged = [];

        foreach ($properties as $property) {
            $allottees = \Illuminate\Support\Facades\DB::table('allottees')
                ->where('property_id', $property->id)
                ->orderBy('ownership_start_date', 'desc')
                ->orderBy('id', 'desc')
                ->get();
            
            if ($allottees->count() > 1) {
                $first = true;
                $propertyConflicts = [
                    'property_id' => $property->id,
                    'block_no' => $property->block_no,
                    'flat_no' => $property->flat_no,
                    'active_owner' => null,
                    'deactivated_owners' => [],
                ];

                foreach ($allottees as $allottee) {
                    if ($first) {
                        \Illuminate\Support\Facades\DB::table('allottees')
                            ->where('id', $allottee->id)
                            ->update(['status' => 'active']);
                        $propertyConflicts['active_owner'] = [
                            'id' => $allottee->id,
                            'name' => $allottee->name,
                            'cnic' => $allottee->cnic,
                        ];
                        $first = false;
                    } else {
                        // Mark older allottees for the same property as inactive
                        \Illuminate\Support\Facades\DB::table('allottees')
                            ->where('id', $allottee->id)
                            ->update([
                                'status' => 'inactive',
                                'ownership_end_date' => $allottee->ownership_end_date ?? $allottees->first()->ownership_start_date
                            ]);
                        $propertyConflicts['deactivated_owners'][] = [
                            'id' => $allottee->id,
                            'name' => $allottee->name,
                            'cnic' => $allottee->cnic,
                        ];
                    }
                }
                $conflictsLogged[] = $propertyConflicts;
            }
        }

        if (!empty($conflictsLogged)) {
            file_put_contents('C:\Users\nadee\.gemini\antigravity-ide\brain\38b48ec6-1f7c-4ed1-82dc-d91b5d51ff8d\deactivated_conflicts.json', json_encode($conflictsLogged, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_ownership_history', function (Blueprint $table) {
            $table->dropColumn([
                'property_id', 'previous_owner_id', 'new_owner_id',
                'transfer_approval_date', 'possession_handover_date',
                'outstanding_balance_at_transfer', 'balance_transfer_status', 'created_by'
            ]);
        });

        Schema::table('allottees', function (Blueprint $table) {
            $table->dropColumn(['father_spouse_name', 'email']);
        });
    }
};
