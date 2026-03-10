<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ijmb_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('applicant_id');
            $table->uuid('academic_session_id');

            // Personal Details
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable();
            $table->string('nationality')->default('Nigerian');
            $table->string('state_of_origin')->nullable();
            $table->string('lga')->nullable();
            $table->string('permanent_address')->nullable();
            $table->text('extracurricular_activities')->nullable();
            $table->text('disability_or_sickness')->nullable();

            // Next of Kin
            $table->string('nok_name')->nullable();
            $table->string('nok_phone')->nullable();
            $table->string('nok_relationship')->nullable();
            $table->text('nok_address')->nullable();

            // Sponsorship
            $table->enum('sponsor_type', ['State Government', 'Non-Governmental Organization', 'Individual', 'Self'])->nullable();
            $table->string('sponsor_name')->nullable();
            $table->text('sponsor_address')->nullable();

            // Declaration
            $table->boolean('declaration_confirmed')->default(false);
            $table->string('declaration_name')->nullable();
            $table->date('declaration_date')->nullable();

            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->cascadeOnDelete();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->cascadeOnDelete();
        });

        // Schools Attended (multiple entries)
        Schema::create('ijmb_schools_attended', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ijmb_application_id');
            $table->string('school_name');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->timestamps();

            $table->foreign('ijmb_application_id')->references('id')->on('ijmb_applications')->cascadeOnDelete();
        });

        // O'Level Results
        Schema::create('ijmb_olevel_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ijmb_application_id');
            $table->enum('exam_type', ['WAEC', 'NECO', 'Others']);
            $table->string('examination_type_other')->nullable();
            $table->string('exam_number');
            $table->string('exam_year');
            $table->timestamps();

            $table->foreign('ijmb_application_id')->references('id')->on('ijmb_applications')->cascadeOnDelete();
        });

        // O'Level Result Subjects
        Schema::create('ijmb_olevel_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ijmb_olevel_result_id');
            $table->string('subject');
            $table->string('grade');
            $table->timestamps();

            $table->foreign('ijmb_olevel_result_id')->references('id')->on('ijmb_olevel_results')->cascadeOnDelete();
        });

        // Referees
        Schema::create('ijmb_referees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ijmb_application_id');
            $table->string('name');
            $table->text('address');
            $table->timestamps();

            $table->foreign('ijmb_application_id')->references('id')->on('ijmb_applications')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ijmb_referees');
        Schema::dropIfExists('ijmb_olevel_subjects');
        Schema::dropIfExists('ijmb_olevel_results');
        Schema::dropIfExists('ijmb_schools_attended');
        Schema::dropIfExists('ijmb_applications');
    }
};
