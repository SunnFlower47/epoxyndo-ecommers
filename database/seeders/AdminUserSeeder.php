<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@epoxyndo.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('super_admin');

        $this->command->info("✅ Super Admin user seeded: admin@epoxyndo.com / password");
    }
}
