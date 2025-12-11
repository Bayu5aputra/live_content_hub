<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmins = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@next.com',
                'password' => Hash::make('password123'),
                'is_super_admin' => true,
            ],
            [
                'name' => 'Bayu Admin',
                'email' => 'bayu@next.com',
                'password' => Hash::make('password123'),
                'is_super_admin' => true,
            ],
        ];

        foreach ($superAdmins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }
    }
}
