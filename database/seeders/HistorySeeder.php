<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\History;
use App\Models\User;
use App\Models\Dropbox;

class HistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        History::truncate(); // Kosongkan tabel history lama

        // Ambil semua user (yang bukan admin) dan dropbox yang ada
        $users = User::where('is_admin', false)->get();
        $dropboxes = Dropbox::all();

        // Pastikan ada user dan dropbox untuk di-seed
        if ($users->isEmpty() || $dropboxes->isEmpty()) {
            $this->command->info('Tidak dapat membuat data history karena tidak ada user atau dropbox.');
            return;
        }

        // Buat 5 data history contoh
        for ($i = 0; $i < 5; $i++) {
            History::create([
                'user_id' => $users->random()->id,
                'dropbox_id' => $dropboxes->random()->id,
                'status' => ['success', 'failed'][array_rand(['success', 'failed'])],
            ]);
        }
    }
}
