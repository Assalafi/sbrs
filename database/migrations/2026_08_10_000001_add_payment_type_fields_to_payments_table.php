<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('payment_type_id')->nullable()->after('payment_type');
            $table->integer('installment')->nullable()->after('payment_type_id')->comment('1 = full/first, 2 = second, ...');
            $table->string('installment_label')->nullable()->after('installment');
            $table->integer('installment_total')->nullable()->after('installment_label')->comment('Total installments for split');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type_id', 'installment', 'installment_label', 'installment_total']);
        });
    }
};
