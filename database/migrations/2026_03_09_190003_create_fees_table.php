<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_session_id');
            $table->enum('fee_type', ['application', 'admission', 'registration', 'examination']);
            $table->enum('programme_type', ['IJMB', 'Remedial', 'all']);
            $table->decimal('amount', 12, 2);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->cascadeOnDelete();
            $table->unique(['academic_session_id', 'fee_type', 'programme_type'], 'fees_session_type_programme_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
