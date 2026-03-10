<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('applicant_id');
            $table->string('registration_number')->unique();
            $table->uuid('academic_session_id');
            $table->uuid('programme_id');
            $table->uuid('subject_combination_id')->nullable();
            $table->enum('programme_type', ['IJMB', 'Remedial']);
            $table->string('password');

            // Personal
            $table->string('surname');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('passport_photo')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable();
            $table->string('nationality')->default('Nigerian');
            $table->string('state_of_origin')->nullable();
            $table->string('lga')->nullable();
            $table->text('home_address')->nullable();

            // Parent/Guardian
            $table->string('guardian_name')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_phone')->nullable();

            // Sponsor
            $table->string('sponsor_name')->nullable();
            $table->string('sponsor_relationship')->nullable();
            $table->text('sponsor_address')->nullable();

            // IJMB specific
            $table->string('group')->nullable();

            // Remedial specific
            $table->string('hall')->nullable();
            $table->string('room_number')->nullable();

            // Health
            $table->enum('health_status', ['Normal', 'Disabled'])->default('Normal');
            $table->string('disability_type')->nullable();
            $table->string('medication_type')->nullable();

            // Hobbies
            $table->text('hobbies')->nullable();

            // Screening
            $table->enum('screening_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('screening_remarks')->nullable();
            $table->uuid('screened_by')->nullable();
            $table->timestamp('screened_at')->nullable();

            // Registration
            $table->boolean('is_registered')->default(false);
            $table->timestamp('registered_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->cascadeOnDelete();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->cascadeOnDelete();
            $table->foreign('programme_id')->references('id')->on('programmes')->cascadeOnDelete();
            $table->foreign('subject_combination_id')->references('id')->on('subject_combinations')->nullOnDelete();
            $table->foreign('screened_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
