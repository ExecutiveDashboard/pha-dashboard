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
        Schema::create('property_ownership_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allottee_id')->constrained('allottees')->onDelete('cascade');
            $table->string('previous_owner_name');
            $table->string('previous_owner_cnic', 50);
            $table->string('previous_owner_cell', 50);
            $table->string('new_owner_name');
            $table->string('new_owner_cnic', 50);
            $table->string('new_owner_cell', 50);
            $table->string('transfer_type', 50); // transfer, sale, cancellation, reallotment
            $table->date('transfer_date');
            $table->date('effective_date');
            $table->string('transfer_ref_no', 100);
            $table->string('status', 50)->default('completed');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_ownership_history');
    }
};
