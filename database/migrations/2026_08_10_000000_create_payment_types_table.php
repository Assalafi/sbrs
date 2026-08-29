<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('programme_type', ['IJMB', 'Remedial', 'all'])->default('all');
            $table->uuid('academic_session_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->boolean('split_enabled')->default(false);
            $table->decimal('split_percent', 5, 2)->nullable()->comment('First installment percentage, e.g. 50');
            $table->integer('installment_count')->default(1)->comment('Total installments when split, e.g. 2');
            $table->boolean('is_required')->default(false)->comment('MUST payment shown on dashboard popup');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('remita_service_type_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
