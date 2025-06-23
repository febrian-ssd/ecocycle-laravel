<?php
// database/migrations/xxxx_xx_xx_update_histories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            // Add new columns for scan details
            $table->string('waste_type')->nullable()->after('dropbox_id');
            $table->decimal('weight', 8, 3)->nullable()->after('waste_type'); // dalam kg
            $table->integer('coins_earned')->default(0)->after('weight');
            $table->timestamp('scan_time')->nullable()->after('coins_earned');
            $table->text('error_message')->nullable()->after('scan_time');

            // Add indexes
            $table->index(['user_id', 'status']);
            $table->index(['waste_type']);
            $table->index(['scan_time']);
        });
    }

    public function down(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['waste_type']);
            $table->dropIndex(['scan_time']);

            $table->dropColumn([
                'waste_type',
                'weight',
                'coins_earned',
                'scan_time',
                'error_message'
            ]);
        });
    }
};
