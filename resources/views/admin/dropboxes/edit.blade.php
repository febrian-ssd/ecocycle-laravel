@extends('layouts.app')

@section('content')
<div class="container admin-page-content">
    <h2>Edit Lokasi Dropbox</h2>

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

    <form action="{{ route('admin.dropboxes.update', $dropbox->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- Penting untuk proses update --}}

        <div class="mb-3">
            <label for="location_name" class="form-label">Nama Lokasi</label>
            <input type="text" name="location_name" class="form-control" value="{{ $dropbox->location_name }}">
        </div>
        <div class="mb-3">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="text" name="latitude" class="form-control" value="{{ $dropbox->latitude }}">
        </div>
         <div class="mb-3">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="text" name="longitude" class="form-control" value="{{ $dropbox->longitude }}">
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" {{ $dropbox->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="maintenance" {{ $dropbox->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.dropboxes.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
