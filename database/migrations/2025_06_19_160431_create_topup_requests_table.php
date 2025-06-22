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
        Schema::table('topup_requests', function (Blueprint $table) {
            // Check if columns exist before dropping
            if (Schema::hasColumn('topup_requests', 'processed_by')) {
                $table->dropColumn('processed_by');
            }
            if (Schema::hasColumn('topup_requests', 'processed_at')) {
                $table->dropColumn('processed_at');
            }

            // Add new columns if they don't exist
            if (!Schema::hasColumn('topup_requests', 'type')) {
                $table->enum('type', ['manual', 'request'])->default('request')->after('status');
            }
            if (!Schema::hasColumn('topup_requests', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('type');
            }
            if (!Schema::hasColumn('topup_requests', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('topup_requests', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('payment_proof');
            }
            if (!Schema::hasColumn('topup_requests', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null')->after('approved_by');
            }
            if (!Schema::hasColumn('topup_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('topup_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('topup_requests', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('topup_requests', 'user_note')) {
                $table->text('user_note')->nullable()->after('admin_note');
            }
        });

        // Add indexes
        Schema::table('topup_requests', function (Blueprint $table) {
            if (!$this->indexExists('topup_requests', 'topup_requests_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
            if (!$this->indexExists('topup_requests', 'topup_requests_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topup_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'created_at']);

            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);

            $table->dropColumn([
                'type', 'payment_method', 'payment_proof',
                'approved_by', 'rejected_by', 'approved_at',
                'rejected_at', 'admin_note', 'user_note'
            ]);

            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
        });
    }

    private function indexExists($table, $index)
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
        return array_key_exists($index, $indexes);
    }
};
