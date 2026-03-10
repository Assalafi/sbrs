<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE remedial_exam_results MODIFY COLUMN exam_category VARCHAR(50) NULL DEFAULT 'GCE O-Level'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE remedial_exam_results MODIFY COLUMN exam_category ENUM('GCE O-Level','WAEC/NECO/MOCK','Teachers Certificate Grade II') NOT NULL");
    }
};
