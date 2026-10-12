<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'student_dashboard_notice')->update([
            'group' => 'announcements',
            'label' => 'Student dashboard announcement',
            'description' => 'Rich-text announcement shown in the notice popup on the student dashboard. Leave empty to hide the popup.',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'student_dashboard_notice')->update([
            'group' => 'student',
            'label' => 'Student dashboard notice',
            'updated_at' => now(),
        ]);
    }
};
