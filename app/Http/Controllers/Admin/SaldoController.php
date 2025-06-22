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
        DB::transaction(function () use ($topupRequest) {
            $topupRequest->status = 'approved';
            $topupRequest->processed_by = auth()->id();
            $topupRequest->processed_at = now();
            $topupRequest->save();

            $user = $topupRequest->user;
            $user->increment('balance_rp', $topupRequest->amount);

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
