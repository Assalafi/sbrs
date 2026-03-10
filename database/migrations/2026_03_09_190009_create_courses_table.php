<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('programme_id');
            $table->uuid('subject_combination_id')->nullable();
            $table->string('course_code');
            $table->string('course_title');
            $table->integer('credit_units')->default(0);
            $table->enum('semester', ['first', 'second'])->default('first');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('programme_id')->references('id')->on('programmes')->cascadeOnDelete();
            $table->foreign('subject_combination_id')->references('id')->on('subject_combinations')->nullOnDelete();
        });

        Schema::create('course_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('course_id');
            $table->uuid('academic_session_id');
            $table->enum('semester', ['first', 'second']);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->cascadeOnDelete();
            $table->unique(['student_id', 'course_id', 'academic_session_id'], 'student_course_session_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_registrations');
        Schema::dropIfExists('courses');
    }
};
