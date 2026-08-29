<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Seed payment types from the existing fees table so that legacy flows
        // (application, admission, registration, examination) are represented.
        // Any successful payment made before now is automatically treated as 100%
        // satisfied (installment IS NULL = full payment).
        $fees = DB::table('fees')->orderBy('academic_session_id')->orderBy('fee_type')->get();

        $requiredMap = [
            'application' => true,
            'admission' => true,
            'registration' => true,
            'examination' => true,
        ];

        // Registration is configured with a 50% split out of the box.
        $splitMap = [
            'registration' => ['enabled' => true, 'percent' => 50, 'count' => 2],
        ];

        foreach ($fees as $fee) {
            $code = $fee->fee_type;
            $exists = DB::table('payment_types')->where('code', $code)
                ->where('programme_type', $fee->programme_type)
                ->where('academic_session_id', $fee->academic_session_id)
                ->exists();
            if ($exists) {
                continue;
            }

            $split = $splitMap[$code] ?? ['enabled' => false, 'percent' => null, 'count' => 1];

            $serviceTypeKey = 'remita_' . strtolower($fee->programme_type === 'all' ? '' : $fee->programme_type) . '_' . $code . '_service_type_id';
            $remitaServiceTypeId = DB::table('settings')->where('key', $serviceTypeKey)->value('value');

            DB::table('payment_types')->insert([
                'id' => (string) Str::uuid(),
                'code' => $code,
                'name' => ucwords(str_replace('_', ' ', $code)) . ' Fee',
                'description' => $fee->description,
                'programme_type' => $fee->programme_type,
                'academic_session_id' => $fee->academic_session_id,
                'amount' => $fee->amount,
                'split_enabled' => $split['enabled'],
                'split_percent' => $split['percent'],
                'installment_count' => $split['count'],
                'is_required' => $requiredMap[$code] ?? false,
                'is_active' => $fee->is_active ? true : false,
                'sort_order' => ['application' => 1, 'admission' => 2, 'registration' => 3, 'examination' => 4][$code] ?? 10,
                'remita_service_type_id' => $remitaServiceTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('payment_types')->whereIn('code', ['application', 'admission', 'registration', 'examination'])->delete();
    }
};
