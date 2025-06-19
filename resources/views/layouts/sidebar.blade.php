{{-- File: resources/views/layouts/sidebar.blade.php (Versi Perbaikan) --}}

<div class="sidebar">
    {{-- 
      Mengecek apakah user sudah login dan apakah dia adalah admin.
      Di database, nama kolomnya adalah 'is_admin', jadi kita gunakan itu.
    --}}
    @if(Auth::check() && Auth::user()->is_admin)

        {{-- Menggunakan tag <a> untuk link, dan helper route() untuk URL --}}
        <a class="sidebar-button" href="{{ route('home') }}">Lihat Peta</a>
        <a class="sidebar-button" href="{{ route('admin.users.index') }}">Data User</a>
        <a class="sidebar-button" href="{{ route('admin.dropboxes.index') }}">Data Dropbox</a>
        <a class="sidebar-button" href="{{ route('admin.history.index') }}">Riwayat Scan User</a>
        
        {{-- === INI MENU BARUNYA === --}}
        <a class="sidebar-button" href="{{ route('admin.saldo.topup.index') }}">Saldo User</a>

    @endif

    {{-- 
      Proses Logout harus menggunakan form dengan metode POST untuk keamanan.
      Link ini akan men-submit form yang ada di bawahnya.
    --}}
    <a class="sidebar-button" href="{{ route('logout') }}"
       onclick="event.preventDefault();
                     document.getElementById('sidebar-logout-form').submit();">
        Logout
    </a>

    <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>


{{-- Anda bisa menambahkan CSS ini di file layout utama (app.blade.php) agar link <a> terlihat seperti tombol --}}
<style>
    .sidebar .sidebar-button {
        display: block;
        width: calc(100% - 20px);
        margin: 5px 10px;
        padding: 10px;
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 5px;
        text-align: center;
        cursor: pointer;
        text-decoration: none;
        color: black;
        font-family: sans-serif;
    }
    .sidebar .sidebar-button:hover {
        background-color: #e0e0e0;
    }
</style>