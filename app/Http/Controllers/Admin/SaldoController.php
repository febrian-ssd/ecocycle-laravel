<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TopupRequest;
use App\Models\User;
use App\Models\Transaction;

class SaldoController extends Controller
{
    public function topupIndex()
    {
        $pendingRequests = TopupRequest::where('status', 'pending')->with('user')->latest()->get();
        return view('admin.saldo.topup_index', compact('pendingRequests'));
    }

    public function approveTopup(TopupRequest $topupRequest)
    {
        // Gunakan transaction untuk keamanan data
        DB::transaction(function () use ($topupRequest) {
            // Update status request menjadi 'approved'
            $topupRequest->status = 'approved';
            $topupRequest->processed_by = auth()->id();
            $topupRequest->processed_at = now();
            $topupRequest->save();

            // Tambah saldo ke user yang mengajukan
            $user = $topupRequest->user;
            $user->increment('balance_rp', $topupRequest->amount);

            // Catat ke tabel transactions agar muncul di riwayat EcoPay
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'topup',
                'amount_rp' => $topupRequest->amount,
                'description' => 'Top up saldo disetujui oleh admin.',
            ]);
        });

        return redirect()->route('admin.saldo.topup.index')->with('success', 'Permintaan top up berhasil disetujui.');
    }
}