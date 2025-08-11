<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // create roles if they don't exist
        foreach (['admin','editor','author'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // bootstrap an admin user (change email/password later if you want)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('12345678')]
        );

        // give the admin role
        $admin->assignRole('admin');
    }
}
