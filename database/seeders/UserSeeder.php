<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'      => 'Super Admin',
            'username'  => 'superadmin',
            'password'  => Hash::make('password'),
            'role'      => 'superadmin',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Admin Alex',
            'username'  => 'admin',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);
    }
}
