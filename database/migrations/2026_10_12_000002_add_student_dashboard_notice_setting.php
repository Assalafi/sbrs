<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'student_dashboard_notice'],
            [
                'id' => (string) Str::uuid(),
                'group' => 'student',
                'value' => '<h3>All Newly Admitted IJMB Students (2026/2027 Academic Session)</h3><p>This is to inform all newly admitted students into the Interim Joint Matriculation Board (IJMB) Programme for the 2026/2027 Academic Session that they are required to report to the School of Basic and Remedial Studies on <strong>Monday, 29th June, 2026, at 10:00 a.m. prompt</strong> for the commencement of registration and lectures.</p><p>All admitted students are advised to come along with their admission documents and other relevant credentials necessary for registration.</p><p>Attendance is mandatory as important information regarding the programme will be communicated during the session.</p><p>Please be punctual and comply accordingly.</p><hr><p style="text-align:right"><em>Signed<br>Management<br>School of Basic and Remedial Studies</em></p>',
                'type' => 'richtext',
                'label' => 'Student dashboard notice',
                'description' => 'Rich-text announcement shown in the notice popup on the student dashboard. Leave empty to hide the popup.',
                'is_public' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'student_dashboard_notice')->delete();
    }
};
