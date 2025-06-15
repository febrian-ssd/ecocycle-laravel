@extends('layouts.app')

@section('content')
<div class="container admin-page-content">
    <h2>Tambah Lokasi Dropbox Baru</h2>

    {{-- Menampilkan error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Ada masalah dengan input Anda.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.dropboxes.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="location_name" class="form-label">Nama Lokasi</label>
            <input type="text" name="location_name" class="form-control" id="location_name" placeholder="Contoh: Depan Pintu Masuk Merdeka Walk">
        </div>
        <div class="mb-3">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="text" name="latitude" class="form-control" id="latitude" placeholder="Contoh: 3.59021">
        </div>
         <div class="mb-3">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="text" name="longitude" class="form-control" id="longitude" placeholder="Contoh: 98.67481">
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select" id="status">
                <option value="active" selected>Active</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.dropboxes.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
