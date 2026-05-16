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
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .dashboard-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .dashboard-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
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

    /* KPI CARDS */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .kpi-card {
        background: white;
        padding: 20px;
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
        margin-bottom: 8px;
    }

    .kpi-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .kpi-detail {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* PROGRESS BAR */
    .progress-section {
        background: white;
        padding: 24px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 32px;
    }

    .progress-section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
    }

    .progress-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
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
        margin-bottom: 32px;
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
        padding: 24px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 32px;
    }

    .table-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
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
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }

    .admin-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
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
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state-text {
        font-size: 14px;
    }

    /* FILTER SECTION */
    .filter-section {
        background: var(--bg-body);
        padding: 16px 20px;
        border-radius: var(--radius-lg);
        margin-bottom: 24px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
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

    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .kpi-grid {
            grid-template-columns: 1fr;
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }

        .admin-table {
            font-size: 12px;
        }

        .admin-table th,
        .admin-table td {
            padding: 10px 12px;
        }

        .filter-section {
            flex-direction: column;
        }

        .filter-group {
            width: 100%;
        }

        .filter-input {
            width: 100%;
        }
    }
</style>

<div class="container-fluid" style="padding: 24px 32px;">

    <!-- ========== HEADER ========== -->
    <div class="dashboard-header">
        <div>
            <h1><i class="bi bi-graph-up" style="margin-right: 10px;"></i>Dashboard Monitoring Penagihan</h1>
            <p class="dashboard-subtitle">
                Periode: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</strong>
            </p>
        </div>
        <div class="dashboard-header-actions">
            <button class="btn-action btn-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button class="btn-action" onclick="exportDashboard()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>

    <!-- ========== FILTER ========== -->
    <div class="filter-section">
        <div class="filter-group">
            <span class="filter-label">Filter Bulan:</span>
            <input type="month" class="filter-input" id="filterBulan" value="{{ $bulan }}" onchange="filterByBulan(this.value)">
        </div>
    </div>

    <!-- ========== KPI CARDS ========== -->
    <div class="kpi-grid">
        <!-- Total Petugas -->
        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-people" style="margin-right: 6px;"></i> Total Petugas
            </div>
            <div class="kpi-value">{{ $totalPetugas }}</div>
            <div class="kpi-detail">Petugas aktif</div>
        </div>

        <!-- Total Pelanggan -->
        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-person-check" style="margin-right: 6px;"></i> Total Pelanggan
            </div>
            <div class="kpi-value">{{ number_format($totalPelanggan) }}</div>
            <div class="kpi-detail">Pelanggan terdaftar</div>
        </div>

        <!-- Total Tagihan -->
        <div class="kpi-card">
            <div class="kpi-label">
                <i class="bi bi-cash-coin" style="margin-right: 6px;"></i> Target Tagihan
            </div>
            <div class="kpi-value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</div>
            <div class="kpi-detail">Total rptag</div>
        </div>

        <!-- Persentase Keberhasilan -->
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
        <!-- Pie Chart: Status Penagihan -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart"></i> Status Penagihan Bulan Ini
            </div>
            <canvas id="statusChart" height="80"></canvas>
        </div>

        <!-- Bar Chart: Top Petugas -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-bar-chart"></i> Top 5 Petugas
            </div>
            <canvas id="topPetugasChart" height="80"></canvas>
        </div>

        <!-- Line Chart: Trend 6 Bulan -->
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
                            <th style="width: 40px;">Rank</th>
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
                                <td>
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
    // BAR CHART - Top Petugas
    // ================================================================
    const topPetugasCtx = document.getElementById('topPetugasChart');
    if (topPetugasCtx) {
        const topPetugasData = @json($topPetugasChart);

        new Chart(topPetugasCtx, {
            type: 'bar',
            data: {
                labels: topPetugasData.map(p => p.nama),
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
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
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
                        ticks: { color: '#5A6A7E' },
                        grid: { color: '#E2E9F1' }
                    },
                    y: {
                        ticks: { color: '#5A6A7E' },
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
                        pointRadius: 4,
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
                        pointRadius: 4,
                        pointBackgroundColor: '#1A7A4A',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }
                ]
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
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#5A6A7E',
                            callback: function(value) {
                                return 'Rp' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: { color: '#E2E9F1' }
                    },
                    x: {
                        ticks: { color: '#5A6A7E' },
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
</script>

@endsection
