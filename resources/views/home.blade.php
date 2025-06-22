@extends('layouts.app')

@section('title', 'EcoCycle - Peta Dropbox')

@section('styles')
<style>
    /* Map Container Styles */
    .map-container {
        position: relative;
        width: 100%;
        height: calc(100vh - 70px);
        background: #e8f5e8;
    }

    #map {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 0;
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
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .map-control-btn:hover {
        background-color: #004d00;
        color: white;
        transform: scale(1.05);
    }

    /* Info Panel */
    .info-panel {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        max-width: 300px;
        z-index: 1000;
    }

    .info-panel h3 {
        color: #004d00;
        margin: 0 0 15px 0;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-panel .stats {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .info-panel .stat-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-panel .stat-item:last-child {
        border-bottom: none;
    }

    .info-panel .stat-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 14px;
    }

    .info-panel .stat-value {
        font-weight: 600;
        color: #004d00;
    }

    /* Loading State */
    .map-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        color: #004d00;
        z-index: 999;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #004d00;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .map-container {
            height: calc(100vh - 60px);
        }

        .map-controls {
            top: 10px;
            right: 10px;
            gap: 8px;
        }

        .map-control-btn {
            width: 40px;
            height: 40px;
            font-size: 14px;
        }

        .info-panel {
            bottom: 10px;
            left: 10px;
            right: 10px;
            max-width: none;
            padding: 15px;
        }

        .info-panel h3 {
            font-size: 1.1rem;
        }
    }

    /* Custom Marker Styles */
    .custom-marker {
        background: #004d00;
        color: white;
        padding: 8px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        box-shadow: 0 2px 10px rgba(0,77,0,0.3);
        position: relative;
    }

    .custom-marker::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #004d00;
    }
</style>
@endsection

