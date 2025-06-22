@extends('layouts.app')

@section('title', 'Edit Dropbox - EcoCycle Admin')

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
        background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
    }

    .page-title {
        color: #1976d2;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 1.8rem;
    }

    .page-title i {
        background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
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
        color: #1976d2;
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
        background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
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

    /* Dropbox Preview Section */
    .dropbox-preview-section {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border-radius: 15px;
        border: 2px dashed #e3f2fd;
    }

    .dropbox-icon-large {
        width: 120px;
        height: 120px;
        border-radius: 20px;
        background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 3rem;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(25,118,210,0.3);
        position: relative;
    }

    .dropbox-icon-large::after {
        content: '';
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        border: 3px solid rgba(25,118,210,0.2);
        border-radius: 20px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.5; }
        100% { transform: scale(1); opacity: 1; }
    }

    .dropbox-preview-info h4 {
        color: #2c3e50;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .dropbox-preview-info p {
        color: #6c757d;
        margin: 0;
        font-size: 16px;
    }

    .dropbox-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 10px;
    }

    .dropbox-status-badge.active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .dropbox-status-badge.maintenance {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
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
        color: #1976d2;
        font-weight: 600;
        font-size: 1.2rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-title i {
        background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
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
        border-color: #1976d2;
        box-shadow: 0 0 0 3px rgba(25,118,210,0.1);
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
        border-color: #1976d2;
        box-shadow: 0 0 0 3px rgba(25,118,210,0.1);
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

    .btn-primary-custom {
        background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(25,118,210,0.3);
    }

    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(25,118,210,0.4);
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

    .btn-danger-custom {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(220,53,69,0.3);
    }

    .btn-danger-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220,53,69,0.4);
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
        position: relative;
    }

    .map-preview.active {
        border-color: #1976d2;
    }

    .map-preview iframe {
        width: 100%;
        height: 100%;
        border: none;
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

        .dropbox-preview-section {
            padding: 20px;
        }

        .dropbox-icon-large {
            width: 80px;
            height: 80px;
            font-size: 2rem;
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
            <i class="fas fa-edit"></i>
            <span>Edit Dropbox</span>
        </h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dropboxes.index') }}">Data Dropbox</a></li>
                <li class="breadcrumb-item active">Edit Dropbox</li>
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
                <i class="fas fa-edit"></i>
                Form Edit Dropbox
            </h5>
        </div>

        <div class="card-body-custom">
            <!-- Dropbox Preview Display -->
            <div class="dropbox-preview-section">
                <div class="dropbox-icon-large">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="dropbox-preview-info">
                    <h4>{{ $dropbox->location_name }}</h4>
                    <p>ID: #{{ str_pad($dropbox->id, 4, '0', STR_PAD_LEFT) }}</p>
                    <span class="dropbox-status-badge {{ $dropbox->status }}">
                        <i class="fas {{ $dropbox->status == 'active' ? 'fa-check-circle' : 'fa-tools' }}"></i>
                        {{ $dropbox->status == 'active' ? 'Aktif' : 'Maintenance' }}
                    </span>
                </div>
            </div>

            <form action="{{ route('admin.dropboxes.update', $dropbox->id) }}" method="POST" id="editDropboxForm">
                @csrf
                @method('PUT')

                <!-- Basic Information Section -->
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-info-circle"></i>
                        Informasi Lokasi
                    </h6>
                    <p class="form-section-subtitle">
                        Perbarui detail lokasi dropbox sesuai kebutuhan.
                    </p>

                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="fas fa-map-marker-alt"></i>
                            Nama Lokasi
                        </label>
                        <input type="text"
                               name="location_name"
                               class="form-control-custom {{ $errors->has('location_name') ? 'is-invalid' : '' }}"
                               value="{{ old('location_name', $dropbox->location_name) }}"
                               required
                               placeholder="Masukkan nama lokasi">
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
                            <option value="active" {{ old('status', $dropbox->status) == 'active' ? 'selected' : '' }}>
                                ✅ Aktif - Siap digunakan
                            </option>
                            <option value="maintenance" {{ old('status', $dropbox->status) == 'maintenance' ? 'selected' : '' }}>
                                🔧 Maintenance - Dalam perbaikan
                            </option>
                        </select>
                        @if($errors->has('status'))
                            <div class="invalid-feedback-custom">{{ $errors->first('status') }}</div>
                        @endif
                        <div class="form-help-text">Ubah status dropbox sesuai kondisi saat ini</div>
                    </div>
                </div>

                <!-- Coordinates Section -->
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-globe"></i>
                        Koordinat Lokasi
                    </h6>
                    <p class="form-section-subtitle">
                        Perbarui posisi geografis dropbox jika diperlukan.
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
                                       value="{{ old('latitude', $dropbox->latitude) }}"
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
                                       value="{{ old('longitude', $dropbox->longitude) }}"
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

                    <!-- Map Preview -->
                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="fas fa-map"></i>
                            Preview Lokasi
                        </label>
                        <div class="map-preview active" id="mapPreview">
                            <iframe src="https://www.google.com/maps/embed/v1/view?key=AIzaSyCzU09IKnlhexDfW_7YMC_lL4oPPqvVTOE&center={{ $dropbox->latitude }},{{ $dropbox->longitude }}&zoom=15&maptype=roadmap" allowfullscreen></iframe>
                        </div>
                        <div class="form-help-text">
                            <a href="https://www.google.com/maps?q={{ $dropbox->latitude }},{{ $dropbox->longitude }}" target="_blank" class="text-primary">
                                <i class="fas fa-external-link-alt"></i> Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="{{ route('admin.dropboxes.index') }}" class="btn-custom btn-secondary-custom">
                        <i class="fas fa-times"></i>
                        Batalkan
                    </a>

                    <button type="button" class="btn-custom btn-danger-custom" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i>
                        Hapus Dropbox
                    </button>

                    <button type="submit" class="btn-custom btn-primary-custom" id="submitBtn">
                        <span class="btn-text">
                            <i class="fas fa-save"></i>
                            Update Dropbox
                        </span>
                        <span class="loading-spinner" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Konfirmasi Hapus Dropbox
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Apakah Anda yakin ingin menghapus dropbox <strong>{{ $dropbox->location_name }}</strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Dropbox akan dihapus dari sistem dan peta.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                <form action="{{ route('admin.dropboxes.destroy', $dropbox->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Ya, Hapus Dropbox
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission with loading state
    const form = document.getElementById('editDropboxForm');
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

// Confirm delete function
function confirmDelete() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
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
@endsection('home') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route
