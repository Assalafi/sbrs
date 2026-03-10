<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('payable'); // applicant or student
            $table->enum('payment_type', ['application', 'admission', 'registration', 'examination']);
            $table->uuid('academic_session_id');
            $table->uuid('fee_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('NGN');
            $table->string('rrr')->nullable()->index();
            $table->string('order_id')->nullable()->index();
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'processing', 'successful', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->cascadeOnDelete();
            $table->foreign('fee_id')->references('id')->on('fees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
