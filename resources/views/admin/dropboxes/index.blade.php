@extends('layouts.app')

@section('content')
<div class="container admin-page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Data Dropbox</h2>
        <a class="btn btn-success" href="{{ route('admin.dropboxes.create') }}">Tambah Dropbox Baru</a>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body text-center">
                    <h1 class="card-title">{{ $totalDropboxes }}</h1>
                    <p class="card-text">Total Dropbox</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body text-center">
                    <h1 class="card-title">{{ $activeDropboxes }}</h1>
                    <p class="card-text">Dropbox Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body text-center">
                    <h1 class="card-title">{{ $maintenanceDropboxes }}</h1>
                    <p class="card-text">Maintenance</p>
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
                        <th>Nama Lokasi</th>
                        <th>Status</th>
                        <th width="150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dropboxes as $dropbox)
                    <tr>
                        <td>{{ $dropbox->id }}</td>
                        <td>{{ $dropbox->location_name }}</td>
                        <td>
                            @if($dropbox->status == 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-warning text-dark">Maintenance</span>
                            @endif
                        </td>
                        <td>
                            {{-- Tombol Edit akan kita tambahkan di tahap selanjutnya --}}
                            <a href="{{ route('admin.dropboxes.edit', $dropbox->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data dropbox.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
