<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EcoCycle - Sistem Pengelolaan Sampah Pintar')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link rel="dns-prefetch" href="//maps.googleapis.com">

    <link href="https://fonts.bunny.net/css?family=Nunito:300,400,500,600,700" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_Maps_API_KEY&libraries=places" defer></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* ===== RESET & BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body,
        html {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
            height: 100%;
            overflow-x: hidden;
        }

        /* ===== LAYOUT STRUCTURE ===== */
        .page-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        .content-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== HEADER STYLES ===== */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 25px;
            background: linear-gradient(135deg, #004d00 0%, #006600 100%);
            color: white;
            height: 70px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.6rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
        }

        .header .logo:hover {
            color: #ffc107;
            transform: scale(1.02);
            transition: all 0.3s ease;
        }

        .header .logo img {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .header .menu-toggle {
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .header .menu-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
        }

        .header .login-btn {
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(255, 140, 0, 0.3);
        }

        .header .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 140, 0, 0.4);
        }

        /* ===== SIDEBAR STYLES ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #003300 0%, #001a00 100%);
            padding-top: 80px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1002;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar a {
            padding: 18px 25px;
            text-decoration: none;
            font-size: 16px;
            color: #e0e0e0;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffc107;
            border-left-color: #ffc107;
            transform: translateX(5px);
        }

        .sidebar a i {
            width: 20px;
            text-align: center;
        }

        .sidebar .logout-btn {
            position: absolute;
            bottom: 30px;
            left: 25px;
            right: 25px;
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            color: white !important;
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            border-left: none !important;
            transform: none !important;
        }

        .sidebar .logout-btn:hover {
            background: linear-gradient(135deg, #ff6b00 0%, #ff5500 100%);
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 10px rgba(255, 140, 0, 0.4);
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
            padding: 0;
            background-color: #f8f9fa;
        }

        .content-wrapper {
            width: 100%;
            transition: all 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper.blurred {
            filter: blur(3px);
        }

        /* ===== MAPS CONTAINER ===== */
        .maps-container {
            width: 100%;
            height: calc(100vh - 70px);
            position: relative;
            background-color: #e8f5e8;
        }

        #map {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 0;
        }

        .map-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .map-control-btn {
            background: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            color: #004d00;
            font-size: 16px;
        }

        .map-control-btn:hover {
            background-color: #004d00;
            color: white;
            transform: scale(1.05);
        }

        /* ===== POPUP STYLES ===== */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            backdrop-filter: blur(5px);
        }

        .popup-overlay.show {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .popup-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            color: #333;
            text-align: center;
            width: 90%;
            max-width: 450px;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .popup-container .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
        }

        .popup-container .close-btn:hover {
            color: #ff6b00;
            transform: scale(1.1);
        }

        .popup-container .popup-logo {
            height: 70px;
            width: 70px;
            margin-bottom: 15px;
            border-radius: 50%;
            object-fit: cover;
        }

        .popup-container h3 {
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 700;
            color: #004d00;
            font-size: 1.8rem;
        }

        .popup-container .input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .popup-container .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 16px;
        }

        .popup-container .form-control {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 18px 18px 18px 50px;
            width: 100%;
            color: #333;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .popup-container .form-control:focus {
            border-color: #004d00;
            box-shadow: 0 0 0 3px rgba(0, 77, 0, 0.1);
            outline: none;
        }

        .popup-container .btn-submit {
            background: linear-gradient(135deg, #004d00 0%, #006600 100%);
            color: white;
            padding: 18px;
            border: none;
            border-radius: 12px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 77, 0, 0.3);
        }

        .popup-container .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 77, 0, 0.4);
        }

        .popup-container .auth-link {
            margin-top: 20px;
            color: #666;
        }

        .popup-container .auth-link a {
            color: #ff8c00;
            text-decoration: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .popup-container .auth-link a:hover {
            color: #ff6b00;
            text-decoration: underline;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .header {
                padding: 10px 15px;
                height: 60px;
            }

            .header .logo {
                font-size: 1.3rem;
            }

            .sidebar {
                width: 250px;
                left: -250px;
            }

            .maps-container {
                height: calc(100vh - 60px);
            }

            .map-controls {
                top: 10px;
                right: 10px;
            }

            .popup-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }

        /* ===== LOADING SPINNER ===== */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #004d00;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* ===== ALERT STYLES ===== */
        .alert {
            margin-bottom: 0;
            border-radius: 10px;
            border: none;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }
    </style>

    @yield('styles')
</head>

<body>
    <div id="app">
        <div class="page-container">
            @auth
                <nav id="sidebar" class="sidebar">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="fas fa-map-marked-alt"></i> Lihat Peta
                    </a>
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}"
                            class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> Data User
                        </a>
                        <a href="{{ route('admin.dropboxes.index') }}"
                            class="{{ request()->routeIs('admin.dropboxes.*') ? 'active' : '' }}">
                            <i class="fas fa-trash-alt"></i> Data Dropbox
                        </a>
                        <a href="{{ route('admin.history.index') }}"
                            class="{{ request()->routeIs('admin.history.*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i> Riwayat Scan User
                        </a>
                        <a href="{{ route('admin.saldo.topup.index') }}"
                            class="{{ request()->routeIs('admin.saldo.*') ? 'active' : '' }}">
                            <i class="fas fa-wallet"></i> Saldo User
                        </a>
                    @endif

                    <a class="logout-btn" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </nav>
            @endauth

            <div class="content-wrapper" id="content-wrapper">
                <header class="header" id="header">
                    <div class="header-left">
                        @auth
                            @if(Auth::user()->isAdmin())
                                <span class="menu-toggle" id="menu-toggle">
                                    <i class="fas fa-bars"></i>
                                </span>
                            @endif
                        @endauth
                        <a href="{{ route('home') }}" class="logo">
                            <img src="{{ asset('images/logo.png') }}" alt="EcoCycle Logo"
                                onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiNmZmM3MDciLz4KPHBhdGggZD0iTTEwIDIwQzEwIDI1LjUgMTQuNSAzMCAyMCAzMEMyNS41IDMwIDMwIDI1LjUgMzAgMjBDMzAgMTQuNSAyNS41IDEwIDIwIDEwQzE0LjUgMTAgMTAgMTQuNSAxMCAyMFoiIGZpbGw9IiMwMDRkMDAiLz4KPHN2Zz4K'">
                            <span>EcoCycle</span>
                        </a>
                    </div>
                    <div class="user-info">
                        @guest
                            <button id="login-popup-btn" class="login-btn">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        @else
                            <span><i class="fas fa-user-circle"></i> Welcome, {{ Auth::user()->name }}</span>
                            @if(Auth::user()->saldo ?? 0 > 0)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-coins"></i> Rp {{ number_format(Auth::user()->saldo ?? 0, 0, ',', '.') }}
                                </span>
                            @endif
                        @endguest
                    </div>
                </header>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <main class="main-content">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @guest
        <div id="login-popup" class="popup-overlay">
            <div class="popup-container">
                <span class="close-btn" data-close-popup>&times;</span>
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="popup-logo"
                    onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAiIGhlaWdodD0iNzAiIHZpZXdCb3g9IjAgMCA3MCA3MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzUiIGN5PSIzNSIgcj0iMzUiIGZpbGw9IiNmZmM3MDciLz4KPHN2Zz4K'">
                <h3>Selamat Datang!</h3>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" required placeholder="Email"
                            value="{{ old('email') }}">
                    </div>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" required placeholder="Password">
                    </div>
                    <button type="submit" class="btn-submit">
                        <span class="btn-text">Login</span>
                        <span class="loading-spinner" style="display: none;"></span>
                    </button>
                    <div class="auth-link">
                        Belum punya akun? <a id="show-register-link">Buat Akun</a>
                    </div>
                </form>
            </div>
        </div>

        <div id="register-popup" class="popup-overlay">
            <div class="popup-container">
                <span class="close-btn" data-close-popup>&times;</span>
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="popup-logo"
                    onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAiIGhlaWdodD0iNzAiIHZpZXdCb3g9IjAgMCA3MCA3MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzUiIGN5PSIzNSIgcj0iMzUiIGZpbGw9IiNmZmM3MDciLz4KPHN2Zz4K'">
                <h3>Buat Akun Baru</h3>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="name" class="form-control" required placeholder="Nama Lengkap"
                            value="{{ old('name') }}">
                    </div>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" required placeholder="Email"
                            value="{{ old('email') }}">
                    </div>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" required
                            placeholder="Password (min. 8 karakter)">
                    </div>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password_confirmation" class="form-control" required
                            placeholder="Konfirmasi Password">
                    </div>
                    <button type="submit" class="btn-submit">
                        <span class="btn-text">Register</span>
                        <span class="loading-spinner" style="display: none;"></span>
                    </button>
                    <div class="auth-link">
                        Sudah punya akun? <a id="show-login-link">Login di sini</a>
                    </div>
                </form>
            </div>
        </div>
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar functionality
            const sidebar = document.getElementById('sidebar');
            const contentWrapper = document.getElementById('content-wrapper');
            const menuToggle = document.getElementById('menu-toggle');

            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                });

                // Close sidebar when clicking outside
                document.addEventListener('click', (e) => {
                    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                });
            }

            // Popup functionality
            const loginPopupBtn = document.getElementById('login-popup-btn');
            const loginPopup = document.getElementById('login-popup');
            const registerPopup = document.getElementById('register-popup');
            const showRegisterLink = document.getElementById('show-register-link');
            const showLoginLink = document.getElementById('show-login-link');
            const closeButtons = document.querySelectorAll('[data-close-popup]');

            function openPopup(popupElement) {
                if (popupElement) {
                    closePopups();
                    popupElement.classList.add('show');
                    if (contentWrapper) contentWrapper.classList.add('blurred');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closePopups() {
                [loginPopup, registerPopup].forEach(popup => {
                    if (popup) popup.classList.remove('show');
                });
                if (contentWrapper) contentWrapper.classList.remove('blurred');
                document.body.style.overflow = '';
            }

            // Event listeners
            if (loginPopupBtn) {
                loginPopupBtn.addEventListener('click', () => openPopup(loginPopup));
            }

            closeButtons.forEach(btn => {
                btn.addEventListener('click', closePopups);
            });

            if (showRegisterLink) {
                showRegisterLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    openPopup(registerPopup);
                });
            }

            if (showLoginLink) {
                showLoginLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    openPopup(loginPopup);
                });
            }

            // Close popup when clicking outside
            [loginPopup, registerPopup].forEach(popup => {
                if (popup) {
                    popup.addEventListener('click', (e) => {
                        if (e.target === popup) closePopups();
                    });
                }
            });

            // Form submission loading states
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function () {
                    const submitBtn = this.querySelector('.btn-submit');
                    const btnText = submitBtn.querySelector('.btn-text');
                    const spinner = submitBtn.querySelector('.loading-spinner');

                    if (btnText && spinner) {
                        btnText.style.display = 'none';
                        spinner.style.display = 'inline-block';
                        submitBtn.disabled = true;
                    }
                });
            });

            // Show popup if there are validation errors
            @if($errors->any())
                @if(old('name'))
                    openPopup(registerPopup);
                @else
                    openPopup(loginPopup);
                @endif
            @endif

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        // Global Maps initialization function
        window.initializeMap = function (containerId = 'map', options = {}) {
            const defaultOptions = {
                center: [-6.200000, 106.816666], // Jakarta coordinates
                zoom: 13,
                markers: []
            };

            const config = { ...defaultOptions, ...options };

            try {
                // Initialize Leaflet map
                const map = L.map(containerId).setView(config.center, config.zoom);

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Add markers if provided
                config.markers.forEach(marker => {
                    const mapMarker = L.marker([marker.lat, marker.lng]).addTo(map);
                    if (marker.popup) {
                        mapMarker.bindPopup(marker.popup);
                    }
                });

                return map;
            } catch (error) {
                console.error('Error initializing map:', error);
                return null;
            }
        };

        // Initialize Google Maps (alternative)
        window.initializeGoogleMap = function (containerId = 'map', options = {}) {
            const defaultOptions = {
                center: { lat: -6.200000, lng: 106.816666 },
                zoom: 13,
                markers: []
            };

            const config = { ...defaultOptions, ...options };

            try {
                if (typeof google === 'undefined') {
                    console.warn('Google Maps API not loaded');
                    return null;
                }

                const map = new google.maps.Map(document.getElementById(containerId), {
                    zoom: config.zoom,
                    center: config.center,
                    styles: [
                        {
                            featureType: "poi",
                            elementType: "labels",
                            stylers: [{ visibility: "off" }]
                        }
                    ]
                });

                // Add markers if provided
                config.markers.forEach(marker => {
                    const mapMarker = new google.maps.Marker({
                        position: { lat: marker.lat, lng: marker.lng },
                        map: map,
                        title: marker.title || ''
                    });

                    if (marker.popup) {
                        const infoWindow = new google.maps.InfoWindow({
                            content: marker.popup
                        });

                        mapMarker.addListener('click', () => {
                            infoWindow.open(map, mapMarker);
                        });
                    }
                });

                return map;
            } catch (error) {
                console.error('Error initializing Google Map:', error);
                return null;
            }
        };
    </script>

    @yield('scripts')
</body>

</html>
