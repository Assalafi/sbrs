<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('indigene_cert')->nullable()->after('passport_photo');
            $table->string('primary_cert')->nullable()->after('indigene_cert');
            $table->string('ssce_cert')->nullable()->after('primary_cert');
            $table->string('birth_cert')->nullable()->after('ssce_cert');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['indigene_cert', 'primary_cert', 'ssce_cert', 'birth_cert']);
        });
    }
};
