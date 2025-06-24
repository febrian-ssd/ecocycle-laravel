<?php

// Create this migration: php artisan make:migration add_missing_columns_to_histories_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('histories', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('histories', 'coins_earned')) {
                $table->integer('coins_earned')->default(0)->after('id');
            }
            if (!Schema::hasColumn('histories', 'weight')) {
                $table->decimal('weight', 8, 2)->default(0)->after('coins_earned');
            }
            if (!Schema::hasColumn('histories', 'weight_g')) {
                $table->decimal('weight_g', 8, 2)->default(0)->after('weight');
            }
            if (!Schema::hasColumn('histories', 'waste_type')) {
                $table->string('waste_type')->nullable()->after('weight_g');
            }
            if (!Schema::hasColumn('histories', 'dropbox_id')) {
                $table->integer('dropbox_id')->nullable()->after('waste_type');
            }
            if (!Schema::hasColumn('histories', 'activity_type')) {
                $table->string('activity_type')->default('scan')->after('dropbox_id');
            }
        });
    }

    public function down()
    {
        Schema::table('histories', function (Blueprint $table) {
            $table->dropColumn([
                'coins_earned', 'weight', 'weight_g',
                'waste_type', 'dropbox_id', 'activity_type'
            ]);
        });
    }
};
