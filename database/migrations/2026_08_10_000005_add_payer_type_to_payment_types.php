<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->enum('payer_type', ['both', 'applicant', 'student'])->default('both')->after('academic_session_id');
        });

        // Application + admission are paid by applicants; registration/exam by students.
        DB::table('payment_types')
            ->whereIn('code', ['application', 'admission'])
            ->update(['payer_type' => 'applicant']);
        DB::table('payment_types')
            ->whereIn('code', ['registration', 'examination'])
            ->update(['payer_type' => 'student']);
    }

    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropColumn('payer_type');
        });
    }
};
