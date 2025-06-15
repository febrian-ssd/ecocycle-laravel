@extends('layouts.app')

@section('content')
<div class="container admin-page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Riwayat Scan User</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID Riwayat</th>
                        <th>Nama User</th>
                        <th>Lokasi Dropbox</th>
                        <th>Status Scan</th>
                        <th>Waktu Scan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($histories as $history)
                    <tr>
                        <td>{{ $history->id }}</td>
                        {{-- Mengakses nama user melalui relasi Eloquent --}}
                        <td>{{ $history->user->name ?? 'User Dihapus' }}</td>
                        {{-- Mengakses nama lokasi melalui relasi Eloquent --}}
                        <td>{{ $history->dropbox->location_name ?? 'Dropbox Dihapus' }}</td>
                        <td>
                            @if($history->status == 'success')
                                <span class="badge bg-success">Success</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        {{-- Format tanggal agar lebih mudah dibaca --}}
                        <td>{{ $history->created_at->format('d M Y, H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada riwayat scan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
