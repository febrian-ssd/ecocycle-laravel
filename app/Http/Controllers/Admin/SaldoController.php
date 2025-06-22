<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TopupRequest; // Assuming you have this model
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SaldoController extends Controller
{
    /**
     * Menampilkan halaman manajemen top up saldo.
     */
    public function topupIndex()
    {
        try {
            // Ambil semua permintaan top up dengan relasi user
            $topupRequests = TopupRequest::with('user')
                                       ->latest()
                                       ->get();

            // Hitung statistik untuk dashboard
            $pendingRequests = $topupRequests->where('status', 'pending')->count();
            $approvedRequests = $topupRequests->where('status', 'approved')->count();
            $totalRequests = $topupRequests->count();
            $totalAmount = $topupRequests->sum('amount');

            // Ambil semua user untuk dropdown manual topup
            $users = User::where('is_admin', false)
                        ->orderBy('name')
                        ->get();

            return view('admin.saldo.topup_index', compact(
                'topupRequests',
                'pendingRequests',
                'approvedRequests',
                'totalRequests',
                'totalAmount',
                'users'
            ));

        } catch (\Exception $e) {
            Log::error('Error in topupIndex: ' . $e->getMessage());

            // Fallback data jika terjadi error
            $topupRequests = collect();
            $pendingRequests = 0;
            $approvedRequests = 0;
            $totalRequests = 0;
            $totalAmount = 0;
            $users = collect();

            return view('admin.saldo.topup_index', compact(
                'topupRequests',
                'pendingRequests',
                'approvedRequests',
                'totalRequests',
                'totalAmount',
                'users'
            ))->with('error', 'Terjadi kesalahan saat memuat data. Silakan refresh halaman.');
        }
    }

    /**
     * Menyetujui permintaan top up saldo.
     */
    public function approveTopup(Request $request, $id)
    {
        try {
            $topupRequest = TopupRequest::findOrFail($id);

            if ($topupRequest->status !== 'pending') {
                return redirect()->route('admin.saldo.topup.index')
                               ->with('error', 'Permintaan top up sudah diproses sebelumnya!');
            }

            DB::beginTransaction();

            // Update status permintaan
            $topupRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'admin_note' => 'Disetujui oleh admin'
            ]);

            // Tambahkan saldo ke user
            $user = $topupRequest->user;
            $user->increment('saldo', $topupRequest->amount);

            // Log aktivitas (opsional)
            Log::info("Top up approved: {$topupRequest->amount} for user {$user->name}");

            DB::commit();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('success', "Top up saldo sebesar Rp " . number_format($topupRequest->amount, 0, ',', '.') . " untuk {$user->name} berhasil disetujui!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving topup: ' . $e->getMessage());

            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Gagal menyetujui top up: ' . $e->getMessage());
        }
    }

    /**
     * Menolak permintaan top up saldo.
     */
    public function rejectTopup(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            $topupRequest = TopupRequest::findOrFail($id);

            if ($topupRequest->status !== 'pending') {
                return redirect()->route('admin.saldo.topup.index')
                               ->with('error', 'Permintaan top up sudah diproses sebelumnya!');
            }

            DB::beginTransaction();

            // Update status permintaan
            $topupRequest->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'admin_note' => $request->reason
            ]);

            // Log aktivitas
            Log::info("Top up rejected: {$topupRequest->amount} for user {$topupRequest->user->name}");

            DB::commit();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('success', "Permintaan top up dari {$topupRequest->user->name} berhasil ditolak!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting topup: ' . $e->getMessage());

            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Gagal menolak top up: ' . $e->getMessage());
        }
    }

    /**
     * Top up manual oleh admin.
     */
    public function manualTopup(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:10000|max:10000000',
                'note' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);

            // Tambah saldo langsung
            $user->increment('saldo', $request->amount);

            // Buat record top up request untuk tracking
            TopupRequest::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'approved',
                'type' => 'manual',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'admin_note' => $request->note ?: 'Top up manual oleh admin'
            ]);

            // Log aktivitas
            Log::info("Manual topup: {$request->amount} for user {$user->name}");

            DB::commit();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('success', "Top up manual sebesar Rp " . number_format($request->amount, 0, ',', '.') . " untuk {$user->name} berhasil!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in manual topup: ' . $e->getMessage());

            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Gagal melakukan top up manual: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint untuk statistik real-time.
     */
    public function getStats()
    {
        try {
            $topupRequests = TopupRequest::all();

            return response()->json([
                'pending' => $topupRequests->where('status', 'pending')->count(),
                'approved' => $topupRequests->where('status', 'approved')->count(),
                'total' => $topupRequests->count(),
                'totalAmount' => $topupRequests->sum('amount')
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting stats: ' . $e->getMessage());
            return response()->json([
                'pending' => 0,
                'approved' => 0,
                'total' => 0,
                'totalAmount' => 0
            ], 500);
        }
    }

    /**
     * Menampilkan detail permintaan top up.
     */
    public function show($id)
    {
        try {
            $topupRequest = TopupRequest::with(['user', 'approvedBy', 'rejectedBy'])->findOrFail($id);
            return view('admin.saldo.topup_show', compact('topupRequest'));
        } catch (\Exception $e) {
            Log::error('Error showing topup detail: ' . $e->getMessage());
            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Data tidak ditemukan.');
        }
    }

    /**
     * Export data top up ke Excel.
     */
    public function export(Request $request)
    {
        try {
            $query = TopupRequest::with('user')->latest();

            // Filter berdasarkan parameter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            $topupRequests = $query->get();

            $filename = 'topup-requests-' . date('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($topupRequests) {
                $file = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($file, [
                    'ID Permintaan',
                    'Nama User',
                    'Email User',
                    'Nominal',
                    'Status',
                    'Waktu Permintaan',
                    'Waktu Diproses',
                    'Catatan Admin'
                ]);

                // CSV Data
                foreach ($topupRequests as $request) {
                    fputcsv($file, [
                        $request->id,
                        $request->user->name ?? 'Unknown',
                        $request->user->email ?? 'No email',
                        $request->amount,
                        ucfirst($request->status),
                        $request->created_at->format('d/m/Y H:i:s'),
                        $request->approved_at ? $request->approved_at->format('d/m/Y H:i:s') :
                        ($request->rejected_at ? $request->rejected_at->format('d/m/Y H:i:s') : '-'),
                        $request->admin_note ?: '-'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting data: ' . $e->getMessage());
            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Gagal mengexport data.');
        }
    }

    /**
     * Mendapatkan riwayat top up user tertentu.
     */
    public function userHistory($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $topupHistory = TopupRequest::where('user_id', $user->id)
                                      ->latest()
                                      ->get();

            return view('admin.saldo.user_history', compact('user', 'topupHistory'));
        } catch (\Exception $e) {
            Log::error('Error getting user history: ' . $e->getMessage());
            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Data user tidak ditemukan.');
        }
    }
}
