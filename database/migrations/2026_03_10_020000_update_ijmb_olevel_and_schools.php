<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add exam_centre to ijmb_olevel_results
        Schema::table('ijmb_olevel_results', function (Blueprint $table) {
            $table->string('exam_centre')->nullable()->after('exam_year');
        });

        // Update exam_type enum to include NABTEB, GCE
        DB::statement("ALTER TABLE ijmb_olevel_results MODIFY COLUMN exam_type VARCHAR(20) NOT NULL DEFAULT 'WAEC'");

        // Add from_year/to_year to ijmb_schools_attended (the form uses year strings, not dates)
        Schema::table('ijmb_schools_attended', function (Blueprint $table) {
            $table->string('from_year')->nullable()->after('school_name');
            $table->string('to_year')->nullable()->after('from_year');
            $table->string('qualification')->nullable()->after('to_year');
        });
    }

    public function down(): void
    {
        Schema::table('ijmb_olevel_results', function (Blueprint $table) {
            $table->dropColumn('exam_centre');
        });

        Schema::table('ijmb_schools_attended', function (Blueprint $table) {
            $table->dropColumn(['from_year', 'to_year', 'qualification']);
        });
    }
};
