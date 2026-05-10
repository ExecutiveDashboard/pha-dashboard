<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            // W&W one-time charge tracking
            $table->boolean('ww_charged')->default(false)->after('city');
            $table->date('ww_charged_date')->nullable()->after('ww_charged');
        });
    }

    public function down(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->dropColumn(['ww_charged', 'ww_charged_date']);
        });
    }
};
