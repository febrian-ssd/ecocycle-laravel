@extends('app')

@section('title', 'Data User - EcoCycle Admin')

@section('styles')
<style>
    .admin-page-content {
        padding: 30px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 70px);
    }

    .page-title {
        color: #004d00;
        font-weight: 700;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-title i {
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
        color: white;
        padding: 15px;
        border-radius: 15px;
        font-size: 1.2rem;
    }

    /* Stats Cards */
    .stats-row {
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: none;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--accent-color);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .stat-card.admin::before {
        --accent-color: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .stat-card.online::before {
        --accent-color: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-card.offline::before {
        --accent-color: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 15px;
    }

    .stat-icon.admin { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
    .stat-icon.online { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
    .stat-icon.offline { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
        line-height: 1;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
    }

    /* Main Content Card */
    .main-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: none;
        overflow: hidden;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
        color: white;
        padding: 25px 30px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-header-custom h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .add-user-btn {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 2px solid rgba(255,255,255,0.3);
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .add-user-btn:hover {
        background: white;
        color: #004d00;
        transform: translateY(-2px);
    }

    .card-body-custom {
        padding: 0;
    }

    /* Search and Filter */
    .table-controls {
        padding: 25px 30px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 300px;
    }

    .search-box input {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 20px 12px 45px;
        font-size: 14px;
        transition: all 0.3s ease;
        width: 100%;
    }

    .search-box input:focus {
        border-color: #004d00;
        box-shadow: 0 0 0 3px rgba(0,77,0,0.1);
        outline: none;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .filter-select {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 14px;
        background: white;
        min-width: 150px;
    }

    /* Custom Table */
    .custom-table {
        margin: 0;
    }

    .custom-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 20px 25px;
        border: none;
        position: sticky;
        top: 0;
    }

    .custom-table tbody td {
        padding: 20px 25px;
        border: none;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
        font-size: 14px;
    }

    .custom-table tbody tr {
        transition: all 0.3s ease;
    }

    .custom-table tbody tr:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }

    /* User Avatar */
    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
        margin-right: 15px;
    }

    .user-info {
        display: flex;
        align-items: center;
    }

    .user-details h6 {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
        font-size: 15px;
    }

    .user-details small {
        color: #6c757d;
        font-size: 13px;
    }

    /* Status Badges */
    .status-badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.admin {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .status-badge.user {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    .status-badge.online {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .status-badge.offline {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .action-btn {
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .action-btn.edit {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }

    .action-btn.delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        color: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .empty-state h4 {
        margin-bottom: 10px;
        color: #495057;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .admin-page-content {
            padding: 20px 15px;
        }

        .table-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            min-width: auto;
        }

        .action-buttons {
            flex-direction: column;
        }

        .custom-table {
            font-size: 12px;
        }

        .custom-table thead th,
        .custom-table tbody td {
            padding: 15px 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="admin-page-content">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="page-title">
            <i class="fas fa-users"></i>
            <span>Manajemen User</span>
        </h1>
    </div>

    {{-- Statistics Cards --}}
    <div class="row stats-row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card admin">
                <div class="d-flex align-items-center">
                    <div class="stat-icon admin">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $adminCount }}</div>
                        <div class="stat-label">Total Admin</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card online">
                <div class="d-flex align-items-center">
                    <div class="stat-icon online">
                        <i class="fas fa-signal"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $onlineUsers }}</div>
                        <div class="stat-label">User Online</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card offline">
                <div class="d-flex align-items-center">
                    <div class="stat-icon offline">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $offlineUsers }}</div>
                        <div class="stat-label">User Offline</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="main-card">
        <div class="card-header-custom">
            <h5>
                <i class="fas fa-list"></i>
                Daftar Pengguna
            </h5>
        </div>

        <div class="table-controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari nama, email, atau role..." autocomplete="off">
            </div>
            <select class="filter-select" id="roleFilter">
                <option value="">Semua Role</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
        </div>

        <div class="card-body-custom">
            <div class="table-responsive">
                <table class="table custom-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pengguna</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terakhir Login</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr data-role="{{ $user->is_admin ? 'admin' : 'user' }}">
                            <td>
                                <span class="text-muted">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="user-details">
                                        <h6>{{ $user->name }}</h6>
                                        <small>{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->is_admin)
                                    <span class="status-badge admin">
                                        <i class="fas fa-crown"></i> Admin
                                    </span>
                                @else
                                    <span class="status-badge user">
                                        <i class="fas fa-user"></i> User
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->last_login_at && $user->last_login_at->diffInMinutes(now()) < 5)
                                    <span class="status-badge online">
                                        <i class="fas fa-circle"></i> Online
                                    </span>
                                @else
                                    <span class="status-badge offline">
                                        <i class="fas fa-clock"></i> Offline
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <span class="text-muted">{{ $user->last_login_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-muted">Belum pernah login</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn edit" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}? Data yang terkait akan ikut terhapus.')"
                                                title="Hapus User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h4>Belum Ada Data User</h4>
                                    <p>Mulai dengan menambahkan user pertama untuk sistem.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const table = document.getElementById('usersTable');
    const rows = table.querySelectorAll('tbody tr');

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterTable();
    });

    // Role filter functionality
    roleFilter.addEventListener('change', function() {
        filterTable();
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value.toLowerCase();

        rows.forEach(row => {
            if (row.querySelector('.empty-state')) return; // Skip empty state row

            const name = row.querySelector('.user-details h6').textContent.toLowerCase();
            const email = row.querySelector('.user-details small').textContent.toLowerCase();
            const role = row.getAttribute('data-role');

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesRole = selectedRole === '' || role === selectedRole;

            if (matchesSearch && matchesRole) {
                row.style.display = '';
                // Add highlight animation
                row.style.animation = 'none';
                setTimeout(() => {
                    row.style.animation = 'fadeIn 0.3s ease';
                }, 10);
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);

    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            if (window.bootstrap && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
});
</script>
@endsection
