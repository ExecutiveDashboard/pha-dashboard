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
            $table->decimal('parking_charges', 10, 2)->default(0)->after('fine');
            $table->decimal('water_charges', 10, 2)->default(0)->after('parking_charges');
            $table->boolean('has_parking')->default(false)->after('water_charges');
            $table->boolean('has_water')->default(false)->after('has_parking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->dropColumn(['parking_charges', 'water_charges', 'has_parking', 'has_water']);
        });
    }
};
