<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EcoCycle</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body, html { height: 100%; margin: 0; font-family: 'Nunito', sans-serif; background-color: #f4f4f4; }
        .page-container { display: flex; height: 100vh; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 10px 25px; background-color: #004d00; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1001; height: 60px; box-sizing: border-box;}
        .header-left { display: flex; align-items: center; }
        .header .logo { display: flex; align-items: center; font-size: 1.5rem; font-weight: bold; }
        .header .logo img { height: 35px; margin-right: 10px; }
        .header .menu-toggle { font-size: 24px; cursor: pointer; margin-right: 15px; }
        .header .user-info { display: flex; align-items: center; gap: 15px; font-weight: bold; color: white;}
        .header .user-info a { color: #ffc107; text-decoration: none; font-weight: bold; }
        .header .user-info .login-btn { background-color: #ff8c00; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .sidebar { position: fixed; top: 0; left: -250px; width: 250px; height: 100%; background-color: #003300; padding-top: 80px; transition: 0.3s; z-index: 1000; display: flex; flex-direction: column; }
        .sidebar.active { left: 0; }
        .content-area { flex-grow: 1; margin-left: 0; transition: margin-left .3s; padding-top: 60px; }
        .content-area.shifted { margin-left: 250px; }
        .main-content { height: 100%; }
        #map { height: calc(100vh - 60px); width: 100%; }
        .admin-page-content { padding: 30px; }
        .card .border-left-danger { border-left: .25rem solid #e74a3b !important; }
        .card .border-left-info { border-left: .25rem solid #36b9cc !important; }
        .card .border-left-secondary { border-left: .25rem solid #858796 !important; }
        .text-xs { font-size: .7rem; }
    </style>
</head>
<body>
    <div id="app" class="page-container">
        @auth
            {{-- Hanya tampilkan sidebar jika yang login adalah admin --}}
            @if(Auth::user()->is_admin)
                @include('layouts.sidebar')
            @endif
        @endauth

        <div class="content-area" id="content-area">
            <header class="header">
                <div class="header-left">
                    @auth
                        @if(Auth::user()->is_admin)
                            <span class="menu-toggle" id="menu-toggle">&#9776;</span>
                        @endif
                    @endauth
                    <div class="logo">
                        <img src="{{ asset('images/logo.png') }}" alt="EcoCycle Logo">
                        <span>EcoCycle</span>
                    </div>
                </div>

                {{-- === BAGIAN YANG DIPERBAIKI === --}}
                <div class="user-info">
                    @guest
                        {{-- Jika pengunjung, tampilkan tombol Login --}}
                        <a href="{{ route('login') }}" class="login-btn">Login</a>
                    @else
                        {{-- Jika sudah login (baik user maupun admin) --}}
                        <span>Welcome, {{ Auth::user()->name }}</span>

                        {{-- Tombol Profile Icon (hanya untuk Admin) --}}
                        @if(Auth::user()->is_admin)
                            <i class="fas fa-user-circle fa-2x"></i>
                        @endif

                        {{-- Tombol Logout ini sekarang muncul untuk SEMUA yang sudah login --}}
                        {{-- (jika Anda ingin admin hanya bisa logout dari sidebar, hapus link di bawah ini) --}}
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                         document.getElementById('header-logout-form').submit();">
                           Logout
                        </a>
                        <form id="header-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endguest
                </div>
                {{-- ============================== --}}
            </header>

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Popup Login/Register tidak kita gunakan di web admin, jadi bisa dihapus jika mau --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const contentArea = document.getElementById('content-area');
            if(menuToggle) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    contentArea.classList.toggle('shifted');
                });
            }
        });
    </script>
    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
