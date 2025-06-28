<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing users (optional)
        User::truncate();

        // Admin User
        User::create([
            'name' => 'Admin EcoCycle',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'balance_rp' => 1000000,
            'balance_coins' => 500,
            'phone_number' => '081234567890',
            'address' => 'Kantor Pusat EcoCycle',
        ]);

        // Regular User 1
        User::create([
            'name' => 'User Test',
            'email' => 'user@gmail.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'is_active' => true,
            'balance_rp' => 50000,
            'balance_coins' => 100,
            'phone_number' => '081234567891',
            'address' => 'Jl. Test No. 1',
        ]);

        // Regular User 2
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'is_active' => true,
            'balance_rp' => 25000,
            'balance_coins' => 50,
            'phone_number' => '081234567892',
            'address' => 'Jl. Example No. 2',
        ]);

        // Inactive User (for testing)
        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'is_active' => false,
            'balance_rp' => 0,
            'balance_coins' => 0,
        ]);

        echo "✅ Created " . User::count() . " users\n";
        echo "Admin: admin@gmail.com / password\n";
        echo "User: user@gmail.com / password\n";
    }
}
