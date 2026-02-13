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
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_id', 20)->nullable();
            $table->string('position_title')->nullable();
            $table->string('appointment_status')->nullable();
            $table->string('sex')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();
            $table->string('highest_degree')->nullable();
            $table->string('degree_course')->nullable();
            $table->string('degree_major')->nullable();
            $table->string('school_graduated')->nullable();
            $table->year('year_graduated')->nullable();
            $table->string('prc_license_number', 20)->nullable();
            $table->date('prc_validity')->nullable();
            $table->string('eligibility')->nullable();
            $table->string('specialization')->nullable();
            $table->date('date_hired')->nullable();
            $table->unsignedInteger('teaching_hours_per_week')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
