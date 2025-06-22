<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EcoCycle Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        /* CSS Utama Aplikasi Web Anda */
        body, html { margin: 0; font-family: 'Nunito', sans-serif; background-color: #f8f9fc; }
        .page-container { display: flex; }
        .content-wrapper { width: 100%; transition: margin-left .3s; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 10px 25px; background-color: #ffffff; color: #5a5c69; border-bottom: 1px solid #e3e6f0; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header .logo { display: flex; align-items: center; gap: 10px; font-size: 1.25rem; font-weight: bold; color: #4e73df; text-decoration: none; }
        .header .logo img { height: 35px; }
        .header .menu-toggle { font-size: 1.25rem; cursor: pointer; color: #858796; background: none; border: 1px solid #d1d3e2; border-radius: .25rem; padding: .25rem .75rem; }
        .header .user-info { display: flex; align-items: center; gap: 15px; font-weight: bold; color: #858796;}
        .header .login-btn { background-color: #ff8c00; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .sidebar { min-width: 250px; max-width: 250px; background-color: #003300; color: #fff; transition: margin-left .3s;}
        .sidebar .sidebar-heading { padding: 1rem 1.25rem; font-size: 1.2rem; font-weight: bold; text-align: center; }
        .sidebar .list-group-item { background-color: #003300; color: rgba(255,255,255,.8); border: none; padding: 1rem 1.25rem; text-decoration: none; display: block;}
        .sidebar .list-group-item:hover, .sidebar .list-group-item.active { background-color: #004d00; color: white; }
        .sidebar .logout-btn { background-color: #ff8c00; color: white !important; text-align: center; border-radius: 5px; cursor: pointer; border: none;}
        .main-content { min-height: calc(100vh - 56px); }
        #map { height: calc(100vh - 56px); width: 100%; }
        .admin-page-content { padding: 30px; }

        /* Style untuk Popup */
        .popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(5px); }
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
    <div id="app" class="page-container">
        @auth
            @include('layouts.sidebar')
        @else
            {{-- Jika user adalah tamu (belum login), kita tampilkan halaman dengan popup --}}
            <div class="content-wrapper" id="content-wrapper" style="width: 100%; margin-left: 0;">
                <header class="header" id="header">
                    <div class="header-left">
                        <div class="logo">
                            <img src="{{ asset('images/logo.png') }}" alt="EcoCycle Logo">
                            <span>EcoCycle</span>
                        </div>
                    </div>
                    <div class="user-info">
                        <button id="login-popup-btn" class="login-btn">Login</button>
                    </div>
                </header>
                <main class="main-content" id="main-content">
                    @yield('content')
                </main>
            </div>
        @endguest

        @auth
            {{-- Jika user sudah login --}}
            <div class="content-area" id="content-area">
                <header class="header">
                    <div class="header-left">
                        @if(Auth::user()->is_admin)
                            <span class="menu-toggle" id="menu-toggle">&#9776;</span>
                        @endif
                        <div class="logo">
                            <img src="{{ asset('images/logo.png') }}" alt="EcoCycle Logo">
                            <span>EcoCycle</span>
                        </div>
                    </div>
                    <div class="user-info">
                        <span>Welcome, {{ Auth::user()->name }}</span>
                    </div>
                </header>
                <main class="main-content">
                    @yield('content')
                </main>
            </div>
        @endauth
    </div>

    @guest
    <div id="login-popup" class="popup-overlay">
        <div class="popup-container">
            <span class="close-btn" data-close-popup>&times;</span>
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="popup-logo">
            <h3>Selamat Datang!</h3>
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
            // Script untuk toggle sidebar admin
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const contentArea = document.querySelector('.content-area');
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', () => {
                    sidebar.style.marginLeft = sidebar.style.marginLeft === '0px' ? '-250px' : '0px';
                    contentArea.style.marginLeft = contentArea.style.marginLeft === '250px' ? '0px' : '250px';
                });
            }

            // Script untuk popup login dan register
            const contentWrapper = document.getElementById('content-wrapper');
            const header = document.getElementById('header');
            const loginPopupBtn = document.getElementById('login-popup-btn');
            const loginPopup = document.getElementById('login-popup');
            const registerPopup = document.getElementById('register-popup');
            const showRegisterLink = document.getElementById('show-register-link');
            const showLoginLink = document.getElementById('show-login-link');
            const closeButtons = document.querySelectorAll('[data-close-popup]');

            function openPopup(popupElement) {
                if(popupElement) {
                    popupElement.classList.add('show');
                    if(contentWrapper) contentWrapper.classList.add('blurred');
                    if(header) header.classList.add('blurred');
                }
            }
            function closePopups() {
                loginPopup?.classList.remove('show');
                registerPopup?.classList.remove('show');
                if(contentWrapper) contentWrapper.classList.remove('blurred');
                if(header) header.classList.remove('blurred');
            }
            if (loginPopupBtn) { loginPopupBtn.addEventListener('click', () => openPopup(loginPopup)); }
            closeButtons.forEach(btn => btn.addEventListener('click', closePopups));
            if (showRegisterLink) { showRegisterLink.addEventListener('click', (e) => { e.preventDefault(); closePopups(); openPopup(registerPopup); }); }
            if (showLoginLink) { showLoginLink.addEventListener('click', (e) => { e.preventDefault(); closePopups(); openPopup(loginPopup); }); }

            // Jika ada error login/register dari server, buka popup yang sesuai
            @if($errors->any())
                @if(old('name')) // 'name' hanya ada di form register
                    openPopup(registerPopup);
                @else // Jika tidak, berarti error dari form login
                    openPopup(loginPopup);
                @endif
            @endif
        });
    </script>
    @yield('scripts')
</body>
</html>
