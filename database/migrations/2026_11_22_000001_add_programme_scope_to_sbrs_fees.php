<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            if (!Schema::hasColumn('fees', 'programme_id')) {
                $table->uuid('programme_id')->nullable()->after('programme_type');
                $table->foreign('programme_id')->references('id')->on('programmes')->nullOnDelete();
                $table->index(['academic_session_id', 'fee_type', 'programme_type', 'programme_id'], 'fees_scope_lookup');
            }
        });

        Schema::table('payment_types', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_types', 'programme_id')) {
                $table->uuid('programme_id')->nullable()->after('programme_type');
                $table->foreign('programme_id')->references('id')->on('programmes')->nullOnDelete();
                $table->index(['academic_session_id', 'programme_type', 'programme_id'], 'payment_types_scope_lookup');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            if (Schema::hasColumn('payment_types', 'programme_id')) {
                $table->dropForeign(['programme_id']);
                $table->dropIndex('payment_types_scope_lookup');
                $table->dropColumn('programme_id');
            }
        });

        Schema::table('fees', function (Blueprint $table) {
            if (Schema::hasColumn('fees', 'programme_id')) {
                $table->dropForeign(['programme_id']);
                $table->dropIndex('fees_scope_lookup');
                $table->dropColumn('programme_id');
            }
        });
    }
};
