<?php
// app/Http/Controllers/Api/EcopayController.php - FIXED VERSION

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Transaction;
use App\Models\User;
use App\Models\TopupRequest;

class EcopayController extends Controller
{
    /**
     * Create topup request - FIXED VERSION
     */
    public function createTopupRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:10000|max:10000000',
                'payment_method' => 'nullable|string|max:100',
                'user_note' => 'nullable|string|max:500',
            ]);

            $user = $request->user();

            if (!Schema::hasTable('topup_requests')) {
                Log::error('TopupRequest table missing - creating basic structure');
                Schema::create('topup_requests', function ($table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained()->onDelete('cascade');
                    $table->decimal('amount', 15, 2);
                    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                    $table->enum('type', ['manual', 'request'])->default('request');
                    $table->string('payment_method')->nullable();
                    $table->text('user_note')->nullable();
                    $table->text('admin_note')->nullable();
                    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                    $table->timestamp('approved_at')->nullable();
                    $table->timestamps();
                });
            }

            DB::beginTransaction();

            $topupRequest = TopupRequest::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
                'type' => 'request',
                'payment_method' => $validated['payment_method'] ?? 'transfer_bank',
                'user_note' => $validated['user_note'] ?? '',
            ]);

            DB::commit();

            Log::info("Topup request created: ID {$topupRequest->id}, Amount {$validated['amount']}, User {$user->id}");

            // PERBAIKAN: Menambahkan (float) untuk memastikan tipe data benar
            return response()->json([
                'success' => true,
                'message' => 'Permintaan top up berhasil dibuat. Silakan tunggu konfirmasi admin.',
                'data' => [
                    'request_id' => $topupRequest->id,
                    'amount' => $topupRequest->amount,
                    'status' => $topupRequest->status,
                    'created_at' => $topupRequest->created_at,
                    'formatted_amount' => 'Rp ' . number_format((float)$topupRequest->amount, 0, ',', '.'),
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Topup request creation failed', ['user_id' => $request->user()->id ?? 'unknown', 'amount' => $request->input('amount'), 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal membuat permintaan top up.', 'error_code' => 'TOPUP_REQUEST_FAILED'], 500);
        }
    }

    /**
     * Get user topup requests - IMPROVED VERSION
     */
    public function getTopupRequests(Request $request)
    {
        try {
            $user = $request->user();

            if (!Schema::hasTable('topup_requests')) {
                return response()->json(['success' => true, 'data' => [], 'message' => 'Belum ada permintaan top up']);
            }

            $requests = TopupRequest::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->get()
                        ->map(function($request) {
                            return [
                                'id' => $request->id,
                                'amount' => (float) $request->amount,
                                // PERBAIKAN: Menambahkan (float)
                                'formatted_amount' => 'Rp ' . number_format((float)$request->amount, 0, ',', '.'),
                                'status' => $request->status,
                                'status_label' => $this->getStatusLabel($request->status),
                                'payment_method' => $request->payment_method,
                                'user_note' => $request->user_note,
                                'admin_note' => $request->admin_note,
                                'created_at' => $request->created_at,
                                'approved_at' => $request->approved_at,
                                'formatted_date' => $request->created_at->format('d M Y H:i'),
                            ];
                        });

            return response()->json(['success' => true, 'data' => $requests]);
        } catch (\Exception $e) {
            Log::error('Get topup requests failed', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data permintaan top up', 'data' => []], 500);
        }
    }

    private function getStatusLabel($status)
    {
        $labels = ['pending' => 'Menunggu Konfirmasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
        return $labels[$status] ?? 'Unknown';
    }

    /**
     * Transfer saldo - ENHANCED ERROR HANDLING
     */
    public function transfer(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'amount' => 'required|numeric|min:1000',
                'description' => 'nullable|string|max:255',
            ]);

            $user = $request->user();
            $amount = (float) $validated['amount'];
            $recipientEmail = $validated['email'];

            if (($user->balance_rp ?? 0) < $amount) {
                return response()->json(['success' => false, 'message' => 'Saldo Anda tidak mencukupi.'], 422);
            }

            $recipient = User::where('email', $recipientEmail)->first();
            if (!$recipient) {
                return response()->json(['success' => false, 'message' => 'Email penerima tidak terdaftar.'], 404);
            }

            if ($recipient->id === $user->id) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat transfer ke diri sendiri.'], 422);
            }

            DB::beginTransaction();

            $user->decrement('balance_rp', $amount);
            $recipient->increment('balance_rp', $amount);

            Transaction::create(['user_id' => $user->id, 'type' => 'transfer_out', 'amount_rp' => -$amount, 'description' => "Transfer ke {$recipient->name}"]);
            Transaction::create(['user_id' => $recipient->id, 'type' => 'transfer_in', 'amount_rp' => $amount, 'description' => "Transfer dari {$user->name}"]);

            DB::commit();

            Log::info("Transfer successful", ['from_user' => $user->id, 'to_user' => $recipient->id, 'amount' => $amount]);

            // PERBAIKAN: Menambahkan (float)
            return response()->json([
                'success' => true,
                'message' => 'Transfer berhasil!',
                'data' => [
                    'amount_transferred' => $amount,
                    'formatted_amount' => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'recipient_name' => $recipient->name,
                    'new_balance_rp' => (float) $user->fresh()->balance_rp,
                    'formatted_balance' => 'Rp ' . number_format((float)$user->fresh()->balance_rp, 0, ',', '.'),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer failed', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal melakukan transfer.'], 500);
        }
    }

    public function getWallet(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'success' => true,
                'message' => 'Wallet data retrieved successfully',
                'data' => [
                    'balance_rp' => $user->balance_rp ?? 0,
                    'balance_koin' => $user->balance_koin ?? 0,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get wallet data', 'error' => $e->getMessage()], 500);
        }
    }
}
