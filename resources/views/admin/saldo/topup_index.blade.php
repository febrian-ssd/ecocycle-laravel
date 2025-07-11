@extends('layouts.app')

@section('title', 'Manajemen Top Up Saldo - EcoCycle Admin')

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

    .stat-card.pending::before {
        --accent-color: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }

    .stat-card.approved::before {
        --accent-color: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-card.total::before {
        --accent-color: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .stat-card.amount::before {
        --accent-color: linear-gradient(135deg, #6f42c1 0%, #9c27b0 100%);
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

    .stat-icon.pending { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); }
    .stat-icon.approved { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
    .stat-icon.total { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
    .stat-icon.amount { background: linear-gradient(135deg, #6f42c1 0%, #9c27b0 100%); }

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

    .manual-topup-btn {
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

    .manual-topup-btn:hover {
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
        padding: 15px 25px;
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

    /* User Info */
    .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

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
        display: block;
        margin-top: 2px;
    }

    .admin-note {
        font-size: 12px;
        color: #007bff;
        font-style: italic;
    }

    /* Amount Display */
    .amount-display {
        font-family: 'Courier New', monospace;
        font-size: 16px;
        font-weight: 700;
        color: #28a745;
        background: #e8f5e8;
        padding: 8px 15px;
        border-radius: 10px;
        border: 1px solid #c3e6cb;
    }

    /* Status Badges */
    .status-badge, .type-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge.pending {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    .status-badge.approved {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .status-badge.rejected {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .type-badge.manual {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }
    .type-badge.request {
        background: linear-gradient(135deg, #6f42c1 0%, #9c27b0 100%);
        color: white;
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
        cursor: pointer;
    }

    .action-btn.approve {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .action-btn.reject {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .action-btn.view {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="admin-page-content">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="page-title">
            <i class="fas fa-wallet"></i>
            <span>Manajemen Top Up Saldo</span>
        </h1>
    </div>

    {{-- Statistics Cards --}}
    <div class="row stats-row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card pending">
                <div class="d-flex align-items-center">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $pendingRequests }}</div>
                        <div class="stat-label">Menunggu Konfirmasi</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card approved">
                <div class="d-flex align-items-center">
                    <div class="stat-icon approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $approvedRequests }}</div>
                        <div class="stat-label">Disetujui</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card total">
                <div class="d-flex align-items-center">
                    <div class="stat-icon total">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">{{ $totalRequests }}</div>
                        <div class="stat-label">Total Transaksi</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card amount">
                <div class="d-flex align-items-center">
                    <div class="stat-icon amount">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">Rp {{ number_format($totalAmount / 1000, 0) }}K</div>
                        <div class="stat-label">Total Nominal Disetujui</div>
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
                Laporan Top Up Saldo
            </h5>
            <a href="#" class="manual-topup-btn" data-bs-toggle="modal" data-bs-target="#manualTopupModal">
                <i class="fas fa-plus"></i>
                Top Up Manual
            </a>
        </div>

        <div class="table-controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari nama user atau nominal..." autocomplete="off">
            </div>
            <div class="filter-controls">
                <select class="filter-select" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
                <input type="date" class="filter-select" id="dateFilter" max="{{ date('Y-m-d') }}">
            </div>
        </div>

        <div class="card-body-custom">
            <div class="table-responsive">
                <table class="table custom-table" id="topupTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Nominal</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topupRequests as $request)
                        <tr data-status="{{ $request->status }}" data-date="{{ $request->created_at->format('Y-m-d') }}">
                            <td>
                                <span class="text-muted">#{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                    </div>
                                    <div class="user-details">
                                        <h6>{{ $request->user->name }}</h6>
                                        <small>{{ $request->user->email }}</small>
                                        @if($request->type == 'manual' && $request->approvedBy)
                                            <small class="admin-note">oleh: {{ $request->approvedBy->name }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="amount-display">
                                    Rp {{ number_format($request->amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td>
                                @if($request->type == 'manual')
                                    <span class="type-badge manual">
                                        <i class="fas fa-user-shield"></i> Manual
                                    </span>
                                @else
                                    <span class="type-badge request">
                                        <i class="fas fa-mobile-alt"></i> Request
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($request->status == 'pending')
                                    <span class="status-badge pending">
                                        <i class="fas fa-clock"></i> Menunggu
                                    </span>
                                @elseif($request->status == 'approved')
                                    <span class="status-badge approved">
                                        <i class="fas fa-check"></i> Disetujui
                                    </span>
                                @else
                                    <span class="status-badge rejected">
                                        <i class="fas fa-times"></i> Ditolak
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="time-display">
                                    {{ $request->created_at->format('d M Y, H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if($request->status == 'pending')
                                        <button class="action-btn approve"
                                                onclick="confirmAction('approve', {{ $request->id }}, '{{ $request->user->name }}', {{ $request->amount }})"
                                                title="Setujui Top Up">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn reject"
                                                onclick="confirmAction('reject', {{ $request->id }}, '{{ $request->user->name }}', {{ $request->amount }})"
                                                title="Tolak Top Up">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @else
                                        <button class="action-btn view" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-wallet"></i>
                                    <h4>Belum Ada Permintaan Top Up</h4>
                                    <p>Permintaan top up saldo dari user akan muncul di sini.</p>
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

{{-- MODAL KONFIRMASI --}}
<div class="modal fade" id="confirmationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="confirmationModalHeader">
                <h5 class="modal-title" id="confirmationModalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="confirmationForm" method="POST" style="display: inline;">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p class="mb-3">Apakah Anda yakin ingin <strong id="actionText"></strong> top up untuk:</p>
                    <div class="text-center mb-3"><strong id="userName"></strong></div>
                    <div class="confirm-amount">
                        <p class="amount" id="amountText"></p>
                        <small class="text-muted">Nominal Top Up</small>
                    </div>
                    <div id="reasonGroup" class="mb-3" style="display: none;">
                        <label for="rejectReason" class="form-label">Alasan Penolakan:</label>
                        <textarea class="form-control" id="rejectReason" name="reason" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" id="confirmButton"></button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL TOP UP MANUAL --}}
<div class="modal fade" id="manualTopupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Top Up Manual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.saldo.topup.manual') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Pilih User:</label>
                        <select class="form-select" name="user_id" required>
                            <option value="" disabled selected>-- Pilih User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Nominal Top Up:</label>
                        <input type="number" class="form-control" name="amount" min="10000" step="1000" required placeholder="Masukkan nominal (min. 10.000)">
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Catatan (Opsional):</label>
                        <textarea class="form-control" name="note" rows="3" placeholder="Catatan untuk top up manual..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-plus"></i> Top Up Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));

    function confirmAction(action, requestId, userName, amount) {
        const modalHeader = document.getElementById('confirmationModalHeader');
        const modalTitle = document.getElementById('confirmationModalTitle');
        const actionText = document.getElementById('actionText');
        const amountText = document.getElementById('amountText');
        const reasonGroup = document.getElementById('reasonGroup');
        const confirmButton = document.getElementById('confirmButton');
        const form = document.getElementById('confirmationForm');

        document.getElementById('userName').textContent = userName;
        amountText.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);

        // --- KODE YANG DIPERBAIKI ---
        if (action === 'approve') {
            let url = "{{ route('admin.saldo.topup.approve', ['topupRequest' => 'PLACEHOLDER']) }}";
            form.action = url.replace('PLACEHOLDER', requestId);
        } else {
            let url = "{{ route('admin.saldo.topup.reject', ['topupRequest' => 'PLACEHOLDER']) }}";
            form.action = url.replace('PLACEHOLDER', requestId);
        }

        if (action === 'approve') {
            modalHeader.className = 'modal-header';
            modalHeader.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
            modalHeader.style.color = 'white';
            modalTitle.innerHTML = '<i class="fas fa-check-circle"></i> Konfirmasi Persetujuan';
            actionText.textContent = 'menyetujui';
            reasonGroup.style.display = 'none';
            document.getElementById('rejectReason').required = false;
            confirmButton.className = 'btn btn-success';
            confirmButton.innerHTML = '<i class="fas fa-check"></i> Ya, Setujui';
        } else { // reject
            modalHeader.className = 'modal-header';
            modalHeader.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
            modalHeader.style.color = 'white';
            modalTitle.innerHTML = '<i class="fas fa-times-circle"></i> Konfirmasi Penolakan';
            actionText.textContent = 'menolak';
            reasonGroup.style.display = 'block';
            document.getElementById('rejectReason').required = true;
            document.getElementById('rejectReason').value = '';
            confirmButton.className = 'btn btn-danger';
            confirmButton.innerHTML = '<i class="fas fa-times"></i> Ya, Tolak';
        }

        confirmationModal.show();
    }
</script>
@endsection
