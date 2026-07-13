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
            $table->string('occupancy_status', 50)->default('owner')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->dropColumn('occupancy_status');
        });
    }
};
