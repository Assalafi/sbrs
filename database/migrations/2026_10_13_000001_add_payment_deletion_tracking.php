<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('payments', 'deleted_by')) {
                $table->uuid('deleted_by')->nullable()->index();
            }
            if (!Schema::hasColumn('payments', 'deletion_reason')) {
                $table->text('deletion_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'deleted_by')) {
                $table->dropIndex(['deleted_by']);
                $table->dropColumn('deleted_by');
            }
            if (Schema::hasColumn('payments', 'deletion_reason')) {
                $table->dropColumn('deletion_reason');
            }
            if (Schema::hasColumn('payments', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
