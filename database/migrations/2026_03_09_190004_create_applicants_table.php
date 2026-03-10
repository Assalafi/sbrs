<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('application_number')->unique();
            $table->string('surname');
            $table->string('first_name');
            $table->string('other_names')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('password');
            $table->enum('programme_type', ['IJMB', 'Remedial']);
            $table->uuid('programme_id')->nullable();
            $table->uuid('subject_combination_id')->nullable();
            $table->uuid('academic_session_id')->nullable();
            $table->string('passport_photo')->nullable();
            $table->enum('status', ['registered', 'payment_pending', 'form_filling', 'submitted', 'under_review', 'approved', 'rejected', 'admitted'])->default('registered');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('programme_id')->references('id')->on('programmes')->nullOnDelete();
            $table->foreign('subject_combination_id')->references('id')->on('subject_combinations')->nullOnDelete();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
