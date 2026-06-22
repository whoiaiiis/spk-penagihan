@extends('layouts.app')

@section('title', 'Dashboard Staff - Monitoring Penagihan')

@section('content')

<style>
    /* ================================================================
       DASHBOARD STAFF STYLES
    ================================================================ */
    .staff-dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px; /* dikurangi dari 32px */
        flex-wrap: wrap;
        gap: 12px;
    }

    .staff-dashboard-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.3;
    }

    .dashboard-subtitle {
        font-size: 15px;
        color: var(--text-muted);
        margin-top: 2px;
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

    .btn-action:hover { background: var(--pln-blue-dark); }

    .btn-secondary {
        background: transparent;
        color: var(--pln-blue);
        border: 1px solid var(--pln-blue);
    }

    .btn-secondary:hover { background: var(--pln-blue-light); }

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
        margin-bottom: 16px;
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .kpi-card {
        background: white;
        padding: 18px 20px;
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
        margin-bottom: 6px;
    }

    .kpi-value {
        font-size: 30px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .kpi-detail {
        font-size: 14px;
        color: var(--text-muted);
    }

    /* PROGRESS SECTION */
    .progress-section {
        background: white;
        padding: 20px 24px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
    }

    .progress-section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 14px;
    }

    .progress-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .progress-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 15px;
    }

    .progress-stat-label { color: var(--text-secondary); }

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
        font-size: 14px;
        font-weight: 600;
    }

    /* CHARTS GRID */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
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
        padding: 20px 24px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
    }

    .table-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 14px;
    }

    .staff-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
    }

    .staff-table thead tr {
        background: var(--bg-body);
        border-bottom: 2px solid var(--border);
    }

    .staff-table th {
        padding: 10px 16px;
        text-align: left;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .staff-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
    }

    .staff-table tbody tr:hover { background: var(--bg-body); }

    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-success { background: #E8F8F0; color: #1A7A4A; }
    .badge-pending { background: #FEF3E2; color: #B35400; }

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

    .empty-state-icon { font-size: 50px; margin-bottom: 16px; opacity: 0.5; }
    .empty-state-text { font-size: 16px; }

    /* ================================================================
       RESPONSIVE
    ================================================================ */
    @media (max-width: 1024px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .staff-dashboard-header h1 { font-size: 22px; }
    }

    @media (max-width: 768px) {
        .staff-dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .staff-dashboard-header h1 { font-size: 20px; }

        .filter-refresh-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .kpi-grid { grid-template-columns: 1fr; gap: 12px; margin-bottom: 16px; }
        .kpi-card { padding: 14px 16px; }
        .kpi-value { font-size: 24px; }

        .charts-grid { grid-template-columns: 1fr; }

        .staff-table { font-size: 14px; }
        .staff-table th,
        .staff-table td { padding: 8px 10px; }

        .filter-group { width: 100%; }
        .filter-input { width: 100%; }

        .progress-stats { grid-template-columns: 1fr; gap: 8px; }
    }

    @media (max-width: 576px) {
        .staff-dashboard-header h1 { font-size: 18px; }
        .kpi-grid { gap: 10px; }
        .kpi-card { padding: 12px; }
        .kpi-value { font-size: 22px; }
        .charts-grid { gap: 12px; }
        .chart-card { padding: 12px; }
        .table-section { padding: 14px 16px; }
        .staff-table { font-size: 13px; }
        .staff-table th,
        .staff-table td { padding: 7px 8px; }
        .filter-label { font-size: 13px; }
        .filter-input { font-size: 14px; padding: 5px 10px; }
    }

    @media (max-width: 400px) {
        .staff-dashboard-header h1 { font-size: 16px; }
        .kpi-card { padding: 10px; }
        .kpi-value { font-size: 20px; }
        .staff-table { font-size: 12px; }
        .staff-table th,
        .staff-table td { padding: 6px 8px; }
        .chart-card { padding: 10px; }
    }

    /* Responsive container padding */
    .dash-container { padding: 20px 32px; }
    @media (max-width: 768px) { .dash-container { padding: 16px 16px; } }
    @media (max-width: 576px) { .dash-container { padding: 14px 12px; } }
    @media (max-width: 400px) { .dash-container { padding: 12px 10px; } }
</style>

<div class="container-fluid dash-container">

    {{-- Bagian Header Dashboard Staff --}}
    <div class="staff-dashboard-header">
        <div>
            <h1><i class="bi bi-graph-up" style="margin-right: 10px;"></i>Dashboard Penagihan</h1>
            <p class="dashboard-subtitle">
                Periode: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</strong>
            </p>
        </div>
    </div>

    {{-- Form filter data berdasarkan bulan --}}
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

    {{-- Menampilkan kartu ringkasan data penagihan (KPI) --}}
    <div class="kpi-grid">
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
                <i class="bi bi-check-circle" style="margin-right: 6px;"></i> Tertagih
            </div>
            <div class="kpi-value">Rp {{ number_format($totalTertagih, 0, ',', '.') }}</div>
            <div class="kpi-detail">Bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F') }}</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-percent" style="margin-right: 6px;"></i> Keberhasilan
            </div>
            <div class="kpi-value">{{ $persentaseKeberhasilan }}%</div>
            <div class="kpi-detail">Tingkat penagihan bulan ini</div>
        </div>
    </div>

    {{-- Bagian progress bar realisasi penagihan --}}
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
                <span class="progress-stat-label">Belum Ditagih:</span>
                <span class="progress-stat-value">{{ $totalBelumTagih }} pelanggan</span>
            </div>
        </div>
        <div class="progress-bar-wrapper">
            <div class="progress-bar-fill" style="width: {{ $totalTagihan > 0 ? ($totalTertagih / $totalTagihan * 100) : 0 }}%">
                {{ $totalTagihan > 0 ? round(($totalTertagih / $totalTagihan * 100), 1) : 0 }}%
            </div>
        </div>
    </div>

    {{-- Tempat menampilkan grafik statistik --}}
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-graph-up-arrow"></i> Tren Penagihan 6 Bulan
            </div>
            <canvas id="trendChart" height="80"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart"></i> Status Penagihan Bulan Ini
            </div>
            <canvas id="statusChart" height="80"></canvas>
        </div>
    </div>

    {{-- Tabel top 5 pelanggan dengan tagihan terbesar --}}
    <div class="table-section">
        <div class="table-title">
            <i class="bi bi-trophy" style="margin-right: 8px;"></i> Top 5 Pelanggan Tagihan Terbesar
        </div>
        @if($topPelanggan->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                <div class="empty-state-text">Belum ada data pelanggan</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>IDPEL</th>
                            <th>Nama Pelanggan</th>
                            <th class="text-right">Nominal</th>
                            <th>Status</th>
                            <th class="text-right">Tertagih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topPelanggan as $pelanggan)
                            <tr>
                                <td><strong>{{ $pelanggan->idpel }}</strong></td>
                                <td>{{ $pelanggan->nama }}</td>
                                <td class="text-right mono">Rp {{ number_format($pelanggan->rptag, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge-status {{ $pelanggan->status_tagih === 'sudah' ? 'badge-success' : 'badge-pending' }}">
                                        {{ $pelanggan->status_tagih === 'sudah' ? 'Sudah' : 'Belum' }}
                                    </span>
                                </td>
                                <td class="text-right mono">Rp {{ number_format($pelanggan->jumlah_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Tabel aktivitas penagihan terbaru --}}
    <div class="table-section">
        <div class="table-title">
            <i class="bi bi-clock-history" style="margin-right: 8px;"></i> Aktivitas Penagihan Terbaru
        </div>
        @if($aktivitasTerbaru->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                <div class="empty-state-text">Belum ada aktivitas penagihan untuk bulan ini</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>IDPEL</th>
                            <th>Nama Pelanggan</th>
                            <th class="text-right">Nominal</th>
                            <th>Status</th>
                            <th class="text-right">Tertagih</th>
                            <th class="text-right">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($aktivitasTerbaru as $aktivitas)
                            <tr>
                                <td><strong>{{ $aktivitas->idpel }}</strong></td>
                                <td>{{ $aktivitas->nama }}</td>
                                <td class="text-right mono">Rp {{ number_format($aktivitas->rptag, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge-status {{ $aktivitas->status === 'sudah' ? 'badge-success' : 'badge-pending' }}">
                                        {{ $aktivitas->status === 'sudah' ? 'Sudah' : 'Belum' }}
                                    </span>
                                </td>
                                <td class="text-right mono">Rp {{ number_format($aktivitas->jumlah_bayar, 0, ',', '.') }}</td>
                                <td class="text-right text-muted">
                                    {{ $aktivitas->tanggal_tagih ? \Carbon\Carbon::parse($aktivitas->tanggal_tagih)->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

{{-- Memuat library Chart.js untuk menampilkan grafik --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

{{-- Konfigurasi grafik Chart.js dan filter bulan --}}
<script>
    const isMobile = window.innerWidth <= 768;
    const isSmallDevice = window.innerWidth <= 576;

    // ================================================================
    // LINE CHART - Trend 6 Bulan
    // ================================================================
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        const trendData = @json($trendChartData);

        trendCtx.parentElement.style.position = 'relative';
        trendCtx.parentElement.style.height = isMobile ? '250px' : '300px';

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(t => t.bulan),
                datasets: [
                    {
                        label: 'Target (Rptag)',
                        data: trendData.map(t => t.target),
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
                        label: 'Terbayar',
                        data: trendData.map(t => t.terbayar),
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
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: 2,
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
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: 2,
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
    // FILTER FUNGSI
    // ================================================================
    function filterByBulan(bulan) {
        if (!bulan) return;
        window.location.href = '/staff/dashboard?bulan=' + bulan;
    }
</script>

@endsection
