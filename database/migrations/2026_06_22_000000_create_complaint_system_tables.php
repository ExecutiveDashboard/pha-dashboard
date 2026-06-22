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
        // 1. Complaint Categories
        Schema::create('complaint_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Maintenance Staff
        Schema::create('maintenance_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('designation'); // Electrician, Plumber, Supervisor, etc.
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_number')->unique();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('allottee_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('complaint_categories')->onDelete('cascade');
            $table->string('subject');
            $table->text('description');
            $table->string('priority')->default('medium'); // low, medium, high, emergency
            $table->string('status')->default('new'); // new, under_review, assigned, in_progress, waiting_for_material, pending_external_vendor, resolved, closed, rejected, reopened
            $table->foreignId('assigned_staff_id')->nullable()->constrained('maintenance_staff')->onDelete('set null');
            $table->boolean('satisfaction_confirmed')->default(false);
            $table->text('feedback_remarks')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // 4. Complaint Attachments
        Schema::create('complaint_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // if uploaded by admin/staff
            $table->unsignedBigInteger('allottee_id')->nullable(); // if uploaded by allottee
            $table->string('file_path');
            $table->string('file_type')->nullable(); // e.g. image, pdf, doc
            $table->string('upload_type')->default('initial'); // initial, completion
            $table->timestamps();

            $table->foreign('allottee_id')->references('id')->on('allottees')->onDelete('set null');
        });

        // 5. Complaint Logs (Timeline / Audit Trail)
        Schema::create('complaint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // if action by admin/staff
            $table->unsignedBigInteger('allottee_id')->nullable(); // if action by allottee
            $table->string('action'); // created, assigned, status_changed, remarked, resolved, closed, reopened, feedback_submitted
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('allottee_id')->references('id')->on('allottees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_logs');
        Schema::dropIfExists('complaint_attachments');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('maintenance_staff');
        Schema::dropIfExists('complaint_categories');
    }
};
