@extends('layouts.app')

@section('content')
<div class="container-fluid admin-page-content">
    <h2 class="mb-4">Riwayat Scan User</h2>

    <div class="card shadow mb-4">
         <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Riwayat</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
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
                            {{-- Tanda ?-> akan mencegah error jika data relasi (user/dropbox) sudah tidak ada --}}
                            <td>{{ $history->user?->name ?? 'User Telah Dihapus' }}</td>
                            <td>{{ $history->dropbox?->location_name ?? 'Dropbox Telah Dihapus' }}</td>
                            <td>
                                @if($history->status == 'success')
                                    <span class="badge bg-success">Success</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
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
</div>
@endsection