<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom 'role' untuk manajemen peran
            $table->string('role')->default(User::ROLE_USER)->after('password');

            // Tambahkan kolom 'is_active' untuk status pengguna
            $table->boolean('is_active')->default(true)->after('role');

            // Hapus kolom 'is_admin' yang sudah digantikan oleh 'role'
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
            $table->boolean('is_admin')->default(false); // Kembalikan jika migrasi di-rollback
        });
    }
};
