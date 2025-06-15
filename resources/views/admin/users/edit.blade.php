@extends('layouts.app')

@section('content')
<div class="container admin-page-content">
    <h2>Edit User: {{ $user->name }}</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}">
        </div>
        <div class="mb-3">
            <label for="is_admin" class="form-label">Role</label>
            <select name="is_admin" class="form-select">
                <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>User</option>
                <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <hr>
        <p class="text-muted">Isi bagian di bawah ini hanya jika Anda ingin mengubah password.</p>
        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
