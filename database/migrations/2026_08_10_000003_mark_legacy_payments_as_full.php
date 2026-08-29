<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_full_payment')->default(false)->after('installment_total');
            $table->timestamp('full_payment_at')->nullable()->after('is_full_payment');
        });

        // Mark every existing successful payment as a FULL payment (100%).
        // This is the smart conversion: payments made before the split-payment
        // feature are all considered full settlements of their fee.
        DB::table('payments')
            ->where('status', 'successful')
            ->whereNull('installment')
            ->update([
                'is_full_payment' => true,
                'full_payment_at' => DB::raw('COALESCE(verified_at, paid_at, created_at)'),
            ]);

        // Link legacy payments to their payment type (best effort), so the
        // admin/history views can show the type name. Try in order:
        // 1) code + programme_type + session, 2) code + programme_type, 3) code only.
        $payerModels = [
            'App\Models\Applicant' => 'applicants',
            'App\Models\Student' => 'students',
        ];

        foreach ($payerModels as $modelClass => $table) {
            DB::statement("
                UPDATE payments p
                JOIN {$table} t ON t.id = p.payable_id AND p.payable_type = '{$modelClass}'
                JOIN payment_types pt ON pt.code = p.payment_type
                    AND pt.programme_type = t.programme_type
                    AND pt.academic_session_id = p.academic_session_id
                SET p.payment_type_id = pt.id
                WHERE p.status = 'successful' AND p.payment_type_id IS NULL
            ");

            DB::statement("
                UPDATE payments p
                JOIN {$table} t ON t.id = p.payable_id AND p.payable_type = '{$modelClass}'
                JOIN payment_types pt ON pt.code = p.payment_type
                    AND pt.programme_type = t.programme_type
                    AND pt.academic_session_id IS NULL
                SET p.payment_type_id = pt.id
                WHERE p.status = 'successful' AND p.payment_type_id IS NULL
            ");
        }

        DB::statement("
            UPDATE payments p
            JOIN payment_types pt ON pt.code = p.payment_type AND pt.programme_type = 'all' AND pt.academic_session_id IS NULL
            SET p.payment_type_id = pt.id
            WHERE p.status = 'successful' AND p.payment_type_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['is_full_payment', 'full_payment_at']);
        });
    }
};
