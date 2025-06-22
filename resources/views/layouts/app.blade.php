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

        /* CSS Anda yang sudah ada di sini, tidak perlu diubah */
        body, html { height: 100%; margin: 0; font-family: 'Nunito', sans-serif; background-color: #f4f4f4; }
        .page-container { display: flex; height: 100vh; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 10px 25px; background-color: #004d00; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1001; height: 60px; box-sizing: border-box;}
        .header-left { display: flex; align-items: center; }
        .header .logo { display: flex; align-items: center; font-size: 1.5rem; font-weight: bold; }
        .header .logo img { height: 35px; margin-right: 10px; }
        .header .menu-toggle { font-size: 24px; cursor: pointer; margin-right: 15px; }
        .header .login-btn { background-color: #ff8c00; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .header .user-info { display: flex; align-items: center; gap: 15px; }
        .header .user-info a { color: white; text-decoration: none; font-weight: bold; }
        .sidebar { position: fixed; top: 0; left: -250px; width: 250px; height: 100%; background-color: #003300; padding-top: 80px; transition: 0.3s; z-index: 1000; }
        .sidebar.active { left: 0; }
        .sidebar a { padding: 15px 20px; text-decoration: none; font-size: 18px; color: white; display: block; border-bottom: 1px solid #004d00; }
        .sidebar a:hover { background-color: #004d00; }
        .sidebar .logout-btn { background-color: #ff8c00; color: white; text-align: center; margin: 20px; padding: 10px; border-radius: 5px; cursor: pointer;}
        .content-area { flex-grow: 1; margin-left: 0; transition: margin-left .3s; padding-top: 60px; }
        .content-area.shifted { margin-left: 250px; }
        .main-content { height: 100%; }
        #map { height: calc(100vh - 60px); width: 100%; }
        .admin-page-content { padding: 30px; }
        .popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(5px); }
        .popup-overlay.show { display: flex; }
        .popup-container { background: #004d00; padding: 40px; border-radius: 15px; color: white; text-align: center; width: 90%; max-width: 400px; position: relative; }
        .popup-container .close-btn { position: absolute; top: 10px; right: 20px; font-size: 30px; cursor: pointer; color: #fff; }
        .popup-container input { width: 100%; padding: 12px; margin-bottom: 15px; border: none; border-radius: 5px; box-sizing: border-box; }
        .popup-container .btn-submit { background-color: #ff8c00; color: white; padding: 12px; border: none; border-radius: 5px; width: 100%; font-size: 16px; cursor: pointer; font-weight: bold; }
        .popup-container .social-login { display: flex; justify-content: center; align-items: center; gap: 20px; margin-top: 15px; }
        .popup-container .social-login img { width: 35px; cursor: pointer; }
        .popup-container .text-divider { display: flex; align-items: center; text-align: center; margin: 20px 0; }
        .popup-container .text-divider::before, .popup-container .text-divider::after { content: ''; flex: 1; border-bottom: 1px solid #fff; }
        .popup-container .text-divider:not(:empty)::before { margin-right: .25em; }
        .popup-container .text-divider:not(:empty)::after { margin-left: .25em; }
        .popup-container .auth-link { margin-top: 15px; }
        .popup-container .auth-link a { color: #ffc107; text-decoration: none; cursor: pointer; }
        .alert-danger ul { padding-left: 20px; margin: 0; }
    </style>
</head>
<body>
    <div id="app" class="page-container">
        @auth
            @if(Auth::user()->is_admin)
            <nav id="sidebar" class="sidebar">
                {{-- === INI BARIS BARUNYA === --}}
                <a href="{{ route('home') }}">Lihat Peta</a>
                {{-- ======================== --}}
                <a href="{{ route('admin.users.index') }}">Data User</a>
                <a href="{{ route('admin.dropboxes.index') }}">Data Dropbox</a>
                <a href="{{ route('admin.history.index') }}">Riwayat Scan User</a>
                <a class="logout-btn" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">Logout</a>
                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </nav>
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
                        <button id="login-popup-btn" class="login-btn">Login</button>
                    @else
                        <span>Welcome, {{ Auth::user()->name }}</span>
                        @if(!Auth::user()->is_admin)
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        @endif
                    @endguest
                </div>
            </header>

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    @guest
    <div id="login-popup" class="popup-overlay">
        <div class="popup-container">
            <span class="close-btn" id="close-login-popup-btn">&times;</span>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <h3 style="margin-top:0;">Login</h3>
                @if ($errors->any() && !old('name'))
                    <div class="alert alert-danger">
                        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                    </div>
                @endif
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email">
                <input type="password" name="password" required autocomplete="current-password" placeholder="Password">
                <button type="submit" class="btn-submit">Login</button>
                <div class="text-divider">Or Continue With</div>
                <div class="social-login">
                    <img src="{{ asset('images/google.png') }}" alt="Google">
                    <img src="{{ asset('images/facebook.png') }}" alt="Facebook">
                    <img src="{{ asset('images/x.png') }}" alt="X">
                </div>
                <div class="auth-link"> Belum punya akun? <a id="show-register-popup-btn">Buat Akun</a></div>
            </form>
        </div>
    </div>
    <div id="register-popup" class="popup-overlay">
        <div class="popup-container">
            <span class="close-btn" id="close-register-popup-btn">&times;</span>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <h3 style="margin-top:0;">Register</h3>
                 @if ($errors->any() && old('name'))
                    <div class="alert alert-danger">
                        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                    </div>
                @endif
                <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Nama Lengkap">
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email">
                <input type="password" name="password" required autocomplete="new-password" placeholder="Password">
                <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Konfirmasi Password">
                <button type="submit" class="btn-submit">Register</button>
                <div class="auth-link"> Sudah punya akun? <a id="show-login-popup-btn">Login di sini</a></div>
            </form>
        </div>
    </div>
    @endguest

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const contentArea = document.getElementById('content-area');
            const loginPopupBtn = document.getElementById('login-popup-btn');
            const loginPopup = document.getElementById('login-popup');
            const closeLoginPopupBtn = document.getElementById('close-login-popup-btn');
            const registerPopup = document.getElementById('register-popup');
            const closeRegisterPopupBtn = document.getElementById('close-register-popup-btn');
            const showRegisterPopupBtn = document.getElementById('show-register-popup-btn');
            const showLoginPopupBtn = document.getElementById('show-login-popup-btn');
            if(menuToggle) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    contentArea.classList.toggle('shifted');
                });
            }
            if(loginPopupBtn) {
                 loginPopupBtn.addEventListener('click', () => loginPopup.classList.add('show'));
            }
            if(closeLoginPopupBtn) {
                closeLoginPopupBtn.addEventListener('click', () => loginPopup.classList.remove('show'));
            }
            if(closeRegisterPopupBtn) {
                closeRegisterPopupBtn.addEventListener('click', () => registerPopup.classList.remove('show'));
            }
            if(showRegisterPopupBtn) {
                showRegisterPopupBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    loginPopup.classList.remove('show');
                    registerPopup.classList.add('show');
                });
            }
            if(showLoginPopupBtn) {
                showLoginPopupBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    registerPopup.classList.remove('show');
                    loginPopup.classList.add('show');
                });
            }
            @if ($errors->any())
                @if (old('name'))
                    if(registerPopup) registerPopup.classList.add('show');
                @else
                    if(loginPopup) loginPopup.classList.add('show');
                @endif
            @endif
        });
    </script>
    @yield('scripts')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
