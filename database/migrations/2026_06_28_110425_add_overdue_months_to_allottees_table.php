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
            $table->integer('overdue_months')->nullable()->default(0)->after('due_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->dropColumn('overdue_months');
        });
    }
};
