@extends('layouts.app')

@section('title', 'Riwayat Scan User - EcoCycle Admin')

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

    .stat-card.total::before {
        --accent-color: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .stat-card.success::before {
        --accent-color: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-card.failed::before {
        --accent-color: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .stat-card.today::before {
        --accent-color: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
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

    .stat-icon.total { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
    .stat-icon.success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
    .stat-icon.failed { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
    .stat-icon.today { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); }

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

    .export-btn {
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
        font-size: 14px;
    }

    .export-btn:hover {
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
        justify-content: space-between;
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

    .filter-controls {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .filter-select {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 14px;
        background: white;
        min-width: 120px;
    }

    .date-filter {
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

    /* History Info */
    .history-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .history-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        position: relative;
    }

    .history-icon.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .history-icon.failed {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .history-details h6 {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
        font-size: 15px;
    }

    .history-details small {
        color: #6c757d;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 2px;
    }

    /* User Badge */
    .user-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 13px;
        color: #495057;
        border: 1px solid #e9ecef;
    }

    .user-badge i {
        color: #007bff;
    }

    .user-badge.deleted {
        background: #fff3cd;
        color: #856404;
        border-color: #ffeaa7;
    }

    .user-badge.deleted i {
        color: #856404;
    }

    /* Location Badge */
    .location-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #e8f5e8;
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 13px;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .location-badge i {
        color: #28a745;
    }

    .location-badge.deleted {
        background: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }

    .location-badge.deleted i {
        color: #dc3545;
    }

    /* Status Badges */
    .status-badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .status-badge.failed {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .status-badge i {
        font-size: 10px;
    }

    /* Time Display */
    .time-display {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        color: #495057;
        border: 1px solid #e9ecef;
    }

    .time-relative {
        color: #6c757d;
        font-size: 11px;
        margin-top: 3px;
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

        .filter-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            min-width: auto;
        }

        .custom-table {
            font-size: 12px;
        }

        .custom-table thead th,
        .custom-table tbody td {
            padding: 15px 10px;
        }

        .history-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="admin-page-content">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="page-title">
            <i class="fas fa-history"></i>
            <span>Riwayat Scan User</span>
        </h1>
    </div>

    {{-- Statistics Cards --}}
    <div class="row stats-row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card total">
                <div class="d-flex align-items-center">
                    <div class="stat-icon total">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $totalScans }}</div>
                        <div class="stat-label">Total Scan</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card success">
                <div class="d-flex align-items-center">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $successScans }}</div>
                        <div class="stat-label">Berhasil</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card failed">
                <div class="d-flex align-items-center">
                    <div class="stat-icon failed">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $failedScans }}</div>
                        <div class="stat-label">Gagal</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card today">
                <div class="d-flex align-items-center">
                    <div class="stat-icon today">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $todayScans }}</div>
                        <div class="stat-label">Hari Ini</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="main-card">
        <div class="card-header-custom">
            <h5>
                <i class="fas fa-table"></i>
                Data Riwayat Scan
            </h5>
            <a href="#" class="export-btn" onclick="exportData()">
                <i class="fas fa-download"></i>
                Export Excel
            </a>
        </div>

        <div class="table-controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari user, lokasi, atau status..." autocomplete="off">
            </div>
            <div class="filter-controls">
                <select class="filter-select" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="success">Berhasil</option>
                    <option value="failed">Gagal</option>
                </select>
                <input type="date" class="date-filter" id="dateFilter" max="{{ date('Y-m-d') }}">
            </div>
        </div>

        <div class="card-body-custom">
            <div class="table-responsive">
                <table class="table custom-table" id="historyTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Informasi Scan</th>
                            <th>User</th>
                            <th>Lokasi Dropbox</th>
                            <th>Status</th>
                            <th>Waktu Scan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($histories as $history)
                        <tr data-status="{{ $history->status }}" data-date="{{ $history->created_at->format('Y-m-d') }}">
                            <td>
                                <span class="text-muted">#{{ str_pad($history->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <div class="history-info">
                                    <div class="history-icon {{ $history->status }}">
                                        <i class="fas {{ $history->status == 'success' ? 'fa-qrcode' : 'fa-exclamation-triangle' }}"></i>
                                    </div>
                                    <div class="history-details">
                                        <h6>Scan {{ $history->status == 'success' ? 'Berhasil' : 'Gagal' }}</h6>
                                        <small>
                                            <i class="fas fa-clock"></i>
                                            {{ $history->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($history->user)
                                    <div class="user-badge">
                                        <i class="fas fa-user"></i>
                                        {{ $history->user->name }}
                                    </div>
                                @else
                                    <div class="user-badge deleted">
                                        <i class="fas fa-user-slash"></i>
                                        User Telah Dihapus
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($history->dropbox)
                                    <div class="location-badge">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $history->dropbox->location_name }}
                                    </div>
                                @else
                                    <div class="location-badge deleted">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Dropbox Telah Dihapus
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($history->status == 'success')
                                    <span class="status-badge success">
                                        <i class="fas fa-check"></i> Berhasil
                                    </span>
                                @else
                                    <span class="status-badge failed">
                                        <i class="fas fa-times"></i> Gagal
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="time-display">
                                    {{ $history->created_at->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="time-relative">
                                    {{ $history->created_at->diffForHumans() }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-history"></i>
                                    <h4>Belum Ada Riwayat Scan</h4>
                                    <p>Riwayat scan user akan muncul di sini setelah ada aktivitas scanning.</p>
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
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const table = document.getElementById('historyTable');
    const rows = table.querySelectorAll('tbody tr');

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterTable();
    });

    // Status filter functionality
    statusFilter.addEventListener('change', function() {
        filterTable();
    });

    // Date filter functionality
    dateFilter.addEventListener('change', function() {
        filterTable();
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();
        const selectedDate = dateFilter.value;

        rows.forEach(row => {
            if (row.querySelector('.empty-state')) return; // Skip empty state row

            const userName = row.querySelector('.user-badge') ?
                           row.querySelector('.user-badge').textContent.toLowerCase() : '';
            const locationName = row.querySelector('.location-badge') ?
                                row.querySelector('.location-badge').textContent.toLowerCase() : '';
            const status = row.getAttribute('data-status');
            const rowDate = row.getAttribute('data-date');

            const matchesSearch = userName.includes(searchTerm) ||
                                locationName.includes(searchTerm) ||
                                status.includes(searchTerm);
            const matchesStatus = selectedStatus === '' || status === selectedStatus;
            const matchesDate = selectedDate === '' || rowDate === selectedDate;

            if (matchesSearch && matchesStatus && matchesDate) {
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

    // Auto-refresh every 30 seconds for real-time updates
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                // Update stats cards only
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const currentStats = document.querySelectorAll('.stat-number');
                const newStats = newDoc.querySelectorAll('.stat-number');

                currentStats.forEach((stat, index) => {
                    if (newStats[index] && stat.textContent !== newStats[index].textContent) {
                        stat.textContent = newStats[index].textContent;
                        stat.style.animation = 'pulse 0.5s ease';
                    }
                });
            })
            .catch(error => {
                console.log('Auto-refresh error:', error);
            });
        }
    }, 30000); // Refresh every 30 seconds
});

// Export function - Updated untuk menggunakan backend
function exportData() {
    const statusFilter = document.getElementById('statusFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;

    // Build URL dengan parameter filter
    let exportUrl = '/admin/history/export?';
    const params = new URLSearchParams();

    if (statusFilter) {
        params.append('status', statusFilter);
    }
    if (dateFilter) {
        params.append('date', dateFilter);
    }

    exportUrl += params.toString();

    // Trigger download
    window.location.href = exportUrl;
}
</script>
@endsection
