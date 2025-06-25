<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Gunakan Schema::create untuk MEMBUAT tabel baru
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('dropbox_id')->constrained()->onDelete('cascade');
            $table->string('waste_type')->nullable(); // Jenis sampah
            $table->integer('points_earned');
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Gunakan dropIfExists untuk menghapus tabel jika ada
        Schema::dropIfExists('histories');
    }
};
