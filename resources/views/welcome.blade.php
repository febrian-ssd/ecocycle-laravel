<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCycle - Sistem Pengelolaan Sampah Pintar</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #004d00 0%, #006600 100%);
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.6rem;
            font-weight: 700;
        }

        .logo i {
            background: rgba(255,255,255,0.2);
            padding: 10px;
            border-radius: 50%;
            font-size: 1.2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            font-weight: 600;
        }

        .welcome-text {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .welcome-text i {
            color: #ffc107;
        }

        /* Logout Button Styles */
        .logout-btn {
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(255,140,0,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255,140,0,0.4);
            background: linear-gradient(135deg, #ff6b00 0%, #ff5500 100%);
        }

        .logout-btn:active {
            transform: translateY(0);
        }

        /* Login Button Styles */
        .login-btn {
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(255,140,0,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255,140,0,0.4);
        }

        /* Map Container */
        .map-container {
            position: relative;
            width: 100%;
            height: calc(100vh - 70px);
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Map Controls */
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        /* Modal Styles */
        .login-container {
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

        .login-container.show {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .login-modal {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .login-modal #close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
        }

        .login-modal #close:hover {
            color: #ff6b00;
            transform: scale(1.1);
        }

        .login-modal h2 {
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 700;
            color: #004d00;
            font-size: 1.8rem;
            text-align: center;
        }

        .login-modal form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .login-modal input {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .login-modal input:focus {
            border-color: #004d00;
            box-shadow: 0 0 0 3px rgba(0,77,0,0.1);
            outline: none;
        }

        .login-modal button[type="submit"] {
            background: linear-gradient(135deg, #004d00 0%, #006600 100%);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,77,0,0.3);
        }

        .login-modal button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,77,0,0.4);
        }

        /* User Badge */
        .user-badge {
            background: rgba(255,255,255,0.1);
            padding: 8px 15px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Floating Logout Button */
        .floating-logout {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1500;
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255,140,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .floating-logout:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 6px 30px rgba(255,140,0,0.6);
            background: linear-gradient(135deg, #ff6b00 0%, #ff5500 100%);
        }

        .floating-logout:active {
            transform: translateY(-1px) scale(1.05);
        }

        .floating-logout i {
            font-size: 20px;
        }

        /* Tooltip for floating button */
        .floating-logout::before {
            content: 'Logout';
            position: absolute;
            right: 70px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .floating-logout::after {
            content: '';
            position: absolute;
            right: 60px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 5px solid transparent;
            border-bottom: 5px solid transparent;
            border-left: 5px solid rgba(0,0,0,0.8);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .floating-logout:hover::before,
        .floating-logout:hover::after {
            opacity: 1;
        }
        @media (max-width: 768px) {
            .header {
                padding: 10px 15px;
                flex-direction: column;
                gap: 10px;
            }

            .user-info {
                gap: 10px;
            }

            .welcome-text {
                font-size: 12px;
            }

            .logout-btn, .login-btn {
                padding: 8px 16px;
                font-size: 12px;
            }

            .map-container {
                height: calc(100vh - 100px);
            }

            .map-controls {
                top: 10px;
                right: 10px;
            }

            .login-modal {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <i class="fas fa-recycle"></i>
            <span>EcoCycle</span>
        </div>

        <div class="user-info">
            @auth
                <div class="welcome-text">
                    <i class="fas fa-user-circle"></i>
                    <span>Welcome, {{ Auth::user()->name }}</span>
                </div>

                @if(Auth::user()->saldo ?? 0 > 0)
                    <div class="user-badge">
                        <i class="fas fa-coins"></i>
                        <span>Rp {{ number_format(Auth::user()->saldo ?? 0, 0, ',', '.') }}</span>
                    </div>
                @endif
            @else
                <!-- Login Button -->
                <button id="loginBtn" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            @endauth
        </div>
    </header>

    <!-- Map Container -->
    <div class="map-container">
        <!-- Map Display -->
        <div id="map"></div>

        <!-- Map Controls -->
        <div class="map-controls">
            <button class="map-control-btn" title="Lokasi Saya" onclick="getCurrentLocation()">
                <i class="fas fa-location-arrow"></i>
            </button>
            <button class="map-control-btn" title="Refresh" onclick="refreshMap()">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button class="map-control-btn" title="Fullscreen" onclick="toggleFullscreen()">
                <i class="fas fa-expand"></i>
            </button>
        </div>
    </div>

    <!-- Floating Logout Button for Authenticated Users -->
    @auth
    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="floating-logout" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </form>
    @endauth

    <!-- Modal Login -->
    @guest
    <div class="login-container" id="loginContainer">
        <div class="login-modal">
            <span id="close">&times;</span>
            <h2>
                <i class="fas fa-recycle" style="color: #004d00;"></i>
                Login EcoCycle
            </h2>

            @if(session('error'))
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <input type="email" name="email" placeholder="Email Address" required value="{{ old('email') }}">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px; color: #666;">
                Belum punya akun? <a href="{{ route('register') }}" style="color: #ff8c00; text-decoration: none; font-weight: 600;">Daftar di sini</a>
            </div>
        </div>
    </div>
    @endguest

    <!-- Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCzU09IKnlhexDfW_7YMC_lL4oPPqvVTOE&callback=initMap" async defer></script>

    <script>
        let map;
        let userLocationMarker;

        function initMap() {
            var mapOptions = {
                center: { lat: 3.5952, lng: 98.6722 }, // Koordinat Medan
                zoom: 13,
                styles: [
                    {
                        featureType: "poi",
                        elementType: "labels",
                        stylers: [{ visibility: "off" }]
                    }
                ]
            };

            map = new google.maps.Map(document.getElementById("map"), mapOptions);

            // Menambahkan marker untuk Dropbox
            var marker = new google.maps.Marker({
                position: { lat: 3.5952, lng: 98.6722 },
                map: map,
                title: 'Dropbox Medan Area',
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#004d00" width="32" height="32"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>'),
                    scaledSize: new google.maps.Size(32, 32)
                }
            });

            // Info window untuk marker
            const infowindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px; text-align: center;">
                        <strong style="color: #004d00;">🗂️ DROPBOX</strong><br>
                        <span style="color: #666;">Medan Area</span><br>
                        <small style="color: #999;">Klik untuk detail lokasi</small>
                    </div>
                `
            });

            marker.addListener("click", () => {
                infowindow.open({ anchor: marker, map });
            });
        }

        // Get current location
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    map.setCenter(pos);
                    map.setZoom(15);

                    // Add user location marker
                    if (userLocationMarker) {
                        userLocationMarker.setMap(null);
                    }

                    userLocationMarker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        title: 'Lokasi Anda',
                        icon: {
                            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ff6b00" width="24" height="24"><circle cx="12" cy="12" r="8"/></svg>'),
                            scaledSize: new google.maps.Size(24, 24)
                        }
                    });
                });
            }
        }

        // Refresh map
        function refreshMap() {
            window.location.reload();
        }

        // Toggle fullscreen
        function toggleFullscreen() {
            const mapContainer = document.querySelector('.map-container');
            if (!document.fullscreenElement) {
                mapContainer.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        // Modal functionality
        @guest
        document.getElementById("loginBtn").onclick = function() {
            document.getElementById("loginContainer").classList.add("show");
        }

        document.getElementById("close").onclick = function() {
            document.getElementById("loginContainer").classList.remove("show");
        }

        // Close modal when clicking outside
        document.getElementById("loginContainer").onclick = function(e) {
            if (e.target === this) {
                this.classList.remove("show");
            }
        }
        @endguest

        // Auto-show login modal if there are validation errors
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById("loginContainer").classList.add("show");
            });
        @endif
    </script>
</body>
</html>
