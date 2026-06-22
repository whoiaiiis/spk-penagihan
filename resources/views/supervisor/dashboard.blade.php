@extends('layouts.app')

@section('title', 'Dashboard Supervisor - Monitoring Performa Petugas')

@section('content')

<style>
    /* ================================================================
       DASHBOARD SUPERVISOR STYLES
    ================================================================ */
    .supervisor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px; /* dikurangi dari 18px */
        flex-wrap: wrap;
        gap: 12px;
    }

    .supervisor-header h1 {
        font-size: 30px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .dashboard-subtitle {
        font-size: 15px;
        color: var(--text-muted);
        margin-top: 2px; /* dikurangi dari 4px */
    }

    .dashboard-actions {
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
        font-size: 15px;
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
       FILTER + REFRESH SEJAJAR (satu baris)
    ================================================================ */
    .filter-refresh-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-body);
        padding: 10px 16px;
        border-radius: var(--radius-lg);
        margin-bottom: 16px; /* dikurangi dari 32px */
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .filter-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
    }

    .filter-input {
        padding: 6px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 15px;
        background: white;
        font-family: var(--font-sans);
    }

    /* KPI CARDS */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
        margin-bottom: 20px; /* dikurangi dari 32px */
    }

    .kpi-card {
        background: white;
        padding: 18px 20px; /* sedikit dikurangi dari 20px */
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
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px; /* dikurangi dari 8px */
    }

    .kpi-value {
        font-size: 30px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px; /* dikurangi dari 4px */
        line-height: 1.2;
    }

    .kpi-detail {
        font-size: 14px;
        color: var(--text-muted);
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
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
    }

    /* TABLE SECTIONS */
    .table-section {
        background: white;
        padding: 20px 24px; /* top/bottom dikurangi dari 24px */
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px; /* dikurangi dari 32px */
    }

    .table-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 14px; /* dikurangi dari 16px */
    }

    .supervisor-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
    }

    .supervisor-table thead tr {
        background: var(--bg-body);
        border-bottom: 2px solid var(--border);
    }

    .supervisor-table th {
        padding: 10px 16px; /* dikurangi dari 12px */
        text-align: left;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .supervisor-table td {
        padding: 12px 16px; /* dikurangi dari 14px */
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
    }

    .supervisor-table tbody tr:hover {
        background: var(--bg-body);
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--pln-blue), var(--pln-blue-dark));
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 14px;
    }

    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-good {
        background: #E8F8F0;
        color: #1A7A4A;
    }

    .badge-fair {
        background: #FEF3E2;
        color: #B35400;
    }

    .badge-poor {
        background: #FEE8E8;
        color: #C0392B;
    }

    .text-right {
        text-align: right;
    }

    .text-muted {
        color: var(--text-muted);
    }

    .mono {
        font-family: var(--font-mono);
    }

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
        font-size: 50px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state-text {
        font-size: 16px;
    }

    /* ================================================================
       RESPONSIVE
    ================================================================ */
    @media (max-width: 1024px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .supervisor-header h1 { font-size: 26px; }
        .supervisor-table { font-size: 14px; }
    }

    @media (max-width: 768px) {
        .supervisor-header {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .supervisor-header h1 { font-size: 22px; }

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
        .kpi-value { font-size: 26px; }

        .supervisor-table { font-size: 13px; }
        .supervisor-table th,
        .supervisor-table td { padding: 8px 10px; }

        .filter-group { width: 100%; }
        .filter-input { width: 100%; height: 40px; font-size: 14px; }

        .dashboard-actions { flex-wrap: wrap; gap: 8px; }
        .btn-action { padding: 7px 12px; font-size: 14px; }

        h1 { font-size: 20px !important; }
    }

    @media (max-width: 576px) {
        .kpi-grid { gap: 10px; }
        .kpi-card { padding: 12px; }
        .kpi-value { font-size: 22px; }
        .supervisor-table { font-size: 12px; }
        .supervisor-table th,
        .supervisor-table td { padding: 6px 8px; }
        .btn-action { padding: 6px 10px; font-size: 13px; }
        h1 { font-size: 18px !important; }
        .filter-input { height: 36px; font-size: 13px; }
    }

    @media (max-width: 400px) {
        .kpi-card { padding: 10px; }
        .kpi-value { font-size: 20px; }
        .supervisor-table { font-size: 11px; }
        .supervisor-table th,
        .supervisor-table td { padding: 5px 6px; }
        h1 { font-size: 16px !important; }
    }
</style>

<div class="container-fluid" style="padding: 20px 32px;">

    <!-- ========== HEADER ========== -->
    <div class="supervisor-header">
        <div>
            <h1><i class="bi bi-speedometer2" style="margin-right: 10px;"></i>Dashboard Monitoring Supervisor</h1>
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
        <div class="dashboard-actions">
            <button class="btn-action btn-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- ========== KPI CARDS ========== -->
    <div class="kpi-grid">
        <!-- Total Petugas Aktif -->
        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-people" style="margin-right: 6px;"></i> Total Petugas
            </div>
            <div class="kpi-value">{{ $totalPetugasAktif }}</div>
            <div class="kpi-detail">Petugas aktif</div>
        </div>

        <!-- Total Pelanggan Tertagih -->
        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-person-check" style="margin-right: 6px;"></i> Pelanggan Tertagih
            </div>
            <div class="kpi-value">{{ number_format($totalPelangganTertagih) }}</div>
            <div class="kpi-detail">Berhasil ditagih</div>
        </div>

        <!-- Total Nominal Tertagih -->
        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-cash-coin" style="margin-right: 6px;"></i> Total Nominal
            </div>
            <div class="kpi-value">Rp {{ number_format($totalNominalTertagih, 0, ',', '.') }}</div>
            <div class="kpi-detail">Nominal terkumpul</div>
        </div>

        <!-- Persentase Kinerja -->
        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-percent" style="margin-right: 6px;"></i> Kinerja
            </div>
            <div class="kpi-value">{{ $persentaseKinerja }}%</div>
            <div class="kpi-detail">Tingkat keberhasilan</div>
        </div>
    </div>

    <!-- ========== CHARTS ========== -->
    <div class="charts-grid">
        <!-- Bar Chart: Top 10 Petugas -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-bar-chart"></i> Top 10 Petugas
            </div>
            <canvas id="topPetugasChart" height="80"></canvas>
        </div>

        <!-- Pie Chart: Status Penagihan -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart"></i> Status Penagihan Bulan Ini
            </div>
            <canvas id="statusChart" height="80"></canvas>
        </div>
    </div>

    <!-- ========== TOP 10 PETUGAS ========== -->
    <div class="table-section">
        <div class="table-title">
            <i class="bi bi-trophy" style="margin-right: 8px;"></i> Top 10 Petugas Terbaik
        </div>
        @if($topPetugas->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                <div class="empty-state-text">Belum ada data penagihan untuk bulan ini</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="supervisor-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Rank</th>
                            <th>Nama Petugas</th>
                            <th>Wilayah</th>
                            <th class="text-right">Jumlah Tagihan</th>
                            <th class="text-right">Total Tertagih</th>
                            <th class="text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topPetugas as $idx => $petugas)
                            <tr>
                                <td>
                                    <span class="rank-badge">{{ $idx + 1 }}</span>
                                </td>
                                <td><strong>{{ $petugas->nama }}</strong></td>
                                <td>{{ $petugas->wilayah ?? '-' }}</td>
                                <td class="text-right mono">{{ $petugas->jumlah_tagihan }}</td>
                                <td class="text-right mono">Rp {{ number_format($petugas->total_tertagih, 0, ',', '.') }}</td>
                                <td class="text-right">
                                    <span class="badge-status {{ $petugas->persentase >= 80 ? 'badge-good' : ($petugas->persentase >= 60 ? 'badge-fair' : 'badge-poor') }}">
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

    <!-- ========== RANKING KEBERHASILAN ========== -->
    <div class="table-section">
        <div class="table-title">
            <i class="bi bi-graph-up-arrow" style="margin-right: 8px;"></i> Ranking Keberhasilan Penagihan
        </div>
        @if($rankingPetugas->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                <div class="empty-state-text">Belum ada data petugas</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="supervisor-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Rank</th>
                            <th>Nama Petugas</th>
                            <th>Wilayah</th>
                            <th class="text-right">Sudah</th>
                            <th class="text-right">Belum</th>
                            <th class="text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rankingPetugas as $idx => $ranking)
                            <tr>
                                <td>
                                    <span class="rank-badge">{{ $idx + 1 }}</span>
                                </td>
                                <td><strong>{{ $ranking->nama }}</strong></td>
                                <td>{{ $ranking->wilayah ?? '-' }}</td>
                                <td class="text-right mono">{{ $ranking->sudah_tagih }}</td>
                                <td class="text-right mono">{{ $ranking->belum_tagih }}</td>
                                <td class="text-right">
                                    <span class="badge-status {{ $ranking->persentase >= 80 ? 'badge-good' : ($ranking->persentase >= 60 ? 'badge-fair' : 'badge-poor') }}">
                                        {{ $ranking->persentase }}%
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
            <i class="bi bi-map" style="margin-right: 8px;"></i> Rekap Performa Wilayah
        </div>
        @if($rekapWilayah->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                <div class="empty-state-text">Belum ada data wilayah</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="supervisor-table">
                    <thead>
                        <tr>
                            <th>Wilayah</th>
                            <th class="text-right">Total Petugas</th>
                            <th class="text-right">Sudah Tagih</th>
                            <th class="text-right">Belum Tagih</th>
                            <th class="text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapWilayah as $wilayah)
                            <tr>
                                <td><strong>{{ $wilayah->wilayah ?? '-' }}</strong></td>
                                <td class="text-right mono">{{ $wilayah->total_petugas }}</td>
                                <td class="text-right mono">{{ $wilayah->total_tagih }}</td>
                                <td class="text-right mono">{{ $wilayah->total_belum }}</td>
                                <td class="text-right">
                                    <span class="badge-status {{ $wilayah->persentase >= 80 ? 'badge-good' : ($wilayah->persentase >= 60 ? 'badge-fair' : 'badge-poor') }}">
                                        {{ $wilayah->persentase }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // ================================================================
    // BAR CHART - Top 10 Petugas
    // ================================================================
    const topPetugasCtx = document.getElementById('topPetugasChart');
    if (topPetugasCtx) {
        const topPetugasData = @json($topPetugasChartData);

        new Chart(topPetugasCtx, {
            type: 'bar',
            data: {
                labels: topPetugasData.map(p => p.nama),
                datasets: [
                    {
                        label: 'Sudah Ditagih',
                        data: topPetugasData.map(p => p.jumlah_tagihan),
                        backgroundColor: '#1A7A4A',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Belum Ditagih',
                        data: topPetugasData.map(p => p.jumlah_belum),
                        backgroundColor: '#FEE8E8',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            font: { size: 12 },
                            color: '#5A6A7E'
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        stacked: true,
                        ticks: { color: '#5A6A7E' },
                        grid: { color: '#E2E9F1' }
                    },
                    y: {
                        stacked: true,
                        ticks: { color: '#5A6A7E' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ================================================================
    // PIE CHART - Status Penagihan
    // ================================================================
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = {
            sudah: {{ $statusChart['sudah'] ?? 0 }},
            belum: {{ $statusChart['belum'] ?? 0 }}
        };

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
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            font: { size: 12 },
                            color: '#5A6A7E'
                        }
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
        window.location.href = '/supervisor/dashboard?bulan=' + bulan;
    }
</script>

@endsection
