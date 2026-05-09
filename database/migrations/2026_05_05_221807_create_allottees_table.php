<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allottees', function (Blueprint $table) {
            $table->id();
            $table->string('file_no')->nullable();
            $table->string('membership_no')->nullable();
            $table->string('fg')->nullable();
            $table->string('endorsed_files')->nullable();
            $table->string('loan_mortgage')->nullable();
            $table->string('handed_over')->nullable();
            $table->string('temporary_occupancy')->nullable();
            $table->date('possession_date')->nullable();
            $table->date('booking_transfer_date')->nullable();
            $table->string('gp')->nullable();
            $table->string('block_no')->nullable();
            $table->string('floor')->nullable();
            $table->string('flat_no')->nullable();
            $table->string('bps')->nullable();
            $table->string('cnic')->nullable();
            $table->string('balloting_fcfs')->nullable();
            $table->string('pal')->nullable();
            $table->string('transfer')->nullable();
            $table->string('verification')->nullable();
            $table->string('scanning')->nullable();
            $table->string('name')->nullable();
            $table->string('office_name')->nullable();
            $table->string('cadre_group')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->string('post_held')->nullable();
            $table->date('dos')->nullable();
            $table->date('dob')->nullable();
            $table->text('office_address')->nullable();
            $table->text('mailing_address')->nullable();
            $table->string('office_tel')->nullable();
            $table->string('home_tel')->nullable();
            $table->string('cell')->nullable();
            $table->string('category')->nullable(); // B or E
            $table->integer('covered_area')->nullable(); // sq ft
            $table->integer('due_months')->nullable()->default(0);
            $table->decimal('maintenance_charges', 12, 2)->nullable()->default(0);
            $table->decimal('watch_ward_charges', 12, 2)->nullable()->default(0);
            $table->decimal('fine', 12, 2)->nullable()->default(0);
            $table->decimal('total_maintenance_charges', 12, 2)->nullable()->default(0);
            // Derived city from mailing_address
            $table->string('city')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allottees');
    }
};
