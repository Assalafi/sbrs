<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change exam_date from date to string to allow free-text like "May/June 2023"
        DB::statement("ALTER TABLE remedial_applications MODIFY COLUMN exam_date VARCHAR(100) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE remedial_applications MODIFY COLUMN exam_date DATE NULL");
    }
};
