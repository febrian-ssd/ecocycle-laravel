@extends('layouts.app')

@section('content')
<div class="container-fluid admin-page-content">
    {{-- Judul Halaman --}}
    <h2 class="mb-4">Konfirmasi Top Up Saldo</h2>

    {{-- Notifikasi jika ada pesan sukses setelah approve --}}
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Kartu yang berisi tabel --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Permintaan Tertunda</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID Request</th>
                            <th>Nama User</th>
                            <th>Jumlah Top Up</th>
                            <th>Tanggal Request</th>
                            <th width="120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop untuk setiap permintaan top up yang statusnya 'pending' --}}
                        @forelse ($pendingRequests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ $request->user->name ?? 'User Tidak Ditemukan' }}</td>
                            <td>Rp {{ number_format($request->amount, 0, ',', '.') }}</td>
                            <td>{{ $request->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                {{-- Form ini akan mengirim request ke method 'approveTopup' --}}
                                <form action="{{ route('admin.saldo.topup.approve', $request->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                    {{-- Nanti bisa ditambahkan tombol 'Tolak' di sini --}}
                                </form>
                            </td>
                        </tr>
                        @empty
                        {{-- Tampilan jika tidak ada permintaan top up --}}
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada permintaan top up yang tertunda.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
