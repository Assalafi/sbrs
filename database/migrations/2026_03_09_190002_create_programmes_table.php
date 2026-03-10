<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['IJMB', 'Remedial']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subject_combinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('programme_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('subjects');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('programme_id')->references('id')->on('programmes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_combinations');
        Schema::dropIfExists('programmes');
    }
};
