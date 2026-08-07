<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            [
                'group' => 'remita',
                'key' => 'remita_hostel_service_type_id',
                'value' => '767540443',
                'type' => 'text',
                'description' => 'Service Type ID for Hostel Fee Payment',
            ],
            [
                'group' => 'remita',
                'key' => 'remita_hostel_description',
                'value' => 'HOSTEL-MAINTENANCE/FEES',
                'type' => 'text',
                'description' => 'Remita description for hostel fee payment',
            ],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('settings')->where('key', $row['key'])->exists();
            if (!$exists) {
                DB::table('settings')->insert(array_merge($row, [
                    'id' => (string) Str::uuid(),
                    'label' => ucwords(str_replace('_', ' ', $row['key'])),
                    'is_public' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        \Illuminate\Support\Facades\Cache::flush();
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['remita_hostel_service_type_id', 'remita_hostel_description'])->delete();
        \Illuminate\Support\Facades\Cache::flush();
    }
};
