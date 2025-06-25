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
    public function run(): void
    {
        // Hapus data user lama agar tidak ada duplikat email
        User::truncate();

        // Buat Akun Admin
        User::create([
            'name' => 'Admin EcoCycle',
            'email' => 'admin@ecocycle.com',
            'password' => Hash::make('password'), // passwordnya adalah 'password'
            'role' => 'admin', // <-- INI PENENTU ADMIN
        ]);

        // Buat Akun User Biasa
        User::create([
            'name' => 'User Biasa',
            'email' => 'user@ecocycle.com',
            'password' => Hash::make('password'), // passwordnya adalah 'password'
            'role' => 'user', // <-- INI PENENTU USER BIASA
        ]);
    }
}
