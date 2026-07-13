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
        Schema::table('properties', function (Blueprint $table) {
            $table->index('project_id', 'idx_properties_project_id');
            $table->index(['project_id', 'category'], 'idx_properties_project_category');
        });

        Schema::table('allottees', function (Blueprint $table) {
            $table->index(['property_id', 'status'], 'idx_allottees_property_status');
        });

        Schema::table('tenant_records', function (Blueprint $table) {
            $table->index('property_id', 'idx_tenant_records_property_id');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->index('allottee_id', 'idx_bills_allottee_id');
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->index('bill_id', 'idx_payment_transactions_bill_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('idx_properties_project_id');
            $table->dropIndex('idx_properties_project_category');
        });

        Schema::table('allottees', function (Blueprint $table) {
            $table->dropIndex('idx_allottees_property_status');
        });

        Schema::table('tenant_records', function (Blueprint $table) {
            $table->dropIndex('idx_tenant_records_property_id');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('idx_bills_allottee_id');
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_payment_transactions_bill_id');
        });
    }
};
