<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Link successful payments to their payment type.
        // Match by code + session first (preferring a type whose programme_type
        // matches the payer or is 'all'), then by code only.
        $payerModels = [
            'App\Models\Applicant' => 'applicants',
            'App\Models\Student' => 'students',
        ];

        foreach ($payerModels as $modelClass => $table) {
            // 1) code + session + programme_type match
            DB::statement("
                UPDATE payments p
                JOIN {$table} t ON t.id = p.payable_id AND p.payable_type = '{$modelClass}'
                JOIN payment_types pt ON pt.code = p.payment_type
                    AND pt.academic_session_id = p.academic_session_id
                    AND (pt.programme_type = t.programme_type OR pt.programme_type = 'all')
                SET p.payment_type_id = pt.id
                WHERE p.status = 'successful' AND p.payment_type_id IS NULL
            ");

            // 2) code + session only (any programme_type)
            DB::statement("
                UPDATE payments p
                JOIN payment_types pt ON pt.code = p.payment_type
                    AND pt.academic_session_id = p.academic_session_id
                SET p.payment_type_id = pt.id
                WHERE p.status = 'successful' AND p.payment_type_id IS NULL
            ");
        }

        // 3) code only (session-agnostic fallback)
        DB::statement("
            UPDATE payments p
            JOIN payment_types pt ON pt.code = p.payment_type
                AND pt.programme_type = 'all'
                AND pt.academic_session_id IS NULL
            SET p.payment_type_id = pt.id
            WHERE p.status = 'successful' AND p.payment_type_id IS NULL
        ");
    }

    public function down(): void
    {
        // Linking is a best-effort denormalization; nothing to revert.
    }
};
