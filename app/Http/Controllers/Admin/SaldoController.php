<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TopupRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            $topupRequests = TopupRequest::with(['user', 'approvedBy'])->latest()->get();

            $pendingRequests = $topupRequests->where('status', 'pending')->count();
            $approvedRequests = $topupRequests->where('status', 'approved')->count();
            $totalRequests = $topupRequests->count();
            $totalAmount = $topupRequests->where('status', 'approved')->sum('amount');

            // Ambil semua user non-admin untuk dropdown manual topup
            // PERBAIKAN: Menggunakan 'role' bukan 'is_admin'
            $users = User::where('role', 'user')->orderBy('name')->get();

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

            return view('admin.saldo.topup_index', [
                'topupRequests' => collect(),
                'pendingRequests' => 0,
                'approvedRequests' => 0,
                'totalRequests' => 0,
                'totalAmount' => 0,
                'users' => User::where('role', 'user')->orderBy('name')->get()
            ])->with('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
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

            $topupRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'admin_note' => $request->input('admin_note', 'Disetujui oleh admin')
            ]);

            $user = $topupRequest->user;
            $user->increment('balance_rp', $topupRequest->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'topup',
                'amount_rp' => $topupRequest->amount,
                'description' => "Top up saldo disetujui oleh admin - Rp " . number_format($topupRequest->amount, 0, ',', '.')
            ]);

            DB::commit();

            return redirect()->route('admin.saldo.topup.index')
                ->with('success', "Top up untuk {$user->name} berhasil disetujui!");

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
            $request->validate(['reason' => 'required|string|max:500']);
            $topupRequest = TopupRequest::findOrFail($id);

            if ($topupRequest->status !== 'pending') {
                return redirect()->route('admin.saldo.topup.index')
                    ->with('error', 'Permintaan top up sudah diproses sebelumnya!');
            }

            $topupRequest->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'admin_note' => $request->reason
            ]);

            return redirect()->route('admin.saldo.topup.index')
                ->with('success', "Permintaan top up dari {$topupRequest->user->name} berhasil ditolak!");

        } catch (\Exception $e) {
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
            $user->increment('balance_rp', $request->amount);

            TopupRequest::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'approved',
                'type' => 'manual',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'admin_note' => $request->note ?: 'Top up manual oleh admin'
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'manual_topup',
                'amount_rp' => $request->amount,
                'description' => "Top up manual oleh admin - " . ($request->note ?: 'Tanpa catatan khusus')
            ]);

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
}
