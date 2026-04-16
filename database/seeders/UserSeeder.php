<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'phone' => '+380500000001',
                'region' => 'Kyiv region',
                'city' => 'Kyiv',
                'address' => 'Admin office 1',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'status' => UserStatus::Active,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '+380500000002',
                'region' => 'Kyiv region',
                'city' => 'Kyiv',
                'address' => 'Customer street 10',
                'password' => Hash::make('password'),
                'role' => UserRole::Customer,
                'status' => UserStatus::Active,
            ]
        );
    }
}
