<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add from_year, to_year, qualification to remedial_institutions
        Schema::table('remedial_institutions', function (Blueprint $table) {
            $table->string('from_year')->nullable()->after('institution_name');
            $table->string('to_year')->nullable()->after('from_year');
            $table->string('qualification')->nullable()->after('to_year');
        });

        // Add from_date, to_date aliases to remedial_employment_records
        Schema::table('remedial_employment_records', function (Blueprint $table) {
            $table->string('from_date')->nullable()->after('post');
            $table->string('to_date')->nullable()->after('from_date');
        });
    }

    public function down(): void
    {
        Schema::table('remedial_institutions', function (Blueprint $table) {
            $table->dropColumn(['from_year', 'to_year', 'qualification']);
        });

        Schema::table('remedial_employment_records', function (Blueprint $table) {
            $table->dropColumn(['from_date', 'to_date']);
        });
    }
};
