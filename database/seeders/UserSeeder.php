<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::ROLES as $role) {
            User::updateOrCreate(
                ['email' => $role . '@bengkel.test'],
                [
                    'name'     => ucfirst($role),
                    'password' => Hash::make('password'),
                    'role'     => $role,
                ]
            );
        }
    }
}
