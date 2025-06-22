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
        // Cek dan tambah kolom is_admin jika belum ada
        if (!Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('email_verified_at');
            });
        }

        // Cek dan tambah kolom saldo jika belum ada
        if (!Schema::hasColumn('users', 'saldo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('saldo', 15, 2)->default(0)->after('is_admin');
            });
        }

        // Set default saldo untuk existing users
        DB::table('users')->whereNull('saldo')->update(['saldo' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'saldo')) {
                $table->dropColumn('saldo');
            }

            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });
    }
};
