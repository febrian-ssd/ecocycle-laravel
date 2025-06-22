<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TopupRequest; // Assuming you have this model
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaldoController extends Controller
{
    /**
     * Menampilkan halaman manajemen top up saldo.
     */
    public function topupIndex()
    {
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

        return view('admin.saldo.topup.index', compact(
            'topupRequests',
            'pendingRequests',
            'approvedRequests',
            'totalRequests',
            'totalAmount',
            'users'
        ));
    }

    /**
     * Menyetujui permintaan top up saldo.
     */
    public function approveTopup(Request $request, TopupRequest $topupRequest)
    {
        if ($topupRequest->status !== 'pending') {
            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Permintaan top up sudah diproses sebelumnya!');
        }

        try {
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
            // ActivityLog::create([...]);

            DB::commit();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('success', "Top up saldo sebesar Rp " . number_format($topupRequest->amount, 0, ',', '.') . " untuk {$user->name} berhasil disetujui!");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Gagal menyetujui top up: ' . $e->getMessage());
        }
    }

    /**
     * Menolak permintaan top up saldo.
     */
    public function rejectTopup(Request $request, TopupRequest $topupRequest)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        if ($topupRequest->status !== 'pending') {
            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Permintaan top up sudah diproses sebelumnya!');
        }

        try {
            DB::beginTransaction();

            // Update status permintaan
            $topupRequest->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'admin_note' => $request->reason
            ]);

            // Send notification to user (opsional)
            // Notification::send($topupRequest->user, new TopupRejectedNotification($topupRequest));

            DB::commit();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('success', "Permintaan top up dari {$topupRequest->user->name} berhasil ditolak!");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Gagal menolak top up: ' . $e->getMessage());
        }
    }

    /**
     * Top up manual oleh admin.
     */
    public function manualTopup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:10000|max:10000000',
            'note' => 'nullable|string|max:500'
        ]);

        try {
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

            // Log aktivitas (opsional)
            // ActivityLog::create([...]);

            DB::commit();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('success', "Top up manual sebesar Rp " . number_format($request->amount, 0, ',', '.') . " untuk {$user->name} berhasil!");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.saldo.topup.index')
                           ->with('error', 'Gagal melakukan top up manual: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint untuk statistik real-time.
     */
    public function getStats()
    {
        $topupRequests = TopupRequest::all();

        return response()->json([
            'pending' => $topupRequests->where('status', 'pending')->count(),
            'approved' => $topupRequests->where('status', 'approved')->count(),
            'total' => $topupRequests->count(),
            'totalAmount' => $topupRequests->sum('amount')
        ]);
    }

    /**
     * Menampilkan detail permintaan top up.
     */
    public function show(TopupRequest $topupRequest)
    {
        $topupRequest->load(['user', 'approvedBy', 'rejectedBy']);
        return view('admin.saldo.topup.show', compact('topupRequest'));
    }

    /**
     * Export data top up ke Excel.
     */
    public function export(Request $request)
    {
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
                    $request->user->name,
                    $request->user->email,
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
    }

    /**
     * Mendapatkan riwayat top up user tertentu.
     */
    public function userHistory(User $user)
    {
        $topupHistory = TopupRequest::where('user_id', $user->id)
                                  ->latest()
                                  ->get();

        return view('admin.saldo.user-history', compact('user', 'topupHistory'));
    }
}
