<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allottee_id')->constrained('allottees')->onDelete('cascade');
            $table->string('bill_month', 7); // YYYY-MM e.g. 2025-05
            $table->string('psid', 30)->nullable()->unique(); // PHAF-A001-202505
            $table->decimal('maintenance_amount', 12, 2)->default(0);
            $table->decimal('ww_amount', 12, 2)->default(0);        // 0 if W&W already charged before
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            // status: unpaid | paid | partial | settled | locked
            $table->string('status', 20)->default('unpaid');
            $table->string('payment_mode', 30)->nullable();  // cash | online | cheque | psid | waived
            $table->date('payment_date')->nullable();
            $table->string('payment_ref', 100)->nullable();
            $table->string('settled_by', 100)->nullable();   // admin name who settled
            $table->text('settled_note')->nullable();        // reason for manual settlement
            $table->boolean('is_locked')->default(false);    // locked after payment confirmed
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['allottee_id', 'bill_month']); // one bill per allottee per month
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
