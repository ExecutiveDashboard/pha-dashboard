<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('total_maintenance_charges');
            $table->string('payment_mode', 30)->nullable()->after('amount_paid'); // cash / online / cheque
            $table->date('payment_date')->nullable()->after('payment_mode');
            $table->string('payment_ref', 100)->nullable()->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('allottees', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'payment_mode', 'payment_date', 'payment_ref']);
        });
    }
};
