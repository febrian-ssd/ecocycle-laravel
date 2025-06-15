v<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCycle</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <!-- Map Display -->
    <div id="map" style="width: 100%; height: 500px;"></div>

    <!-- Login Button -->
    <button id="loginBtn">Login</button>

    <!-- Modal Login -->
    <div class="login-container" style="display: none;">
        <div class="login-modal">
            <span id="close">&times;</span>
            <h2>Login</h2>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=AlzaSyDyMRRYPTnc_eNJWOROHwpwt6TU&callback=initMap" async defer></script>
    <script>
        function initMap() {
            var mapOptions = {
                center: { lat: -6.1751, lng: 106.8650 }, // Contoh koordinat Jakarta
                zoom: 12
            };
            var map = new google.maps.Map(document.getElementById("map"), mapOptions);

            // Menambahkan marker untuk Dropbox
            var marker = new google.maps.Marker({
                position: { lat: -6.1751, lng: 106.8650 },
                map: map,
                title: 'Dropbox Medan Area'
            });
        }

        // Tampilkan modal login ketika tombol login diklik
        document.getElementById("loginBtn").onclick = function() {
            document.querySelector(".login-container").style.display = "flex";
        }

        // Menutup modal login saat klik 'X'
        document.getElementById("close").onclick = function() {
            document.querySelector(".login-container").style.display = "none";
        }
    </script>
</body>
</html>