@section('content')
<div class="map-container">
    <!-- Loading State -->
    <div class="map-loading" id="mapLoading">
        <div class="spinner"></div>
        <p>Memuat peta dropbox...</p>
    </div>

    <!-- Map Display -->
    <div id="map"></div>

    <!-- Map Controls -->
    <div class="map-controls">
        <button class="map-control-btn" title="Lokasi Saya" onclick="getCurrentLocation()">
            <i class="fas fa-location-arrow"></i>
        </button>
        <button class="map-control-btn" title="Refresh Peta" onclick="refreshMap()">
            <i class="fas fa-sync-alt"></i>
        </button>
        <button class="map-control-btn" title="Zoom In" onclick="zoomIn()">
            <i class="fas fa-plus"></i>
        </button>
        <button class="map-control-btn" title="Zoom Out" onclick="zoomOut()">
            <i class="fas fa-minus"></i>
        </button>
        <button class="map-control-btn" title="Fullscreen" onclick="toggleFullscreen()">
            <i class="fas fa-expand"></i>
        </button>
    </div>

    <!-- Info Panel -->
    <div class="info-panel">
        <h3>
            <i class="fas fa-info-circle"></i>
            Informasi Dropbox
        </h3>
        <div class="stats">
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-map-marker-alt"></i>
                    Total Lokasi
                </div>
                <div class="stat-value" id="totalDropboxes">{{ count($activeDropboxes ?? []) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-check-circle"></i>
                    Status Aktif
                </div>
                <div class="stat-value">{{ count($activeDropboxes ?? []) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-user"></i>
                    User
                </div>
                <div class="stat-value">{{ Auth::user()->name ?? 'Guest' }}</div>
            </div>
            @if(Auth::user() && (Auth::user()->saldo ?? 0) > 0)
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-coins"></i>
                    Saldo
                </div>
                <div class="stat-value">Rp {{ number_format(Auth::user()->saldo ?? 0, 0, ',', '.') }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let map;
    let userLocationMarker;
    let dropboxMarkers = [];
    const dropboxes = @json($activeDropboxes ?? []);

    function initMap() {
        // Hide loading
        document.getElementById('mapLoading').style.display = 'none';

        const initialPosition = { lat: 3.5952, lng: 98.6722 }; // Medan coordinates

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 13,
            center: initialPosition,
            disableDefaultUI: true,
            styles: [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                },
                {
                    featureType: "transit",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ]
        });

        // Add dropbox markers
        dropboxes.forEach((dropbox, index) => {
            const position = {
                lat: parseFloat(dropbox.latitude),
                lng: parseFloat(dropbox.longitude)
            };

            const marker = new google.maps.Marker({
                position: position,
                map: map,
                title: dropbox.location_name,
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#004d00" width="32" height="32">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(32, 32),
                    anchor: new google.maps.Point(16, 32)
                },
                animation: google.maps.Animation.DROP
            });

            // Add to markers array
            dropboxMarkers.push(marker);

            // Create info window with enhanced content
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 15px; text-align: center; min-width: 200px;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 10px;">
                            <i class="fas fa-trash-alt" style="color: #004d00; font-size: 18px;"></i>
                            <strong style="color: #004d00; font-size: 16px;">DROPBOX ECOCYCLE</strong>
                        </div>
                        <div style="color: #333; font-weight: 600; margin-bottom: 8px;">${dropbox.location_name}</div>
                        <div style="color: #666; font-size: 13px; margin-bottom: 10px;">
                            <i class="fas fa-map-marker-alt"></i> Lokasi ${index + 1}
                        </div>
                        <div style="display: flex; gap: 10px; justify-content: center;">
                            <button onclick="showDirections(${dropbox.latitude}, ${dropbox.longitude})"
                                    style="background: #004d00; color: white; border: none; padding: 6px 12px; border-radius: 15px; cursor: pointer; font-size: 12px;">
                                <i class="fas fa-directions"></i> Rute
                            </button>
                            <button onclick="shareLocation(${dropbox.latitude}, ${dropbox.longitude}, '${dropbox.location_name}')"
                                    style="background: #ff8c00; color: white; border: none; padding: 6px 12px; border-radius: 15px; cursor: pointer; font-size: 12px;">
                                <i class="fas fa-share-alt"></i> Bagikan
                            </button>
                        </div>
                    </div>
                `
            });

            marker.addListener("click", () => {
                // Close other info windows
                dropboxMarkers.forEach(m => {
                    if (m.infoWindow) {
                        m.infoWindow.close();
                    }
                });

                infoWindow.open({ anchor: marker, map });
                marker.infoWindow = infoWindow;

                // Animate marker
                marker.setAnimation(google.maps.Animation.BOUNCE);
                setTimeout(() => {
                    marker.setAnimation(null);
                }, 2000);
            });
        });

        // Auto-fit bounds if there are dropboxes
        if (dropboxes.length > 0) {
            const bounds = new google.maps.LatLngBounds();
            dropboxes.forEach(dropbox => {
                bounds.extend(new google.maps.LatLng(
                    parseFloat(dropbox.latitude),
                    parseFloat(dropbox.longitude)
                ));
            });
            map.fitBounds(bounds);

            // Ensure minimum zoom level
            google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
                if (map.getZoom() > 15) {
                    map.setZoom(15);
                }
            });
        }
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
                map.setZoom(16);

                // Remove existing user marker
                if (userLocationMarker) {
                    userLocationMarker.setMap(null);
                }

                // Add user location marker
                userLocationMarker = new google.maps.Marker({
                    position: pos,
                    map: map,
                    title: 'Lokasi Anda',
                    icon: {
                        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ff6b00" width="20" height="20">
                                <circle cx="12" cy="12" r="8" stroke="white" stroke-width="2"/>
                            </svg>
                        `),
                        scaledSize: new google.maps.Size(20, 20),
                        anchor: new google.maps.Point(10, 10)
                    },
                    animation: google.maps.Animation.DROP
                });

                // Add info window for user location
                const userInfoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="padding: 10px; text-align: center;">
                            <strong style="color: #ff6b00;">📍 Lokasi Anda</strong><br>
                            <small style="color: #666;">Lat: ${pos.lat.toFixed(6)}<br>Lng: ${pos.lng.toFixed(6)}</small>
                        </div>
                    `
                });

                userLocationMarker.addListener('click', () => {
                    userInfoWindow.open({ anchor: userLocationMarker, map });
                });

            }, function(error) {
                alert('Error getting location: ' + error.message);
            });
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    }

    // Show directions
    function showDirections(lat, lng) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const origin = `${position.coords.latitude},${position.coords.longitude}`;
                const destination = `${lat},${lng}`;
                const url = `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}&travelmode=driving`;
                window.open(url, '_blank');
            }, function() {
                const destination = `${lat},${lng}`;
                const url = `https://www.google.com/maps/dir/?api=1&destination=${destination}&travelmode=driving`;
                window.open(url, '_blank');
            });
        }
    }

    // Share location
    function shareLocation(lat, lng, name) {
        const url = `https://www.google.com/maps?q=${lat},${lng}`;
        const text = `Dropbox EcoCycle: ${name}\nLokasi: ${url}`;

        if (navigator.share) {
            navigator.share({
                title: 'Dropbox EcoCycle',
                text: text,
                url: url
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(text).then(() => {
                alert('Link lokasi berhasil disalin ke clipboard!');
            }).catch(() => {
                prompt('Copy link lokasi:', url);
            });
        }
    }

    // Refresh map
    function refreshMap() {
        location.reload();
    }

    // Zoom controls
    function zoomIn() {
        map.setZoom(map.getZoom() + 1);
    }

    function zoomOut() {
        map.setZoom(map.getZoom() - 1);
    }

    // Toggle fullscreen
    function toggleFullscreen() {
        const mapContainer = document.querySelector('.map-container');
        const fullscreenBtn = document.querySelector('[onclick="toggleFullscreen()"] i');

        if (!document.fullscreenElement) {
            mapContainer.requestFullscreen().then(() => {
                fullscreenBtn.classList.remove('fa-expand');
                fullscreenBtn.classList.add('fa-compress');
            });
        } else {
            document.exitFullscreen().then(() => {
                fullscreenBtn.classList.remove('fa-compress');
                fullscreenBtn.classList.add('fa-expand');
            });
        }
    }

    // Handle fullscreen change
    document.addEventListener('fullscreenchange', function() {
        const fullscreenBtn = document.querySelector('[onclick="toggleFullscreen()"] i');
        if (!document.fullscreenElement) {
            fullscreenBtn.classList.remove('fa-compress');
            fullscreenBtn.classList.add('fa-expand');
        }
    });

    // Initialize map when page loads
    window.addEventListener('load', function() {
        if (typeof google !== 'undefined') {
            initMap();
        }
    });

    // Fallback if Google Maps API fails
    window.addEventListener('error', function(e) {
        if (e.message.includes('Google') || e.message.includes('maps')) {
            document.getElementById('mapLoading').innerHTML = `
                <div style="text-align: center; color: #dc3545;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p>Gagal memuat Google Maps</p>
                    <button onclick="location.reload()" style="background: #004d00; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                        Coba Lagi
                    </button>
                </div>
            `;
        }
    });
</script>

<!-- Load Google Maps API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCzU09IKnlhexDfW_7YMC_lL4oPPqvVTOE&callback=initMap"></script>
@endsection
