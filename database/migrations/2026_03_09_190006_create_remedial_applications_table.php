<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remedial_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('applicant_id');
            $table->uuid('academic_session_id');

            // Personal Details
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable();
            $table->string('state_of_origin')->nullable();
            $table->string('lga')->nullable();
            $table->text('correspondence_address')->nullable();

            // Parent/Guardian
            $table->string('guardian_name')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_email')->nullable();

            // Permanent Contact
            $table->text('permanent_address')->nullable();
            $table->string('permanent_phone')->nullable();
            $table->string('permanent_email')->nullable();

            // Primary Education
            $table->string('primary_school_name')->nullable();
            $table->string('primary_school_from')->nullable();
            $table->string('primary_school_to')->nullable();

            // Examination Info
            $table->date('exam_date')->nullable();
            $table->string('exam_centre')->nullable();
            $table->string('exam_number')->nullable();

            // Sponsorship
            $table->enum('sponsor_type', ['State Government', 'Non-Governmental Organization', 'Any Other Government', 'Individual', 'Self'])->nullable();
            $table->string('sponsor_name')->nullable();
            $table->text('sponsor_address')->nullable();

            // Extracurricular
            $table->text('games')->nullable();
            $table->text('hobbies')->nullable();
            $table->text('other_activities')->nullable();
            $table->text('positions_held')->nullable();

            // Declaration
            $table->boolean('declaration_confirmed')->default(false);
            $table->string('declaration_name')->nullable();
            $table->date('declaration_date')->nullable();

            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->cascadeOnDelete();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->cascadeOnDelete();
        });

        // Post Primary Institutions
        Schema::create('remedial_institutions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('remedial_application_id');
            $table->string('institution_name');
            $table->string('date_from')->nullable();
            $table->string('date_to')->nullable();
            $table->timestamps();

            $table->foreign('remedial_application_id')->references('id')->on('remedial_applications')->cascadeOnDelete();
        });

        // Examination Results (GCE, WAEC/NECO, Teachers Cert)
        Schema::create('remedial_exam_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('remedial_application_id');
            $table->enum('exam_category', ['GCE O-Level', 'WAEC/NECO/MOCK', 'Teachers Certificate Grade II']);
            $table->string('subject');
            $table->string('grade');
            $table->timestamps();

            $table->foreign('remedial_application_id')->references('id')->on('remedial_applications')->cascadeOnDelete();
        });

        // Employment Records
        Schema::create('remedial_employment_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('remedial_application_id');
            $table->string('employer');
            $table->string('post');
            $table->string('date_from')->nullable();
            $table->string('date_to')->nullable();
            $table->timestamps();

            $table->foreign('remedial_application_id')->references('id')->on('remedial_applications')->cascadeOnDelete();
        });

        // Referees
        Schema::create('remedial_referees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('remedial_application_id');
            $table->string('name');
            $table->text('address');
            $table->timestamps();

            $table->foreign('remedial_application_id')->references('id')->on('remedial_applications')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remedial_referees');
        Schema::dropIfExists('remedial_employment_records');
        Schema::dropIfExists('remedial_exam_results');
        Schema::dropIfExists('remedial_institutions');
        Schema::dropIfExists('remedial_applications');
    }
};
