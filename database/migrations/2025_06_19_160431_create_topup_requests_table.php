<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom is_admin jika belum ada
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('email_verified_at');
            }

            // Tambah kolom balance_rp jika belum ada
            if (!Schema::hasColumn('users', 'balance_rp')) {
                $table->decimal('balance_rp', 15, 2)->default(0)->after('is_admin');
            }
        });

        // Set nilai default balance_rp ke 0 untuk existing users yang nilainya null
        DB::table('users')->whereNull('balance_rp')->update(['balance_rp' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'balance_rp')) {
                $table->dropColumn('balance_rp');
            }

            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });
    }
};
