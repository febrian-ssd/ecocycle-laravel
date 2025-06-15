<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_dropboxes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('dropboxes', function (Blueprint $table) {
        $table->id();
        $table->string('location_name');
        $table->string('latitude');
        $table->string('longitude');
        $table->enum('status', ['active', 'maintenance'])->default('active');
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('dropboxes');
    }
};
