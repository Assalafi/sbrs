<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sbrs.umstad.online'],
            [
                'name' => 'Super Admin',
                'phone' => '08000000000',
                'password' => Hash::make('Admin@SBRS' . date('Y')),
                'is_active' => true,
            ]
        );

        $admin->assignRole('Super Admin');
    }
}
