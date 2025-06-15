@extends('layouts.app')

@section('content')
<div id="map"></div>
@endsection

@section('scripts')
<script>
    let map;
    const dropboxes = @json($activeDropboxes ?? []);

    function initMap() {
        const initialPosition = { lat: 3.5952, lng: 98.6722 };

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 13,
            center: initialPosition,
            disableDefaultUI: true,
        });

        dropboxes.forEach(dropbox => {
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(dropbox.latitude), lng: parseFloat(dropbox.longitude) },
                map: map,
                title: dropbox.location_name,
            });

            // === PERUBAHAN DI SINI ===
            // Kita ubah isi content untuk menambahkan tulisan "DROPBOX"
            const infowindow = new google.maps.InfoWindow({
                content: `<div><strong>DROPBOX</strong><br>${dropbox.location_name}</div>`
            });
            // ==========================

            marker.addListener("click", () => {
                infowindow.open({ anchor: marker, map });
            });
        });
    }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCzU09IKnlhexDfW_7YMC_lL4oPPqvVTOE&callback=initMap"></script>
@endsection
