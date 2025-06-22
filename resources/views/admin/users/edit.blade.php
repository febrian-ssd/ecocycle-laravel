@extends('layouts.app')

@section('title', 'Edit User - EcoCycle Admin')

@section('styles')
<style>
    .admin-page-content {
        padding: 30px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
    }

    .page-title {
        color: #004d00;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 1.8rem;
    }

    .page-title i {
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
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
        color: #004d00;
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
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
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

    /* User Profile Section */
    .user-profile-section {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        border: 2px dashed #dee2e6;
    }

    .user-avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 3rem;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(0,77,0,0.3);
        position: relative;
    }

    .user-avatar-large::after {
        content: '';
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        border: 3px solid rgba(0,77,0,0.2);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.5; }
        100% { transform: scale(1); opacity: 1; }
    }

    .user-profile-info h4 {
        color: #2c3e50;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .user-profile-info p {
        color: #6c757d;
        margin: 0;
        font-size: 16px;
    }

    .user-status-badge {
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

    .user-status-badge.admin {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .user-status-badge.user {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    /* Form Sections */
    .form-section {
        margin-bottom: 40px;
        padding: 30px;
        background: #f8f9fa;
        border-radius: 15px;
        border: 1px solid #e9ecef;
    }

    .form-section-title {
        color: #004d00;
        font-weight: 600;
        font-size: 1.2rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-title i {
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
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
        border-color: #004d00;
        box-shadow: 0 0 0 3px rgba(0,77,0,0.1);
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
        border-color: #004d00;
        box-shadow: 0 0 0 3px rgba(0,77,0,0.1);
        outline: none;
    }

    .input-group-custom {
        position: relative;
    }

    .input-group-custom .form-control-custom {
        padding-right: 50px;
    }

    .input-group-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        cursor: pointer;
        z-index: 5;
    }

    .password-toggle {
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .password-toggle:hover {
        color: #004d00;
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
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(0,77,0,0.3);
    }

    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,77,0,0.4);
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

    .alert-success-custom {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border-left: 4px solid #28a745;
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

        .user-profile-section {
            padding: 20px;
        }

        .user-avatar-large {
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

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        .admin-page-content {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }

        .main-card,
        .page-header {
            background: #3c4b5c;
            color: #ecf0f1;
        }

        .form-section {
            background: #34495e;
            border-color: #4a5568;
        }

        .form-control-custom,
        .form-select-custom {
            background: #2c3e50;
            border-color: #4a5568;
            color: #ecf0f1;
        }
    }
</style>
@endsection

@section('content')
<div class="admin-page-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-user-edit"></i>
            <span>Edit User</span>
        </h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Data User</a></li>
                <li class="breadcrumb-item active">Edit User</li>
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
                Form Edit User
            </h5>
        </div>

        <div class="card-body-custom">
            <!-- User Profile Display -->
            <div class="user-profile-section">
                <div class="user-avatar-large">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="user-profile-info">
                    <h4>{{ $user->name }}</h4>
                    <p>{{ $user->email }}</p>
                    <span class="user-status-badge {{ $user->is_admin ? 'admin' : 'user' }}">
                        <i class="fas {{ $user->is_admin ? 'fa-crown' : 'fa-user' }}"></i>
                        {{ $user->is_admin ? 'Administrator' : 'Regular User' }}
                    </span>
                </div>
            </div>

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="editUserForm">
                @csrf
                @method('PUT')

                <!-- Basic Information Section -->
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-user"></i>
                        Informasi Dasar
                    </h6>
                    <p class="form-section-subtitle">
                        Perbarui informasi dasar pengguna seperti nama dan email.
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-user"></i>
                                    Nama Lengkap
                                </label>
                                <input type="text"
                                       name="name"
                                       class="form-control-custom {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                       value="{{ old('name', $user->name) }}"
                                       required
                                       placeholder="Masukkan nama lengkap">
                                @if($errors->has('name'))
                                    <div class="invalid-feedback-custom">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-envelope"></i>
                                    Alamat Email
                                </label>
                                <input type="email"
                                       name="email"
                                       class="form-control-custom {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                       value="{{ old('email', $user->email) }}"
                                       required
                                       placeholder="user@example.com">
                                @if($errors->has('email'))
                                    <div class="invalid-feedback-custom">{{ $errors->first('email') }}</div>
                                @endif
                                <div class="form-help-text">Email harus unik dan valid</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role & Permissions Section -->
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-shield-alt"></i>
                        Role & Hak Akses
                    </h6>
                    <p class="form-section-subtitle">
                        Tentukan level akses dan peran pengguna dalam sistem.
                    </p>

                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="fas fa-user-tag"></i>
                            Role Pengguna
                        </label>
                        <select name="is_admin" class="form-select-custom {{ $errors->has('is_admin') ? 'is-invalid' : '' }}">
                            <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>
                                👤 Regular User - Akses terbatas ke fitur user
                            </option>
                            <option value="1" {{ $user->is_admin ? 'selected' : '' }}>
                                👑 Administrator - Akses penuh ke semua fitur
                            </option>
                        </select>
                        @if($errors->has('is_admin'))
                            <div class="invalid-feedback-custom">{{ $errors->first('is_admin') }}</div>
                        @endif
                        <div class="form-help-text">Administrator memiliki akses ke panel admin dan semua fitur management</div>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-key"></i>
                        Keamanan Password
                    </h6>
                    <p class="form-section-subtitle">
                        Kosongkan field password jika tidak ingin mengubah password saat ini.
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-lock"></i>
                                    Password Baru
                                </label>
                                <div class="input-group-custom">
                                    <input type="password"
                                           name="password"
                                           id="password"
                                           class="form-control-custom {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                           placeholder="Masukkan password baru (opsional)">
                                    <span class="input-group-icon password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-icon"></i>
                                    </span>
                                </div>
                                @if($errors->has('password'))
                                    <div class="invalid-feedback-custom">{{ $errors->first('password') }}</div>
                                @endif
                                <div class="form-help-text">Minimal 8 karakter, kombinasi huruf dan angka</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="fas fa-lock"></i>
                                    Konfirmasi Password
                                </label>
                                <div class="input-group-custom">
                                    <input type="password"
                                           name="password_confirmation"
                                           id="password_confirmation"
                                           class="form-control-custom"
                                           placeholder="Ulangi password baru">
                                    <span class="input-group-icon password-toggle" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                    </span>
                                </div>
                                <div class="form-help-text">Pastikan password sama dengan yang di atas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="{{ route('admin.users.index') }}" class="btn-custom btn-secondary-custom">
                        <i class="fas fa-times"></i>
                        Batalkan
                    </a>

                    @if($user->id !== auth()->id())
                    <button type="button" class="btn-custom btn-danger-custom" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i>
                        Hapus User
                    </button>
                    @endif

                    <button type="submit" class="btn-custom btn-primary-custom" id="submitBtn">
                        <span class="btn-text">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
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
                    Konfirmasi Hapus User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Apakah Anda yakin ingin menghapus user <strong>{{ $user->name }}</strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Semua data yang terkait dengan user ini akan ikut terhapus.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Ya, Hapus User
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
    const form = document.getElementById('editUserForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function() {
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.loading-spinner');

        btnText.style.display = 'none';
        spinner.style.display = 'inline-block';
        submitBtn.classList.add('btn-loading');
    });

    // Password validation
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');

    function validatePassword() {
        if (passwordField.value && confirmPasswordField.value) {
            if (passwordField.value !== confirmPasswordField.value) {
                confirmPasswordField.setCustomValidity('Password tidak cocok');
            } else {
                confirmPasswordField.setCustomValidity('');
            }
        }
    }

    passwordField.addEventListener('input', validatePassword);
    confirmPasswordField.addEventListener('input', validatePassword);
});

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
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
@endsection
