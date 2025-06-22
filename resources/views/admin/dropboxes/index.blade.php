@extends('layouts.app')

@section('title', 'Data Dropbox - EcoCycle Admin')

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

    .stat-card.active::before {
        --accent-color: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-card.maintenance::before {
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
    .stat-icon.active { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
    .stat-icon.maintenance { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); }

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
        justify-content: between;
    }

    .card-header-custom h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    .add-dropbox-btn {
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

    .add-dropbox-btn:hover {
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

    /* Dropbox Info */
    .dropbox-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .dropbox-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: linear-gradient(135deg, #004d00 0%, #006600 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .dropbox-details h6 {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
        font-size: 15px;
    }

    .dropbox-details small {
        color: #6c757d;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 2px;
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

    .status-badge.active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .status-badge.maintenance {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    .status-badge i {
        font-size: 10px;
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

    .action-btn.view {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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

    /* Location Display */
    .location-coords {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        color: #666;
        border: 1px solid #e9ecef;
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

        .dropbox-info {
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
            <i class="fas fa-trash-alt"></i>
            <span>Manajemen Dropbox</span>
        </h1>
    </div>

    {{-- Statistics Cards --}}
    <div class="row stats-row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card total">
                <div class="d-flex align-items-center">
                    <div class="stat-icon total">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $totalDropboxes }}</div>
                        <div class="stat-label">Total Dropbox</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card active">
                <div class="d-flex align-items-center">
                    <div class="stat-icon active">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $activeDropboxes }}</div>
                        <div class="stat-label">Dropbox Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card maintenance">
                <div class="d-flex align-items-center">
                    <div class="stat-icon maintenance">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $maintenanceDropboxes }}</div>
                        <div class="stat-label">Maintenance</div>
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
                Daftar Dropbox
            </h5>
            <a href="{{ route('admin.dropboxes.create') }}" class="add-dropbox-btn">
                <i class="fas fa-plus"></i>
                Tambah Dropbox
            </a>
        </div>

        <div class="table-controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari lokasi dropbox..." autocomplete="off">
            </div>
            <select class="filter-select" id="statusFilter">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>

        <div class="card-body-custom">
            <div class="table-responsive">
                <table class="table custom-table" id="dropboxTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lokasi Dropbox</th>
                            <th>Koordinat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dropboxes as $dropbox)
                        <tr data-status="{{ $dropbox->status }}">
                            <td>
                                <span class="text-muted">#{{ str_pad($dropbox->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <div class="dropbox-info">
                                    <div class="dropbox-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="dropbox-details">
                                        <h6>{{ $dropbox->location_name }}</h6>
                                        <small>
                                            <i class="fas fa-clock"></i>
                                            Dibuat {{ $dropbox->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="location-coords">
                                    <div><strong>Lat:</strong> {{ $dropbox->latitude }}</div>
                                    <div><strong>Lng:</strong> {{ $dropbox->longitude }}</div>
                                </div>
                            </td>
                            <td>
                                @if($dropbox->status == 'active')
                                    <span class="status-badge active">
                                        <i class="fas fa-circle"></i> Aktif
                                    </span>
                                @else
                                    <span class="status-badge maintenance">
                                        <i class="fas fa-exclamation-triangle"></i> Maintenance
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" class="action-btn view" title="Lihat di Maps"
                                       onclick="viewOnMap({{ $dropbox->latitude }}, {{ $dropbox->longitude }}, '{{ $dropbox->location_name }}')">
                                        <i class="fas fa-map"></i>
                                    </a>
                                    <a href="{{ route('admin.dropboxes.edit', $dropbox->id) }}" class="action-btn edit" title="Edit Dropbox">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.dropboxes.destroy', $dropbox->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus dropbox {{ $dropbox->location_name }}?')"
                                                title="Hapus Dropbox">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-trash-alt"></i>
                                    <h4>Belum Ada Data Dropbox</h4>
                                    <p>Mulai dengan menambahkan dropbox pertama untuk sistem.</p>
                                    <a href="{{ route('admin.dropboxes.create') }}" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Tambah Dropbox Pertama
                                    </a>
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
    const table = document.getElementById('dropboxTable');
    const rows = table.querySelectorAll('tbody tr');

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterTable();
    });

    // Status filter functionality
    statusFilter.addEventListener('change', function() {
        filterTable();
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();

        rows.forEach(row => {
            if (row.querySelector('.empty-state')) return; // Skip empty state row

            const locationName = row.querySelector('.dropbox-details h6').textContent.toLowerCase();
            const status = row.getAttribute('data-status');

            const matchesSearch = locationName.includes(searchTerm);
            const matchesStatus = selectedStatus === '' || status === selectedStatus;

            if (matchesSearch && matchesStatus) {
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

// View on map function
function viewOnMap(lat, lng, name) {
    const url = `https://www.google.com/maps?q=${lat},${lng}&t=m&z=15`;
    window.open(url, '_blank');
}
</script>
@endsection
