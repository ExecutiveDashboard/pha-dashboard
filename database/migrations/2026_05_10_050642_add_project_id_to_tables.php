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
            $table->foreignId('project_id')->default(1)->constrained()->onDelete('cascade');
        });
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('project_id')->default(1)->constrained()->onDelete('cascade');
        });
        Schema::table('notifications_log', function (Blueprint $table) {
            $table->foreignId('project_id')->default(1)->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
        Schema::table('notifications_log', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
