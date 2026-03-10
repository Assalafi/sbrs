<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Settings
            'settings.view',
            'settings.edit',

            // Academic Sessions
            'academic-sessions.view',
            'academic-sessions.create',
            'academic-sessions.edit',
            'academic-sessions.delete',

            // Programmes
            'programmes.view',
            'programmes.create',
            'programmes.edit',
            'programmes.delete',

            // Fees
            'fees.view',
            'fees.create',
            'fees.edit',
            'fees.delete',

            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Applications
            'applications.view',
            'applications.approve',
            'applications.reject',

            // Screening
            'screening.view',
            'screening.approve',
            'screening.reject',

            // Students
            'students.view',
            'students.export',

            // Payments
            'payments.view',
            'payments.verify',
            'payments.export',

            // Courses
            'courses.view',
            'courses.create',
            'courses.edit',
            'courses.delete',

            // Results
            'results.view',
            'results.upload',

            // Audit Logs
            'audit-logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Super Admin gets all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin role with most permissions
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::whereNotIn('name', [
            'users.delete', 'roles.delete', 'settings.edit',
        ])->get());

        // Staff role with limited permissions
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'dashboard.view',
            'applications.view',
            'screening.view',
            'students.view',
            'payments.view',
            'courses.view',
            'results.view',
        ]);
    }
}
