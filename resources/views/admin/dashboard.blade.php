@extends('layouts.app')

@section('title', 'Dashboard Admin - Monitoring Penagihan')

@section('content')

<style>
    /* ================================================================
       DASHBOARD ADMIN STYLES
    ================================================================ */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px; /* dikurangi dari 32px */
        flex-wrap: wrap;
        gap: 12px;
    }

    .dashboard-header h1 {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.3;
    }

    .dashboard-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 2px; /* dikurangi dari 4px */
    }

    .dashboard-header-actions {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: var(--pln-blue);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background var(--transition);
        font-family: var(--font-sans);
    }

    .btn-action:hover {
        background: var(--pln-blue-dark);
    }

    .btn-secondary {
        background: transparent;
        color: var(--pln-blue);
        border: 1px solid var(--pln-blue);
    }

    .btn-secondary:hover {
        background: var(--pln-blue-light);
    }

    /* ================================================================
       FILTER + REFRESH SEJAJAR
    ================================================================ */
    .filter-refresh-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-body);
        padding: 10px 16px;
        border-radius: var(--radius-lg);
        margin-bottom: 16px; /* dikurangi dari 24px */
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
    }

    .filter-input {
        padding: 6px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 13px;
        background: white;
        font-family: var(--font-sans);
    }

    /* KPI CARDS */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 14px;
        margin-bottom: 20px; /* dikurangi dari 32px */
    }

    .kpi-card {
        background: white;
        padding: 18px 20px; /* dikurangi dari 20px */
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        transition: all var(--transition);
        border-left: 4px solid var(--pln-blue);
    }

    .kpi-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .kpi-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px; /* dikurangi dari 8px */
    }

    .kpi-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px; /* dikurangi dari 4px */
        line-height: 1.2;
    }

    .kpi-detail {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* PROGRESS BAR */
    .progress-section {
        background: white;
        padding: 20px 24px; /* dikurangi dari 24px */
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px; /* dikurangi dari 32px */
    }

    .progress-section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 14px; /* dikurangi dari 16px */
    }

    .progress-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 14px; /* dikurangi dari 16px */
    }

    .progress-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .progress-stat-label {
        color: var(--text-secondary);
    }

    .progress-stat-value {
        font-weight: 600;
        color: var(--text-primary);
    }

    .progress-bar-wrapper {
        width: 100%;
        height: 32px;
        background: var(--bg-body);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--pln-blue), var(--pln-blue-light));
        transition: width 0.6s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        font-weight: 600;
    }

    /* CHARTS GRID */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
        margin-bottom: 20px; /* dikurangi dari 32px */
    }

    .chart-card {
        background: white;
        padding: 20px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .chart-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
    }

    /* TABLE SECTIONS */
    .table-section {
        background: white;
        padding: 20px 24px; /* dikurangi dari 24px */
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px; /* dikurangi dari 32px */
    }

    .table-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 14px; /* dikurangi dari 16px */
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .admin-table thead tr {
        background: var(--bg-body);
        border-bottom: 2px solid var(--border);
    }

    .admin-table th {
        padding: 10px 16px; /* dikurangi dari 12px */
        text-align: left;
        vertical-align: middle;
    }

    .admin-table td {
        padding: 12px 16px; /* dikurangi dari 14px */
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
    }

    .admin-table th.text-center,
    .admin-table td.text-center {
        text-align: center !important;
    }

    .admin-table th.text-right,
    .admin-table td.text-right {
        text-align: right !important;
    }

    .admin-table tbody tr:hover {
        background: var(--bg-body);
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, var(--pln-blue), var(--pln-blue-dark));
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 12px;
    }

    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-good {
        background: #E8F8F0;
        color: #1A7A4A;
    }

    .badge-warning {
        background: #FEF3E2;
        color: #B35400;
    }

    .badge-danger {
        background: #FEE8E8;
        color: #C0392B;
    }

    .text-right { text-align: right; }
    .text-muted { color: var(--text-muted); }
    .mono { font-family: var(--font-mono); }

    /* EMPTY STATE */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 48px 24px;
        color: var(--text-muted);
        text-align: center;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state-text { font-size: 14px; }

    /* ================================================================
       RESPONSIVE
    ================================================================ */
    @media (max-width: 1024px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .dashboard-header h1 { font-size: 20px; }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .dashboard-header h1 { font-size: 18px; }

        .filter-refresh-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .kpi-grid {
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .kpi-card { padding: 14px 16px; }
        .kpi-value { font-size: 22px; }

        .charts-grid { grid-template-columns: 1fr; }

        .admin-table { font-size: 12px; }
        .admin-table th,
        .admin-table td { padding: 8px 10px; }

        .filter-group { width: 100%; }
        .filter-input { width: 100%; }

        .progress-stats {
            grid-template-columns: 1fr;
            gap: 8px;
        }
    }

    @media (max-width: 576px) {
        .dashboard-header { margin-bottom: 10px; }
        .dashboard-header h1 { font-size: 16px; }

        .kpi-grid { gap: 10px; }
        .kpi-card { padding: 12px; }
        .kpi-value { font-size: 20px; }

        .charts-grid { gap: 12px; }
        .chart-card { padding: 12px; }
        .table-section { padding: 14px 16px; }

        .admin-table { font-size: 11px; }
        .admin-table th,
        .admin-table td { padding: 7px 8px; }

        .filter-label { font-size: 11px; }
        .filter-input { font-size: 12px; padding: 5px 10px; }
    }

    @media (max-width: 400px) {
        .dashboard-header { margin-bottom: 8px; }
        .dashboard-header h1 { font-size: 14px; }

        .kpi-card { padding: 10px; }
        .kpi-value { font-size: 18px; }

        .admin-table { font-size: 10px; }
        .admin-table th,
        .admin-table td { padding: 6px 8px; }

        .chart-card { padding: 10px; }
    }
</style>

<div class="container-fluid" style="padding: 20px 32px;">

    <!-- ========== HEADER ========== -->
    <div class="dashboard-header">
        <div>
            <h1><i class="bi bi-graph-up" style="margin-right: 10px;"></i>Dashboard Monitoring Penagihan</h1>
            <p class="dashboard-subtitle">
                Periode: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</strong>
            </p>
        </div>
    </div>

    <!-- ========== FILTER + REFRESH SEJAJAR ========== -->
    <div class="filter-refresh-bar">
        <div class="filter-group">
            <span class="filter-label">Filter Bulan:</span>
            <input type="month" class="filter-input" id="filterBulan" value="{{ $bulan }}" onchange="filterByBulan(this.value)">
        </div>
        <div class="dashboard-header-actions">
            <button class="btn-action btn-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- ========== KPI CARDS ========== -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-people" style="margin-right: 6px;"></i> Total Petugas
            </div>
            <div class="kpi-value">{{ $totalPetugas }}</div>
            <div class="kpi-detail">Petugas aktif</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-person-check" style="margin-right: 6px;"></i> Total Pelanggan
            </div>
            <div class="kpi-value">{{ number_format($totalPelanggan) }}</div>
            <div class="kpi-detail">Pelanggan terdaftar</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-cash-coin" style="margin-right: 6px;"></i> Target Tagihan
            </div>
            <div class="kpi-value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</div>
            <div class="kpi-detail">Total rptag</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-percent" style="margin-right: 6px;"></i> Keberhasilan
            </div>
            <div class="kpi-value">{{ $persentaseTertagih }}%</div>
            <div class="kpi-detail">Tingkat penagihan bulan ini</div>
        </div>
    </div>

    <!-- ========== PROGRESS REALISASI ========== -->
    <div class="progress-section">
        <div class="progress-section-title">
            <i class="bi bi-graph-up-arrow"></i> Progress Realisasi Penagihan
        </div>
        <div class="progress-stats">
            <div class="progress-stat">
                <span class="progress-stat-label">Berhasil Ditagih:</span>
                <span class="progress-stat-value">Rp {{ number_format($totalTertagih, 0, ',', '.') }}</span>
            </div>
            <div class="progress-stat">
                <span class="progress-stat-label">Target Tagihan:</span>
                <span class="progress-stat-value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
            </div>
            <div class="progress-stat">
                <span class="progress-stat-label">Sisa Saldo:</span>
                <span class="progress-stat-value">Rp {{ number_format($totalTagihan - $totalTertagih, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="progress-bar-wrapper">
            <div class="progress-bar-fill" style="width: {{ $totalTagihan > 0 ? ($totalTertagih / $totalTagihan * 100) : 0 }}%">
                {{ $totalTagihan > 0 ? round(($totalTertagih / $totalTagihan * 100), 1) : 0 }}%
            </div>
        </div>
    </div>

    <!-- ========== CHARTS ========== -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart"></i> Status Penagihan Bulan Ini
            </div>
            <canvas id="statusChart" height="80"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-bar-chart"></i> Top 5 Petugas
            </div>
            <canvas id="topPetugasChart" height="80"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-graph-up-arrow"></i> Trend 6 Bulan Terakhir
            </div>
            <canvas id="trendChart" height="80"></canvas>
        </div>
    </div>

    <!-- ========== TOP PETUGAS TABLE ========== -->
    <div class="table-section">
        <div class="table-title">
            <i class="bi bi-trophy" style="margin-right: 8px;"></i> Top 5 Petugas Berdasarkan Penagihan
        </div>
        @if($petugasTeratas->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                <div class="empty-state-text">Belum ada data penagihan untuk bulan ini</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">Rank</th>
                            <th>Nama Petugas</th>
                            <th>Wilayah</th>
                            <th class="text-right">Jumlah Pelanggan Tertagih</th>
                            <th class="text-right">Total Tertagih</th>
                            <th class="text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($petugasTeratas as $idx => $petugas)
                        <tr>
                            <td class="text-center">
                                <span class="rank-badge">{{ $idx + 1 }}</span>
                            </td>
                            <td>{{ $petugas->nama }}</td>
                            <td>{{ $petugas->wilayah ?? '-' }}</td>
                            <td class="text-right mono">{{ $petugas->jumlah_tagihan }}</td>
                            <td class="text-right mono">Rp {{ number_format($petugas->total_tertagih, 0, ',', '.') }}</td>
                            <td class="text-right">
                                <span class="badge-status {{ $petugas->persentase >= 80 ? 'badge-good' : ($petugas->persentase >= 60 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $petugas->persentase }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- ========== REKAP WILAYAH ========== -->
    <div class="table-section">
        <div class="table-title">
            <i class="bi bi-map" style="margin-right: 8px;"></i> Rekap Penagihan Per Wilayah
        </div>
        @if($rekapWilayah->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                <div class="empty-state-text">Belum ada data wilayah</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Wilayah</th>
                            <th class="text-right">Total Petugas</th>
                            <th class="text-right">Total Pelanggan</th>
                            <th class="text-right">Total Tertagih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapWilayah as $wilayah)
                            <tr>
                                <td>{{ $wilayah->wilayah ?? '-' }}</td>
                                <td class="text-right mono">{{ $wilayah->total_petugas }}</td>
                                <td class="text-right mono">{{ $wilayah->total_pelanggan }}</td>
                                <td class="text-right mono">Rp {{ number_format($wilayah->total_tertagih, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- ========== REKAP BULANAN ========== -->
    <div class="table-section">
        <div class="table-title">
            <i class="bi bi-calendar3" style="margin-right: 8px;"></i> Rekap 6 Bulan Terakhir
        </div>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Target Tagihan (Rptag)</th>
                        <th class="text-right">Total Tertagih</th>
                        <th class="text-right">Sisa Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapBulan as $rekap)
                        <tr>
                            <td>{{ $rekap['bulan_label'] }}</td>
                            <td class="text-right mono">Rp {{ number_format($rekap['total_rptag'], 0, ',', '.') }}</td>
                            <td class="text-right mono">Rp {{ number_format($rekap['total_tagih'], 0, ',', '.') }}</td>
                            <td class="text-right mono">Rp {{ number_format($rekap['total_rptag'] - $rekap['total_tagih'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // ================================================================
    // RESPONSIVE CHART SETTINGS
    // ================================================================
    const isMobile = window.innerWidth <= 768;
    const isSmallDevice = window.innerWidth <= 576;

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        devicePixelRatio: 2
    };

    // ================================================================
    // PIE CHART - Status Penagihan
    // ================================================================
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = {
            sudah: {{ $statusChart['sudah'] ?? 0 }},
            belum: {{ $statusChart['belum'] ?? 0 }}
        };

        statusCtx.parentElement.style.position = 'relative';
        statusCtx.parentElement.style.height = isMobile ? '250px' : '300px';

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sudah Ditagih', 'Belum Ditagih'],
                datasets: [{
                    data: [statusData.sudah, statusData.belum],
                    backgroundColor: ['#1A7A4A', '#FEE8E8'],
                    borderColor: ['#1A7A4A', '#C0392B'],
                    borderWidth: 2,
                }]
            },
            options: {
                ...chartOptions,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: isSmallDevice ? 8 : 16,
                            font: { size: isSmallDevice ? 10 : 12 },
                            color: '#5A6A7E'
                        }
                    }
                }
            }
        });
    }

    // ================================================================
    // BAR CHART - Top Petugas
    // ================================================================
    const topPetugasCtx = document.getElementById('topPetugasChart');
    if (topPetugasCtx) {
        const topPetugasData = @json($topPetugasChart);

        topPetugasCtx.parentElement.style.position = 'relative';
        topPetugasCtx.parentElement.style.height = isMobile ? '250px' : '300px';

        new Chart(topPetugasCtx, {
            type: 'bar',
            data: {
                labels: topPetugasData.map(p => p.nama.length > 15 ? p.nama.substring(0, 15) + '...' : p.nama),
                datasets: [{
                    label: 'Jumlah Pelanggan Tertagih',
                    data: topPetugasData.map(p => p.jumlah),
                    backgroundColor: '#0055A5',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                ...chartOptions,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            padding: isSmallDevice ? 8 : 16,
                            font: { size: isSmallDevice ? 10 : 12 },
                            color: '#5A6A7E'
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { color: '#5A6A7E', font: { size: isSmallDevice ? 9 : 11 } },
                        grid: { color: '#E2E9F1' }
                    },
                    y: {
                        ticks: { color: '#5A6A7E', font: { size: isSmallDevice ? 9 : 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ================================================================
    // LINE CHART - Trend 6 Bulan
    // ================================================================
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        const trendData = @json($trendChart);

        trendCtx.parentElement.style.position = 'relative';
        trendCtx.parentElement.style.height = isMobile ? '250px' : '300px';

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(t => t.bulan),
                datasets: [
                    {
                        label: 'Target (Rptag)',
                        data: trendData.map(t => t.total_rptag),
                        borderColor: '#0055A5',
                        backgroundColor: 'rgba(0, 85, 165, 0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: isSmallDevice ? 3 : 4,
                        pointBackgroundColor: '#0055A5',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Tertagih',
                        data: trendData.map(t => t.total_tagih),
                        borderColor: '#1A7A4A',
                        backgroundColor: 'rgba(26, 122, 74, 0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: isSmallDevice ? 3 : 4,
                        pointBackgroundColor: '#1A7A4A',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                ...chartOptions,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: isSmallDevice ? 8 : 16,
                            font: { size: isSmallDevice ? 10 : 12 },
                            color: '#5A6A7E'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#5A6A7E',
                            font: { size: isSmallDevice ? 9 : 11 },
                            callback: function(value) {
                                if (value >= 1000000000) return 'Rp' + (value/1000000000).toFixed(1) + 'M';
                                if (value >= 1000000) return 'Rp' + (value/1000000).toFixed(1) + 'J';
                                return 'Rp' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: { color: '#E2E9F1' }
                    },
                    x: {
                        ticks: { color: '#5A6A7E', font: { size: isSmallDevice ? 9 : 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ================================================================
    // FILTER FUNGSI
    // ================================================================
    function filterByBulan(bulan) {
        if (!bulan) return;
        window.location.href = '/admin/dashboard?bulan=' + bulan;
    }

    // ================================================================
    // EXPORT FUNGSI (Stub untuk export)
    // ================================================================
    function exportDashboard() {
        alert('Fitur export sedang dalam pengembangan');
    }

    // ================================================================
    // RESPONSIVE RESIZE HANDLER
    // ================================================================
    window.addEventListener('resize', function() {
        location.reload();
    });
</script>

@endsection