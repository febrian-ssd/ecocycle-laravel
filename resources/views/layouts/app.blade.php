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
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    {{-- Memindahkan semua style ke dalam satu file utama --}}
    <style>
        body, html { height: 100%; margin: 0; font-family: 'Nunito', sans-serif; background-color: #f4f4f4; }
        .page-container { display: flex; height: 100vh; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 10px 25px; background-color: #004d00; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1001; height: 60px; box-sizing: border-box;}
        .header-left { display: flex; align-items: center; }
        .header .logo { display: flex; align-items: center; font-size: 1.5rem; font-weight: bold; }
        .header .logo img { height: 35px; margin-right: 10px; }
        .header .menu-toggle { font-size: 24px; cursor: pointer; margin-right: 15px; }
        .header .user-info { display: flex; align-items: center; gap: 15px; font-weight: bold; color: white;}
        .sidebar { position: fixed; top: 0; left: -250px; width: 250px; height: 100%; background-color: #003300; padding-top: 80px; transition: 0.3s; z-index: 1000; }
        .sidebar.active { left: 0; }
        .content-area { flex-grow: 1; margin-left: 0; transition: margin-left .3s; padding-top: 60px; }
        .content-area.shifted { margin-left: 250px; }
        .main-content { height: 100%; }
        #map { height: calc(100vh - 60px); width: 100%; }
        .admin-page-content { padding: 30px; }
    </style>
</head>
<body>
    <div id="app" class="page-container">
        @auth
            @if(Auth::user()->is_admin)
                {{-- PERUBAHAN UTAMA: Memanggil file sidebar --}}
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
                <div class="user-info">
                    @auth
                        <span>Welcome, {{ Auth::user()->name }}</span>
                    @endauth
                </div>
            </header>

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

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
