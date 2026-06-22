{{-- resources/views/supervisor/monitoringkinerja.blade.php --}}
@extends('layouts.app')

@section('title', 'Monitoring Kinerja Petugas')

@push('styles')
<style>
    /* ── Tokens ──────────────────────────────────────────────────────────── */
    :root {
        --c-bg:         #F0F4F8;
        --c-surface:    #FFFFFF;
        --c-border:     #E2E8F0;
        --c-primary:    #2563EB;
        --c-primary-dk: #1D4ED8;
        --c-primary-lt: #EFF6FF;
        --c-success:    #16A34A;
        --c-success-lt: #F0FDF4;
        --c-success-bd: #BBF7D0;
        --c-danger:     #DC2626;
        --c-danger-lt:  #FEF2F2;
        --c-danger-bd:  #FECACA;
        --c-warning:    #D97706;
        --c-warning-lt: #FFFBEB;
        --c-warning-bd: #FDE68A;
        --c-text:       #1E293B;
        --c-muted:      #64748B;
        --c-light:      #94A3B8;
        --radius:       12px;
        --radius-sm:    8px;
        --shadow:       0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
        --font:         'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
        --mono:         'JetBrains Mono', 'Fira Code', monospace;
    }

    body { background: var(--c-bg); font-family: var(--font); color: var(--c-text); }

    /* ── Layout ──────────────────────────────────────────────────────────── */
    .page-wrap { max-width: 1200px; padding: 24px 20px 40px; }
    .breadcrumb-bar {
        font-size: 0.9375rem; color: var(--c-muted);
        margin-bottom: 14px; display: flex; align-items: center; gap: 6px;
    }
    .breadcrumb-bar a { color: var(--c-primary); text-decoration: none; }
    .breadcrumb-bar .sep { color: var(--c-light); }

    .page-header { margin-bottom: 24px; display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .page-header h1 { font-size: 1.5rem; font-weight: 700; letter-spacing: -.02em; margin: 0 0 2px; }
    .page-header p  { font-size: 1rem; color: var(--c-muted); margin: 0; }

    /* ── Filter bar ──────────────────────────────────────────────────────── */
    .filter-bar {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); padding: 16px 20px;
        display: flex; align-items: flex-end; justify-content: flex-start;
        gap: 16px; flex-wrap: wrap;
        box-shadow: var(--shadow); margin-bottom: 24px;
    }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-size: 0.905rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); }
    .filter-group select,
    .filter-group input[type="month"] {
        height: 38px; padding: 0 12px; border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); font-size: 1rem; color: var(--c-text);
        background: #F8FAFC; outline: none; transition: border-color .15s;
        font-family: var(--font);
    }
    .filter-group select:focus,
    .filter-group input[type="month"]:focus { border-color: var(--c-primary); background: #fff; }

    /* ── Filter actions (tombol-tombol sejajar) ──────────────────────────── */
    .filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 18px; border-radius: var(--radius-sm);
        font-size: 1rem; font-weight: 600; cursor: pointer;
        border: 1px solid transparent; transition: all .15s;
        text-decoration: none; line-height: 1; height: 38px;
    }
    .btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .btn-primary  { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }
    .btn-primary:hover { background: var(--c-primary-dk); }
    .btn-outline  { background: #fff; color: var(--c-muted); border-color: var(--c-border); }
    .btn-outline:hover { background: #F8FAFC; color: var(--c-text); }
    .btn-danger   { background: var(--c-danger); color: #fff; border-color: var(--c-danger); }
    .btn-danger:hover { background: #B91C1C; }
    .btn-sm { padding: 6px 12px; font-size: 0.9375rem; height: auto; }

    /* ── Summary cards ───────────────────────────────────────────────────── */
    .summary-grid {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 14px; margin-bottom: 24px;
    }
    .sum-card {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); padding: 18px 20px;
        box-shadow: var(--shadow); position: relative; overflow: hidden;
    }
    .sum-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    }
    .sum-card.blue::before   { background: var(--c-primary); }
    .sum-card.green::before  { background: var(--c-success); }
    .sum-card.red::before    { background: var(--c-danger); }
    .sum-card.orange::before { background: var(--c-warning); }
    .sum-card.purple::before { background: #7C3AED; }
    .sum-card.teal::before   { background: #0D9488; }

    .sum-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px;
    }
    .sum-icon.blue   { background: var(--c-primary-lt); color: var(--c-primary); }
    .sum-icon.green  { background: var(--c-success-lt); color: var(--c-success); }
    .sum-icon.red    { background: var(--c-danger-lt);  color: var(--c-danger); }
    .sum-icon.orange { background: var(--c-warning-lt); color: var(--c-warning); }
    .sum-icon.purple { background: #F5F3FF; color: #7C3AED; }
    .sum-icon.teal   { background: #F0FDFA; color: #0D9488; }

    .sum-label { font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); margin-bottom: 4px; }
    .sum-value { font-size: 1.75rem; font-weight: 700; line-height: 1; color: var(--c-text); font-family: var(--mono); }
    .sum-sub   { font-size: 0.925rem; color: var(--c-muted); margin-top: 4px; }

    /* ── Saldo summary (wide) ────────────────────────────────────────────── */
    .saldo-row {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 14px; margin-bottom: 24px;
    }
    .saldo-card {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); padding: 18px 20px;
        box-shadow: var(--shadow);
    }
    .saldo-card .s-label { font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); margin-bottom: 6px; }
    .saldo-card .s-value { font-size: 1.5rem; font-weight: 700; font-family: var(--mono); }
    .saldo-card.rpta   .s-value { color: var(--c-text); }
    .saldo-card.tagih  .s-value { color: var(--c-success); }
    .saldo-card.sisa   .s-value { color: var(--c-danger); }
    .saldo-card .s-bar { height: 6px; background: var(--c-border); border-radius: 99px; margin-top: 10px; overflow: hidden; }
    .saldo-card .s-fill { height: 100%; border-radius: 99px; transition: width .6s ease; }
    .saldo-card.tagih .s-fill { background: var(--c-success); }
    .saldo-card.sisa  .s-fill { background: var(--c-danger); }

    /* ── Chart section ───────────────────────────────────────────────────── */
    .chart-card {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); padding: 22px 24px;
        box-shadow: var(--shadow); margin-bottom: 24px;
    }
    .chart-card h3 { font-size: 1.0625rem; font-weight: 600; margin: 0 0 16px; }
    .chart-wrap { position: relative; height: 260px; }

    /* ── Table card ──────────────────────────────────────────────────────── */
    .table-card {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
    }
    .table-card-header {
        padding: 16px 20px; border-bottom: 1px solid var(--c-border);
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .table-card-header h3 { font-size: 1.0625rem; font-weight: 600; margin: 0; }

    .tbl-wrap { overflow-x: auto; }
    .rank-table { width: 100%; border-collapse: collapse; font-size: 1rem; }
    .rank-table thead th {
        background: #F8FAFC; padding: 10px 14px;
        text-align: left; font-size: 0.875rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--c-muted); border-bottom: 1px solid var(--c-border); white-space: nowrap;
    }
    .rank-table thead th.right { text-align: right; }
    .rank-table tbody tr { border-bottom: 1px solid #F1F5F9; transition: background .1s; }
    .rank-table tbody tr:hover { background: #F8FAFC; }
    .rank-table tbody tr:last-child { border-bottom: none; }
    .rank-table td { padding: 12px 14px; vertical-align: middle; white-space: nowrap; }
    .rank-table td.right { text-align: right; }
    .rank-table td.mono { font-family: var(--mono); font-size: 0.945rem; }

    /* Rank badge */
    .rank-badge {
        width: 28px; height: 28px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.905rem; font-weight: 700; font-family: var(--mono);
    }
    .rank-badge.gold   { background: #FEF9C3; color: #A16207; border: 1.5px solid #FDE047; }
    .rank-badge.silver { background: #F1F5F9; color: #475569; border: 1.5px solid #CBD5E1; }
    .rank-badge.bronze { background: #FEF3C7; color: #92400E; border: 1.5px solid #FCD34D; }
    .rank-badge.normal { background: #F8FAFC; color: var(--c-muted); border: 1.5px solid var(--c-border); }

    /* Progress bar cell */
    .progress-cell { min-width: 140px; }
    .progress-wrap { display: flex; align-items: center; gap: 8px; }
    .progress-bar  { flex: 1; height: 8px; background: var(--c-border); border-radius: 99px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 99px; transition: width .5s ease; }
    .progress-fill.green  { background: var(--c-success); }
    .progress-fill.yellow { background: var(--c-warning); }
    .progress-fill.red    { background: var(--c-danger); }
    .progress-pct { font-size: 0.945rem; font-weight: 700; font-family: var(--mono); min-width: 40px; text-align: right; }
    .progress-pct.green  { color: var(--c-success); }
    .progress-pct.yellow { color: var(--c-warning); }
    .progress-pct.red    { color: var(--c-danger); }

    /* Status pill */
    .pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 99px; font-size: 0.875rem; font-weight: 600;
    }
    .pill.baik   { background: var(--c-success-lt); color: var(--c-success); border: 1px solid var(--c-success-bd); }
    .pill.cukup  { background: var(--c-warning-lt); color: var(--c-warning); border: 1px solid var(--c-warning-bd); }
    .pill.kurang { background: var(--c-danger-lt);  color: var(--c-danger);  border: 1px solid var(--c-danger-bd); }

    /* Empty state */
    .empty-state { padding: 48px 24px; text-align: center; color: var(--c-muted); }
    .empty-state svg { opacity: .3; margin-bottom: 12px; display: block; margin-left: auto; margin-right: auto; }
    .empty-state p { margin: 0; font-size: 1.0625rem; }

    /* ── Responsive ──────────────────────────────────────────────────────── */
    @media (max-width: 900px) {
        .summary-grid { grid-template-columns: repeat(2, 1fr); }
        .saldo-row    { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .summary-grid { grid-template-columns: 1fr 1fr; }
        .filter-bar   { flex-direction: column; align-items: stretch; }
        .filter-actions { flex-wrap: wrap; }
        .page-header  { flex-direction: column; align-items: flex-start; }
    }
</style>
@endpush

@section('content')
<div class="px-4 py-4">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h1>Monitoring Kinerja Petugas</h1>
            <p>Rekapitulasi penagihan & persentase kinerja —
                {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}
            </p>
        </div>
    </div>

    {{-- ── Filter bar ──────────────────────────────────────────────────── --}}
    <form method="GET" action="/monitoringkinerja">
        <div class="filter-bar">

            <div class="filter-group">
                <label>Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <svg fill="none" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Tampilkan
                </button>

                <a href="{{ route('monitoring.index') }}" class="btn btn-outline">
                    Reset
                </a>

                <a href="{{ route('monitoring.exportPdf', ['bulan' => $bulan]) }}"
                   class="btn btn-danger"
                   target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.6a.5.5 0 0 0 .5.5h12a.5.5 0 0 0 .5-.5v-2.6a.5.5 0 0 1 1 0v2.6A1.5 1.5 0 0 1 13.5 14h-12A1.5 1.5 0 0 1 0 12.5v-2.6a.5.5 0 0 1 .5-.5z"/>
                        <path d="M5.354 5.646a.5.5 0 1 0-.708.708L7.5 9.207V1.5a.5.5 0 0 1 1 0v7.707l2.854-2.853a.5.5 0 0 1 .708.708l-3.707 3.707a.5.5 0 0 1-.708 0L5.354 5.646z"/>
                    </svg>
                    Export PDF
                </a>
            </div>

        </div>
    </form>

    {{-- ── Summary cards ────────────────────────────────────────────────── --}}
    <div class="summary-grid">
        <div class="sum-card blue">
            <div class="sum-icon blue">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="sum-label">Total Petugas Aktif</div>
            <div class="sum-value">{{ $summary['total_petugas'] }}</div>
            <div class="sum-sub">
                <span style="color:var(--c-success)">{{ $summary['petugas_baik'] }} Baik</span> ·
                <span style="color:var(--c-warning)">{{ $summary['petugas_cukup'] }} Cukup</span> ·
                <span style="color:var(--c-danger)">{{ $summary['petugas_kurang'] }} Kurang</span>
            </div>
        </div>

        <div class="sum-card green">
            <div class="sum-icon green">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="sum-label">Rata-rata Kinerja</div>
            <div class="sum-value">{{ $summary['avg_kinerja'] }}%</div>
            <div class="sum-sub">dari total {{ number_format($summary['total_target']) }} tagihan</div>
        </div>

        <div class="sum-card green">
            <div class="sum-icon green">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="sum-label">Sudah Ditagih</div>
            <div class="sum-value">{{ number_format($summary['total_sudah']) }}</div>
            <div class="sum-sub">dari {{ number_format($summary['total_target']) }} target</div>
        </div>

        <div class="sum-card red">
            <div class="sum-icon red">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="sum-label">Belum Ditagih</div>
            <div class="sum-value">{{ number_format($summary['total_belum']) }}</div>
            <div class="sum-sub">perlu segera ditindaklanjuti</div>
        </div>
    </div>

    {{-- ── Saldo row ─────────────────────────────────────────────────────── --}}
    @php
        $pctTagih = $summary['total_rptag'] > 0 ? ($summary['total_tagih'] / $summary['total_rptag']) * 100 : 0;
        $pctSisa  = $summary['total_rptag'] > 0 ? ($summary['total_sisa']  / $summary['total_rptag']) * 100 : 0;
    @endphp
    <div class="saldo-row">
        <div class="saldo-card rpta">
            <div class="s-label">Total RPTAG (Target Tagihan)</div>
            <div class="s-value">Rp {{ number_format($summary['total_rptag'], 0, ',', '.') }}</div>
            <div class="s-bar"><div class="s-fill" style="width:100%;background:var(--c-border)"></div></div>
        </div>
        <div class="saldo-card tagih">
            <div class="s-label">Total Berhasil Ditagih</div>
            <div class="s-value">Rp {{ number_format($summary['total_tagih'], 0, ',', '.') }}</div>
            <div class="s-bar"><div class="s-fill" style="width:{{ min($pctTagih,100) }}%"></div></div>
        </div>
        <div class="saldo-card sisa">
            <div class="s-label">Sisa Saldo Belum Tertagih</div>
            <div class="s-value">Rp {{ number_format($summary['total_sisa'], 0, ',', '.') }}</div>
            <div class="s-bar"><div class="s-fill" style="width:{{ min($pctSisa,100) }}%"></div></div>
        </div>
    </div>

    {{-- ── Chart ────────────────────────────────────────────────────────── --}}
    @if(count($chartData) > 0)
    <div class="chart-card">
        <h3>Perbandingan Kinerja Petugas (%)</h3>
        <div class="chart-wrap">
            <canvas id="kinerjaChart"></canvas>
        </div>
    </div>
    @endif

    {{-- ── Ranking table ─────────────────────────────────────────────────── --}}
    <div class="table-card">
        <div class="table-card-header">
            <h3>Ranking Kinerja Petugas</h3>
            <span style="font-size: 0.9375rem;color:var(--c-muted)">
                {{ count($kinerja) }} petugas · Bulan {{ \Carbon\Carbon::createFromFormat('Y-m',$bulan)->translatedFormat('F Y') }}
            </span>
        </div>

        @if($kinerja->isEmpty())
        <div class="empty-state">
            <svg width="52" height="52" fill="none" viewBox="0 0 24 24"><path d="M9 17H7A5 5 0 017 7h1M15 7h1a5 5 0 010 10h-1M8 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <p>Tidak ada data untuk bulan dan filter ini.</p>
        </div>
        @else
        <div class="tbl-wrap">
            <table class="rank-table">
                <thead>
                    <tr>
                        <th style="width:50px">Rank</th>
                        <th>Petugas</th>
                        <th>Wilayah</th>
                        <th class="right">Target</th>
                        <th class="right">Sudah</th>
                        <th class="right">Belum</th>
                        <th>% Kinerja</th>
                        <th class="right">RPTA</th>
                        <th class="right">Tertagih</th>
                        <th class="right">Sisa Saldo</th>
                        <th>Status</th>
                        <th style="width:80px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kinerja as $i => $p)
                    @php
                        $rank   = $i + 1;
                        $rClass = $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : 'normal'));
                        $pClass = $p->persen >= 80 ? 'green' : ($p->persen >= 60 ? 'yellow' : 'red');
                        $pPill  = strtolower($p->kategori); // baik / cukup / kurang
                    @endphp
                    <tr>
                        <td><span class="rank-badge {{ $rClass }}">{{ $rank }}</span></td>
                        <td>
                            <div style="font-weight:600">{{ $p->nama }}</div>
                            <div style="font-size: 0.905rem;color:var(--c-muted);font-family:var(--mono)">{{ $p->kode_petugas }}</div>
                        </td>
                        <td>{{ $p->wilayah }}</td>
                        <td class="right mono">{{ number_format($p->total_ditagih) }}</td>
                        <td class="right mono" style="color:var(--c-success);font-weight:600">{{ number_format($p->sudah_tagih) }}</td>
                        <td class="right mono" style="color:var(--c-danger)">{{ number_format($p->belum_tagih) }}</td>
                        <td class="progress-cell">
                            <div class="progress-wrap">
                                <div class="progress-bar">
                                    <div class="progress-fill {{ $pClass }}" style="width:{{ $p->persen }}%"></div>
                                </div>
                                <span class="progress-pct {{ $pClass }}">{{ $p->persen }}%</span>
                            </div>
                        </td>
                        <td class="right mono" style="font-size: 0.925rem">{{ number_format($p->total_rptag, 0, ',', '.') }}</td>
                        <td class="right mono" style="font-size: 0.925rem;color:var(--c-success)">{{ number_format($p->total_tagih, 0, ',', '.') }}</td>
                        <td class="right mono" style="font-size: 0.925rem;color:var(--c-danger)">{{ number_format($p->sisa_saldo, 0, ',', '.') }}</td>
                        <td><span class="pill {{ $pPill }}">{{ $p->kategori }}</span></td>
                        <td>
                            <a href="{{ route('monitoring.detail', ['id' => $p->id, 'bulan' => $bulan]) }}"
                               class="btn btn-outline btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const raw = @json($chartData);
    if (!raw || raw.length === 0) return;

    const labels  = raw.map(r => r.nama);
    const perens  = raw.map(r => r.persen);
    const sudahs  = raw.map(r => r.sudah);
    const belums  = raw.map(r => r.belum);

    const colors = perens.map(p =>
        p >= 80 ? '#16A34A' : p >= 60 ? '#D97706' : '#DC2626'
    );

    const ctx = document.getElementById('kinerjaChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: '% Kinerja',
                    data: perens,
                    backgroundColor: colors.map(c => c + '33'),
                    borderColor: colors,
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    label: 'Sudah Ditagih',
                    data: sudahs,
                    type: 'line',
                    borderColor: '#2563EB',
                    backgroundColor: '#2563EB22',
                    borderWidth: 2,
                    pointBackgroundColor: '#2563EB',
                    pointRadius: 4,
                    tension: 0.3,
                    yAxisID: 'y2',
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12 }, boxWidth: 12 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.dataset.label === '% Kinerja') return ' Kinerja: ' + ctx.raw + '%';
                            return ' ' + ctx.dataset.label + ': ' + ctx.raw + ' pelanggan';
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear', position: 'left',
                    min: 0, max: 100,
                    ticks: { callback: v => v + '%', font: { size: 11 } },
                    grid: { color: '#F1F5F9' },
                },
                y2: {
                    type: 'linear', position: 'right',
                    min: 0,
                    ticks: { font: { size: 11 } },
                    grid: { drawOnChartArea: false },
                },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } }
            }
        }
    });
})();
</script>
@endpush
