<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    $this->call([
        UserSeeder::class,
        DropboxSeeder::class, // Pastikan Anda juga punya seeder untuk dropbox
        HistorySeeder::class, // <-- TAMBAHKAN INI
    ]);
}
}
