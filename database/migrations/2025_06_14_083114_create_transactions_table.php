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
        // For MySQL/MariaDB, we need to use raw SQL to modify enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('topup', 'coin_exchange_to_rp', 'scan_reward', 'transfer_out', 'manual_topup') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('topup', 'coin_exchange_to_rp', 'scan_reward') NOT NULL");
    }
};
