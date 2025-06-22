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
        Schema::create('topup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Nominal top up
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('type', ['manual', 'transfer', 'ewallet'])->default('transfer');
            $table->string('payment_method')->nullable(); // Bank Transfer, OVO, etc
            $table->string('payment_proof')->nullable(); // Path ke bukti transfer

            // Admin yang memproses
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');

            // Timestamp pemrosesan
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Catatan
            $table->text('admin_note')->nullable(); // Catatan dari admin
            $table->text('user_note')->nullable(); // Catatan dari user

            $table->timestamps();

            // Indexes untuk performa
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_requests');
    }
};
