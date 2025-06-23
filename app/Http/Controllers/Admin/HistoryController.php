<?php
// app/Http/Controllers/Admin/HistoryController.php - FINAL CARBON SAFE VERSION

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;
use Carbon\Carbon;

class HistoryController extends Controller
{
    /**
     * Menampilkan halaman riwayat scan dengan statistik.
     */
    public function index()
    {
        $histories = History::with(['user', 'dropbox'])
                           ->latest()
                           ->get();

        // Hitung statistik untuk kartu dashboard
        $totalScans = $histories->count();
        $successScans = $histories->where('status', 'success')->count();
        $failedScans = $histories->where('status', 'failed')->count();

        // Hitung scan hari ini - CARBON SAFE VERSION
        $todayScans = $histories->filter(function ($history) {
            return $this->isCreatedToday($history);
        })->count();

        return view('admin.history.index', compact(
            'histories',
            'totalScans',
            'successScans',
            'failedScans',
            'todayScans'
        ));
    }

    /**
     * Safe method to check if history was created today
     */
    private function isCreatedToday($history)
    {
        try {
            if (!$history->created_at) {
                return false;
            }

            // Convert to Carbon if it's not already
            $date = $history->created_at instanceof \Carbon\Carbon
                ? $history->created_at
                : Carbon::parse($history->created_at);

            return $date->isToday();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Menampilkan detail riwayat scan tertentu.
     */
    public function show(History $history)
    {
        $history->load(['user', 'dropbox']);
        return view('admin.history.show', compact('history'));
    }

    /**
     * Menghapus riwayat scan (opsional).
     */
    public function destroy(History $history)
    {
        try {
            $history->delete();

            return redirect()->route('admin.history.index')
                           ->with('success', 'Riwayat scan berhasil dihapus!');

        } catch (\Exception $e) {
            return redirect()->route('admin.history.index')
                           ->with('error', 'Gagal menghapus riwayat: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint untuk mendapatkan statistik real-time.
     */
    public function getStats()
    {
        $histories = History::all();

        // CARBON SAFE: Use helper method for today check
        $todayCount = $histories->filter(function ($history) {
            return $this->isCreatedToday($history);
        })->count();

        return response()->json([
            'total' => $histories->count(),
            'success' => $histories->where('status', 'success')->count(),
            'failed' => $histories->where('status', 'failed')->count(),
            'today' => $todayCount
        ]);
    }

    /**
     * Export data riwayat ke CSV.
     */
    public function export(Request $request)
    {
        $query = History::with(['user', 'dropbox'])->latest();

        // Filter berdasarkan parameter yang diberikan
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $histories = $query->get();

        $filename = 'riwayat-scan-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($histories) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'ID Riwayat',
                'Nama User',
                'Email User',
                'Lokasi Dropbox',
                'Status Scan',
                'Waktu Scan',
                'Tanggal'
            ]);

            // CSV Data
            foreach ($histories as $history) {
                fputcsv($file, [
                    $history->id,
                    $history->user ? $history->user->name : 'User Telah Dihapus',
                    $history->user ? $history->user->email : 'N/A',
                    $history->dropbox ? $history->dropbox->location_name : 'Dropbox Telah Dihapus',
                    ucfirst($history->status),
                    $history->created_at ? $history->created_at->format('H:i:s') : '',
                    $history->created_at ? $history->created_at->format('d/m/Y') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mendapatkan data untuk chart/grafik (opsional).
     */
    public function getChartData()
    {
        // Data untuk chart mingguan
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayScans = History::whereDate('created_at', $date)->count();
            $weeklyData[] = [
                'date' => $date->format('d/m'),
                'scans' => $dayScans
            ];
        }

        // Data untuk chart status
        $statusData = [
            'success' => History::where('status', 'success')->count(),
            'failed' => History::where('status', 'failed')->count()
        ];

        return response()->json([
            'weekly' => $weeklyData,
            'status' => $statusData
        ]);
    }
}
