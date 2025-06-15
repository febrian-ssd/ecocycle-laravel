@extends('layouts.app')

@section('content')
<div class="container admin-page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Data User</h2>
        {{-- Di sini bisa ditambahkan tombol "Tambah User Baru" jika diperlukan nanti --}}
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body text-center">
                    <h1 class="card-title">{{ $adminCount }}</h1>
                    <p class="card-text">Total Admin</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body text-center">
                    <h1 class="card-title">{{ $onlineUsers }}</h1>
                    <p class="card-text">User Online</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark bg-light mb-3">
                <div class="card-body text-center">
                    <h1 class="card-title">{{ $offlineUsers }}</h1>
                    <p class="card-text">User Offline</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop untuk setiap user di database --}}
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge bg-danger">Admin</span>
                            @else
                                <span class="badge bg-secondary">User</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data user.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
