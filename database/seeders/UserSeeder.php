<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/UserSeeder.php

public function run(): void
{
    // Admin Utama
    User::create([
        'name' => 'Admin',
        'email' => 'admin@gmail.com',
        'password' => Hash::make('password'),
        'phone_number' => '081234567890',
        'address' => 'Kantor Pusat',
        'role' => 'admin', // Perubahan di sini
        'is_active' => true,
    ]);

    // User Biasa
    User::create([
        'name' => 'User Biasa',
        'email' => 'user@gmail.com',
        'password' => Hash::make('password'),
        'phone_number' => '089876543210',
        'address' => 'Alamat User',
        'role' => 'user', // Perubahan di sini
        'is_active' => true,
    ]);
}
}
