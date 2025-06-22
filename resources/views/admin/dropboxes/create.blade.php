@extends('layouts.app')

@section('title', 'Tambah Dropbox Baru - EcoCycle Admin')

@section('styles')
<style>
    .admin-page-content {
        padding: 30px;
        background: linear-gradient(135deg, #f1f8ff 0%, #e6f3ff 100%);
        min-height: calc(100vh - 70px);
    }

    .page-header {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .page-title {
        color: #28a745;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 1.8rem;
    }

    .page-title i {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 15px;
        border-radius: 15px;
        font-size: 1.2rem;
    }

    .breadcrumb-custom {
        background: none;
        padding: 0;
        margin: 10px 0 0 0;
        font-size: 14px;
    }

    .breadcrumb-custom .breadcrumb-item {
        color: #6c757d;
    }

    .breadcrumb-custom .breadcrumb-item.active {
        color: #28a745;
        font-weight: 600;
    }

    .breadcrumb-custom a {
        color: #007bff;
        text-decoration: none;
    }

    .breadcrumb-custom a:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    /* Main Form Card */
    .main-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 25px 30px;
        border: none;
    }

    .card-header-custom h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body-custom {
        padding: 40px;
    }

    /* Form Sections */
    .form-section {
        margin-bottom: 40px;
        padding: 30px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border-radius: 15px;
        border: 1px solid #e3f2fd;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .form-section-title {
        color: #28a745;
        font-weight: 600;
        font-size: 1.2rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-title i {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 10px;
        border-radius: 8px;
        font-size: 14px;
    }

    .form-section-subtitle {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 25px;
        font-style: italic;
    }

    /* Form Controls */
    .form-group-custom {
        margin-bottom: 25px;
        position: relative;
    }

    .form-label-custom {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .form-label-custom i {
        color: #6c757d;
        width: 16px;
    }

    .form-control-custom {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px 20px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control-custom:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40,167,69,0.1);
        outline: none;
        background: white;
    }

    .form-control-custom.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220,53,69,0.1);
    }

    .form-select-custom {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px 20px;
        font-size: 16px;
        background: white;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .form-select-custom:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40,167,69,0.1);
        outline: none;
    }

    /* Error Messages */
    .invalid-feedback-custom {
        display: block;
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
        font-weight: 500;
    }

    .form-help-text {
        color: #6c757d;
        font-size: 12px;
        margin-top: 5px;
        font-style: italic;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        align-items: center;
        padding-top: 30px;
        border-top: 2px solid #f1f1f1;
        margin-top: 40px;
    }

    .btn-custom {
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
    }

    .btn-custom::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-custom:hover::before {
        left: 100%;
    }

    .btn-success-custom {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(40,167,69,0.3);
    }

    .btn-success-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40,167,69,0.4);
        color: white;
    }

    .btn-secondary-custom {
        background: #6c757d;
        color: white;
        box-shadow: 0 5px 15px rgba(108,117,125,0.3);
    }

    .btn-secondary-custom:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108,117,125,0.4);
        color: white;
    }

    /* Loading State */
    .btn-loading {
        pointer-events: none;
        opacity: 0.6;
    }

    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Alert Improvements */
    .alert-custom {
        border: none;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        font-weight: 500;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .alert-danger-custom {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    /* Map Preview */
    .map-preview {
        height: 300px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e9ecef;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-style: italic;
        position: relative;
    }

    .map-preview.active {
        border-color: #28a745;
    }

    .map-preview iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .get-coordinates-btn {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .get-coordinates-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,123,255,0.3);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .admin-page-content {
            padding: 20px 15px;
        }

        .page-header,
        .card-body-custom {
            padding: 20px;
        }

        .form-section {
            padding: 20px;
        }

        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-custom {
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="admin-page-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i>
            <span>Tambah Dropbox Baru</span>
        </h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.dropboxes.index') }}">Data Dropbox</a></li>
                <li class="breadcrumb-item active">Tambah Dropbox</li>
            </ol>
        </nav>
    </div>

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert alert-danger-custom alert-custom">
            <h6><i class="fas fa-exclamation-triangle"></i> Terdapat kesalahan pada form:</h6>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Form Card -->
    <div class="main-card">
        <div class="card-header-custom">
            <h5>
                <i class="fas fa-map-marker-alt"></i>
                Form Tambah Lokasi Dropbox
            </h5>
        </div>

        <div class="card-body-custom">
            <form action="{{ route('admin.dropboxes.store') }}" method="POST" id="dropboxForm">
                @csrf

                <!-- Basic Information Section -->
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-info-circle"></i>
                        Informasi Lokasi
                    </h6>
                    <p class="form-section-subtitle">
                        Masukkan detail lokasi dropbox yang akan ditambahkan ke sistem.
                    </p>

                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="fas fa-map-marker-alt"></i>
                            Nama Lokasi
                        </label>
                        <input type="text"
                               name="location_name"
                               class="form-control-custom {{ $errors->has('location_name') ? 'is-invalid' : '' }}"
                               value="{{ old('location_name') }}"
                               required
                               placeholder="Contoh: Depan Pintu Masuk Merdeka Walk">
                        @if($errors->has('location_name'))
                            <div class="invalid-feedback-custom">{{ $errors->first('location_name') }}</div>
                        @endif
                        <div class="form-help-text">Berikan nama lokasi yang jelas dan mudah dikenali</div>
                    </div>

                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="fas fa-cog"></i>
                            Status Dropbox
                        </label>
                        <select name="status" class="form-select-custom {{ $errors->has('status') ? 'is-invalid' : '' }}">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                ✅ Aktif - Siap digunakan
                            </option>
                            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>
                                🔧 Maintenance - Dalam perbaikan
                            </option>
                        </select>
                        @if($errors->has('status'))
                            <div class="invalid-feedback-custom">{{ $errors->first('status') }}</div>
                        @endif
                        <div class="form-help-text">Pilih status awal dropbox setelah ditambahkan</div>
                    </div>
                </div>

                <!-- Coordinates Section -->
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-globe"></i>
                        Koordinat Lokasi
                    </h6>
                    <p class="form-section-subtitle">
                        Tentukan posisi geografis dropbox dengan koordinat latitude dan longitude.
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-compass"></i>
                                    Latitude
                                </label>
                                <input type="text"
                                       name="latitude"
                                       id="latitude"
                                       class="form-control-custom {{ $errors->has('latitude') ? 'is-invalid' : '' }}"
                                       value="{{ old('latitude') }}"
                                       required
                                       placeholder="Contoh: 3.59021"
                                       onchange="updateMapPreview()">
                                @if($errors->has('latitude'))
                                    <div class="invalid-feedback-custom">{{ $errors->first('latitude') }}</div>
                                @endif
                                <div class="form-help-text">Koordinat garis lintang (-90 hingga 90)</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-compass"></i>
                                    Longitude
                                </label>
                                <input type="text"
                                       name="longitude"
                                       id="longitude"
                                       class="form-control-custom {{ $errors->has('longitude') ? 'is-invalid' : '' }}"
                                       value="{{ old('longitude') }}"
                                       required
                                       placeholder="Contoh: 98.67481"
                                       onchange="updateMapPreview()">
                                @if($errors->has('longitude'))
                                    <div class="invalid-feedback-custom">{{ $errors->first('longitude') }}</div>
                                @endif
                                <div class="form-help-text">Koordinat garis bujur (-180 hingga 180)</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <button type="button" class="get-coordinates-btn" onclick="getCurrentLocation()">
                            <i class="fas fa-location-arrow"></i>
                            Gunakan Lokasi Saat Ini
                        </button>
                    </div>

                    <!-- Map Preview -->
                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="fas fa-map"></i>
                            Preview Lokasi
                        </label>
                        <div class="map-preview" id="mapPreview">
                            <div>
                                <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 10px; opacity: 0.3;"></i><br>
                                Masukkan koordinat untuk melihat preview lokasi
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="{{ route('admin.dropboxes.index') }}" class="btn-custom btn-secondary-custom">
                        <i class="fas fa-times"></i>
                        Batalkan
                    </a>

                    <button type="submit" class="btn-custom btn-success-custom" id="submitBtn">
                        <span class="btn-text">
                            <i class="fas fa-save"></i>
                            Simpan Dropbox
                        </span>
                        <span class="loading-spinner" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission with loading state
    const form = document.getElementById('dropboxForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function() {
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.loading-spinner');

        btnText.style.display = 'none';
        spinner.style.display = 'inline-block';
        submitBtn.classList.add('btn-loading');
    });

    // Coordinate validation
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    latInput.addEventListener('input', validateCoordinates);
    lngInput.addEventListener('input', validateCoordinates);

    function validateCoordinates() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (lat < -90 || lat > 90) {
            latInput.setCustomValidity('Latitude harus antara -90 dan 90');
        } else {
            latInput.setCustomValidity('');
        }

        if (lng < -180 || lng > 180) {
            lngInput.setCustomValidity('Longitude harus antara -180 dan 180');
        } else {
            lngInput.setCustomValidity('');
        }
    }
});

// Update map preview
function updateMapPreview() {
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    const mapPreview = document.getElementById('mapPreview');

    if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
        const embedUrl = `https://www.google.com/maps/embed/v1/view?key=AIzaSyCzU09IKnlhexDfW_7YMC_lL4oPPqvVTOE&center=${lat},${lng}&zoom=15&maptype=roadmap`;

        mapPreview.innerHTML = `<iframe src="${embedUrl}" allowfullscreen></iframe>`;
        mapPreview.classList.add('active');
    }
}

// Get current location
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
            updateMapPreview();

            // Show success message
            const btn = document.querySelector('.get-coordinates-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Lokasi Berhasil Diambil';
            btn.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = 'linear-gradient(135deg, #007bff 0%, #0056b3 100%)';
            }, 2000);

        }, function(error) {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        if (window.bootstrap && bootstrap.Alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 8000);
</script>
@endsection
