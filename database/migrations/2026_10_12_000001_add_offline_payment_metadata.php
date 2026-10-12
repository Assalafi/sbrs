<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('sponsor', 40)->nullable()->after('amount')->index();
            $table->string('reference', 120)->nullable()->after('rrr')->index();
            $table->string('proof_path')->nullable()->after('reference');
            $table->text('notes')->nullable()->after('description');
            $table->uuid('recorded_by')->nullable()->after('verified_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['sponsor']);
            $table->dropIndex(['reference']);
            $table->dropIndex(['recorded_by']);
            $table->dropColumn(['sponsor', 'reference', 'proof_path', 'notes', 'recorded_by']);
        });
    }
};
