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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body, html { margin: 0; font-family: 'Nunito', sans-serif; background-color: #f4f4f4; }
        .page-container { display: flex; height: 100vh; }
        .content-area { flex-grow: 1; position: relative; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 10px 25px; background-color: #004d00; color: white; height: 60px; box-sizing: border-box;}
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header .logo { display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: bold; }
        .header .logo img { height: 35px; }
        .header .menu-toggle { font-size: 24px; cursor: pointer; }
        .header .user-info { display: flex; align-items: center; gap: 15px; font-weight: bold; color: white;}
        .header .login-btn { background-color: #ff8c00; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .sidebar { position: fixed; top: 0; left: -250px; width: 250px; height: 100%; background-color: #003300; padding-top: 20px; transition: 0.3s; z-index: 1002; }
        .sidebar.active { left: 0; }
        .sidebar a { padding: 15px 20px; text-decoration: none; font-size: 18px; color: white; display: block; }
        .sidebar a:hover { background-color: #004d00; }
        .sidebar .logout-btn { position: absolute; bottom: 20px; left: 20px; right: 20px; background-color: #ff8c00; color: white !important; text-align: center; padding: 10px; border-radius: 5px; cursor: pointer;}
        .main-content { height: calc(100vh - 60px); }
        .content-wrapper { filter: blur(0); transition: filter .3s; }
        .content-wrapper.blurred { filter: blur(5px); }
        .popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); display: none; justify-content: center; align-items: center; z-index: 2000; }
        .popup-overlay.show { display: flex; }
        .popup-container { background: #004d00; padding: 30px 40px; border-radius: 25px; color: white; text-align: center; width: 90%; max-width: 400px; position: relative; }
        .popup-container .close-btn { position: absolute; top: 10px; right: 20px; font-size: 30px; cursor: pointer; color: #fff; }
        .popup-container .popup-logo { height: 60px; margin-bottom: 8px; }
        .popup-container h3 { margin-top:0; margin-bottom: 20px; font-weight: bold; }
        .popup-container .input-wrapper { position: relative; margin-bottom: 15px; }
        .popup-container .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #888; }
        .popup-container .form-control { background-color: #f0f0f0; border: none; border-radius: 10px; padding: 15px 15px 15px 45px; width: 100%; box-sizing: border-box; color: #333; }
        .popup-container .btn-submit { background-color: #ff8c00; color: white; padding: 15px; border: none; border-radius: 10px; width: 100%; font-size: 16px; cursor: pointer; font-weight: bold; }
        .popup-container .auth-link { margin-top: 15px; }
        .popup-container .auth-link a { color: #ffc107; text-decoration: underline; cursor: pointer; }
    </style>
</head>
<body>
    <div id="app">
        @auth
            <nav id="sidebar" class="sidebar">
                <a href="{{ route('home') }}">Lihat Peta</a>
                <a href="{{ route('admin.users.index') }}">Data User</a>
                <a href="{{ route('admin.dropboxes.index') }}">Data Dropbox</a>
                <a href="{{ route('admin.history.index') }}">Riwayat Scan User</a>
                <a href="{{ route('admin.saldo.topup.index') }}">Saldo User</a>
                <a class="logout-btn" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">Logout</a>
                <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
            </nav>
        @endauth

        <div id="content-wrapper">
            <header class="header">
                <div class="header-left">
                    @auth @if(Auth::user()->is_admin)
                        <span class="menu-toggle" id="menu-toggle">&#9776;</span>
                    @endif @endauth
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
                <span class="close-btn" data-close-popup>&times;</span>
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="popup-logo">
                <h3>Selamat Datang!</h3>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="input-wrapper"><i class="fas fa-envelope input-icon"></i><input type="email" name="email" class="form-control" required placeholder="Email"></div>
                    <div class="input-wrapper"><i class="fas fa-lock input-icon"></i><input type="password" name="password" class="form-control" required placeholder="Password"></div>
                    <button type="submit" class="btn-submit">Login</button>
                    <div class="auth-link">Belum punya akun? <a id="show-register-link">Buat Akun</a></div>
                </form>
            </div>
        </div>
        <div id="register-popup" class="popup-overlay">
            <div class="popup-container">
                <span class="close-btn" data-close-popup>&times;</span>
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="popup-logo">
                <h3>Buat Akun Baru</h3>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="input-wrapper"><i class="fas fa-user input-icon"></i><input type="text" name="name" class="form-control" required placeholder="Nama Lengkap"></div>
                    <div class="input-wrapper"><i class="fas fa-envelope input-icon"></i><input type="email" name="email" class="form-control" required placeholder="Email"></div>
                    <div class="input-wrapper"><i class="fas fa-lock input-icon"></i><input type="password" name="password" class="form-control" required placeholder="Password (min. 8 karakter)"></div>
                    <div class="input-wrapper"><i class="fas fa-lock input-icon"></i><input type="password" name="password_confirmation" class="form-control" required placeholder="Konfirmasi Password"></div>
                    <button type="submit" class="btn-submit">Register</button>
                    <div class="auth-link">Sudah punya akun? <a id="show-login-link">Login di sini</a></div>
                </form>
            </div>
        </div>
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const contentWrapper = document.getElementById('content-wrapper');
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                });
            }

            const loginPopupBtn = document.getElementById('login-popup-btn');
            const loginPopup = document.getElementById('login-popup');
            const registerPopup = document.getElementById('register-popup');
            const showRegisterLink = document.getElementById('show-register-link');
            const showLoginLink = document.getElementById('show-login-link');
            const closeButtons = document.querySelectorAll('[data-close-popup]');

            function openPopup(popupElement) {
                if(popupElement) {
                    popupElement.classList.add('show');
                    contentWrapper.classList.add('blurred');
                }
            }
            function closePopups() {
                loginPopup?.classList.remove('show');
                registerPopup?.classList.remove('show');
                contentWrapper.classList.remove('blurred');
            }
            if (loginPopupBtn) { loginPopupBtn.addEventListener('click', () => openPopup(loginPopup)); }
            closeButtons.forEach(btn => btn.addEventListener('click', closePopups));
            if (showRegisterLink) { showRegisterLink.addEventListener('click', (e) => { e.preventDefault(); closePopups(); openPopup(registerPopup); }); }
            if (showLoginLink) { showLoginLink.addEventListener('click', (e) => { e.preventDefault(); closePopups(); openPopup(loginPopup); }); }

            @if($errors->any())
                @if(old('name'))
                    openPopup(registerPopup);
                @else
                    openPopup(loginPopup);
                @endif
            @endif
        });
    </script>
    @yield('scripts')
</body>
</html>
