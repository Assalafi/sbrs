<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            // The original unique key did not include programme_id, so it
            // prevented separate Science and Arts rules for one fee/session.
            $table->dropUnique('fees_session_type_programme_unique');
            $table->unique(
                ['academic_session_id', 'fee_type', 'programme_type', 'programme_id'],
                'fees_session_type_programme_scope_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropUnique('fees_session_type_programme_scope_unique');
            $table->unique(
                ['academic_session_id', 'fee_type', 'programme_type'],
                'fees_session_type_programme_unique'
            );
        });
    }
};
