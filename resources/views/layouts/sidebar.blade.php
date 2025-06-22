{{-- File: resources/views/layouts/sidebar.blade.php --}}

{{-- File: resources/views/layouts/sidebar.blade.php --}}
<nav id="sidebar" class="sidebar">
    <a href="{{ route('home') }}">Lihat Peta</a>
    <a href="{{ route('admin.users.index') }}">Data User</a>
    <a href="{{ route('admin.dropboxes.index') }}">Data Dropbox</a>
    <a href="{{ route('admin.history.index') }}">Riwayat Scan User</a>
    <a href="{{ route('admin.saldo.topup.index') }}">Saldo User</a>
    <a class="logout-btn" href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
        Logout
    </a>
    <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</nav>

{{-- Style khusus untuk sidebar bisa diletakkan di sini atau di app.blade.php --}}
<style>
#sidebar a {
    padding: 15px 20px;
    text-decoration: none;
    font-size: 18px;
    color: white;
    display: block;
    border-bottom: 1px solid #004d00;
}
#sidebar a:hover {
    background-color: #004d00;
}
#sidebar .logout-btn {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: 20px;
    background-color: #ff8c00;
    color: white !important;
    text-align: center;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    border-bottom: none;
}
</style>
