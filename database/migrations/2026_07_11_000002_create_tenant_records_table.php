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
        Schema::create('tenant_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allottee_id')->constrained('allottees')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->unsignedBigInteger('property_id')->nullable();
            $table->string('tenant_name');
            $table->string('tenant_cnic', 50);
            $table->string('spouse_name')->nullable();
            $table->string('mobile_no', 50);
            $table->string('alternate_contact_no', 50)->nullable();
            $table->string('tenant_email')->nullable();
            $table->text('permanent_address')->nullable();
            $table->text('current_address')->nullable();
            $table->string('agreement_no', 100);
            $table->date('agreement_date')->nullable();
            $table->date('agreement_start_date');
            $table->date('agreement_expiry_date');
            $table->string('duration_of_stay', 50)->nullable();
            $table->decimal('monthly_rent', 12, 2)->nullable();
            $table->decimal('security_deposit', 12, 2)->nullable();
            $table->date('occupancy_date');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_records');
    }
};
