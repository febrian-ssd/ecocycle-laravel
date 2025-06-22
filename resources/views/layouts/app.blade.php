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
        /* === STYLE UTAMA === */
        body, html { margin: 0; font-family: 'Nunito', sans-serif; background-color: #f4f4f4; }
        .page-container { display: flex; height: 100vh; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 10px 25px; background-color: #004d00; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1001; height: 60px; box-sizing: border-box;}
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header .logo { display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: bold; }
        .header .logo img { height: 35px; }
        .header .menu-toggle { font-size: 24px; cursor: pointer; }
        .header .user-info { display: flex; align-items: center; gap: 15px; font-weight: bold; color: white;}
        .header .user-info a { color: #ffc107; text-decoration: none; font-weight: bold; }
        .header .login-btn { background-color: #ff8c00; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .sidebar { position: fixed; top: 0; left: -250px; width: 250px; height: 100%; background-color: #003300; padding-top: 80px; transition: 0.3s; z-index: 1000; display: flex; flex-direction: column; }
        .sidebar.active { left: 0; }
        .sidebar a { display: block; padding: 15px 20px; text-decoration: none; font-size: 18px; color: white; border-bottom: 1px solid #004d00; }
        .sidebar a:hover { background-color: #004d00; }
        .sidebar .logout-btn { background-color: #ff8c00; color: white; text-align: center; margin: 20px; padding: 10px; border-radius: 5px; cursor: pointer; position: absolute; bottom: 20px; left: 20px; right: 20px; border-bottom: none;}
        .content-area { flex-grow: 1; margin-left: 0; transition: margin-left .3s, filter .3s; padding-top: 60px; }
        .main-content { height: 100%; }
        #map { height: calc(100vh - 60px); width: 100%; }
        .admin-page-content { padding: 30px; }
        .card .border-left-danger { border-left: .25rem solid #e74a3b !important; }
        .card .border-left-info { border-left: .25rem solid #36b9cc !important; }
        .card .border-left-secondary { border-left: .25rem solid #858796 !important; }
        .text-xs { font-size: .7rem; }

        /* === STYLE UNTUK POPUP LOGIN === */
        .popup-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none; /* Sembunyi by default */
            justify-content: center; align-items: center;
            z-index: 2000;
        }
        .popup-overlay.show {
            display: flex; /* Tampilkan saat aktif */
        }
        .content-area.blurred {
            filter: blur(5px); /* Efek blur untuk background */
        }
        .popup-container {
            background: #004d00; /* Warna hijau tua */
            padding: 40px;
            border-radius: 25px;
            color: white;
            text-align: center;
            width: 90%;
            max-width: 400px;
            position: relative;
        }
        .popup-container .close-btn { position: absolute; top: 10px; right: 20px; font-size: 30px; cursor: pointer; color: #fff; }
        .popup-container .form-control {
            background-color: #f0f0f0; border: none; border-radius: 10px;
            padding: 15px 15px 15px 45px; /* Beri ruang untuk ikon */
            width: 100%; box-sizing: border-box;
        }
        .popup-container .input-wrapper { position: relative; margin-bottom: 15px; }
        .popup-container .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #888; }
        .popup-container .btn-submit { background-color: #ff8c00; color: white; padding: 15px; border: none; border-radius: 10px; width: 100%; font-size: 16px; cursor: pointer; font-weight: bold; }
        .popup-container .create-account { margin-top: 8px; font-size: 14px; }
        .popup-container .create-account a { color: #ffc107; text-decoration: none; cursor: pointer; }
    </style>
</head>
<body>
    <div id="app" class="page-container">
        @auth
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
                <div class="user-info">
                    @guest
                        {{-- TOMBOL INI YANG AKAN MEMICU POPUP --}}
                        <button id="login-popup-btn" class="login-btn">Login</button>
                    @else
                        <span>Welcome, {{ Auth::user()->name }}</span>
                        @if(Auth::user()->is_admin)
                             <i class="fas fa-user-circle fa-2x"></i>
                        @endif
                    @endguest
                </div>
            </header>

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- KODE POPUP LOGIN (HANYA UNTUK TAMU) --}}
    @guest
    <div id="login-popup" class="popup-overlay">
        <div class="popup-container">
            <span class="close-btn" id="close-popup-btn">&times;</span>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" required placeholder="Email">
                </div>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" class="form-control" required placeholder="Password">
                </div>
                <button type="submit" class="btn-submit">Login</button>
                <div class="create-account">
                    <a href="{{ route('register') }}">Create Account</a>
                </div>
            </form>
        </div>
    </div>
    @endguest

    {{-- JAVASCRIPT UNTUK MENGONTROL POPUP --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const contentArea = document.getElementById('content-area');
            const loginPopupBtn = document.getElementById('login-popup-btn');
            const loginPopup = document.getElementById('login-popup');
            const closePopupBtn = document.getElementById('close-popup-btn');

            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    contentArea.classList.toggle('shifted');
                });
            }

            if (loginPopupBtn) {
                 loginPopupBtn.addEventListener('click', () => {
                    loginPopup.classList.add('show');
                    contentArea.classList.add('blurred');
                 });
            }

            if (closePopupBtn) {
                closePopupBtn.addEventListener('click', () => {
                    loginPopup.classList.remove('show');
                    contentArea.classList.remove('blurred');
                });
            }
        });
    </script>
    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
