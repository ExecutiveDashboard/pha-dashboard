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
        Schema::table('allottees', function (Blueprint $table) {
            $table->index(['project_id', 'status'], 'idx_allottees_project_status');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->index(['project_id', 'status'], 'idx_complaints_project_status');
            $table->index('assigned_staff_id', 'idx_complaints_assigned_staff_id');
            $table->index('allottee_id', 'idx_complaints_allottee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->dropIndex('idx_allottees_project_status');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex('idx_complaints_project_status');
            $table->dropIndex('idx_complaints_assigned_staff_id');
            $table->dropIndex('idx_complaints_allottee_id');
        });
    }
};
