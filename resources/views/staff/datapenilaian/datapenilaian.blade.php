@extends('layouts.app')
@section('title', 'Data Penilaian')

@push('styles')
<style>
    :root {
        --c-primary:    #005baa;
        --c-primary-dk: #004080;
        --c-border:     #E2E8F0;
        --c-surface:    #F8FAFC;
        --c-text:       #1E293B;
        --c-muted:      #64748B;
        --c-light:      #94A3B8;
        --radius:       12px;
        --radius-sm:    8px;
        --shadow:       0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
        --mono:         'Courier New', monospace;
    }

    .page-wrap  { max-width: 1100px; padding: 24px 20px 48px; }
    .page-hdr   { margin-bottom: 20px; }
    .page-hdr h1 { font-size: 1.375rem; font-weight: 700; margin: 0 0 2px; }
    .page-hdr p  { font-size: 1rem; color: var(--c-muted); margin: 0; }

    .main-card {
        background: #fff; border: 1px solid var(--c-border);
        border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
    }

    /* ── TAB BAR ── */
    .tab-bar {
        display: flex; gap: 6px; padding: 12px 16px;
        background: var(--c-surface); border-bottom: 1px solid var(--c-border);
    }
    .tab-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 7px 16px; font-size: 0.9375rem; font-weight: 500;
        color: var(--c-muted); background: transparent;
        border: 1px solid transparent; border-radius: 99px;
        cursor: pointer; transition: all .15s; white-space: nowrap;
    }
    .tab-btn:hover  { background: #fff; color: var(--c-text); border-color: var(--c-border); }
    .tab-btn.active {
        background: #fff; color: var(--c-text); font-weight: 600;
        border-color: var(--c-border); box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .tab-btn svg   { width: 14px; height: 14px; }
    .tab-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px; padding: 0 5px;
        border-radius: 99px; font-size: 12px; font-weight: 700;
        background: #E2E8F0; color: #475569;
    }

    /* ── FILTER BAR ── */
    .filter-bar {
        background: var(--c-surface); border-bottom: 1px solid var(--c-border);
        padding: 10px 18px;
        display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;
    }
    .fg { display: flex; flex-direction: column; gap: 4px; }
    .fg label {
        font-size: 0.825rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: var(--c-muted);
    }
    .fg input, .fg select {
        height: 34px; padding: 0 10px;
        border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        font-size: 0.9375rem; color: var(--c-text); background: #fff;
        outline: none; transition: border-color .15s; min-width: 150px;
    }
    .fg input:focus, .fg select:focus { border-color: var(--c-primary); }
    .btn-filter {
        height: 34px; padding: 0 16px; border-radius: var(--radius-sm);
        background: var(--c-primary); color: #fff; border: none;
        font-size: 0.9375rem; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: background .15s; white-space: nowrap;
    }
    .btn-filter:hover { background: var(--c-primary-dk); }
    .btn-filter svg   { width: 13px; height: 13px; }
    .btn-reset {
        height: 34px; padding: 0 12px; border-radius: var(--radius-sm);
        border: 1px solid var(--c-border); background: #fff;
        font-size: 0.9375rem; color: var(--c-muted);
        text-decoration: none; display: inline-flex; align-items: center;
        transition: background .15s; cursor: pointer;
    }
    .btn-reset:hover { background: var(--c-surface); color: var(--c-text); }

    /* ── PANE ── */
    .tab-pane        { display: none; }
    .tab-pane.active { display: block; }
    .pane-body       { padding: 24px 26px 28px; }
    .sec-label {
        font-size: 0.825rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .07em; color: var(--c-muted); margin: 0 0 16px;
    }

    /* ── STAT CHIPS ── */
    .stat-row  { display: flex; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    .stat-chip {
        flex: 1; min-width: 110px;
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); padding: 11px 15px;
    }
    .stat-chip-lbl { font-size: 0.825rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); margin-bottom: 4px; }
    .stat-chip-val { font-size: 1.375rem; font-weight: 700; color: var(--c-text); line-height: 1; }
    .stat-chip-val.sm     { font-size: 1rem; padding-top: 3px; }
    .stat-chip-val.ringan { color: #065F46; }
    .stat-chip-val.sedang { color: #92400E; }
    .stat-chip-val.berat  { color: #9A3412; }
    .stat-chip-val.kritis { color: #991B1B; }

    /* ── BOBOT STRIP ── */
    .bobot-strip {
        display: flex; margin-bottom: 18px;
        border: 1px solid var(--c-border); border-radius: var(--radius-sm); overflow: hidden;
    }
    .bobot-cell {
        flex: 1; padding: 10px 14px;
        border-right: 1px solid var(--c-border); background: var(--c-surface);
    }
    .bobot-cell:last-child { border-right: none; }
    .bobot-cell-lbl { font-size: 0.825rem; color: var(--c-muted); font-weight: 600; margin-bottom: 3px; }
    .bobot-cell-val { font-weight: 700; color: var(--c-primary); font-family: var(--mono); }
    .bobot-cell-tipe {
        font-size: 0.825rem; display: inline-block; margin-top: 2px;
        padding: 1px 7px; border-radius: 99px; font-weight: 600;
    }
    .tipe-b { background: #DBEAFE; color: #1E40AF; }
    .tipe-c { background: #FEE2E2; color: #991B1B; }

    /* ── TABLE ── */
    .tbl-wrap {
        border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        overflow: auto; margin-bottom: 14px;
    }
    .penilaian-tbl { width: 100%; border-collapse: collapse; font-size: 1rem; }
    .penilaian-tbl thead tr { background: var(--c-surface); border-bottom: 2px solid var(--c-border); }
    .penilaian-tbl th {
        padding: 9px 13px; text-align: left;
        font-size: 0.825rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: var(--c-muted); white-space: nowrap;
    }
    .penilaian-tbl th.right { text-align: right; }
    .penilaian-tbl td { padding: 9px 13px; border-bottom: 1px solid #F1F5F9; font-size: 1rem; }
    .penilaian-tbl td.right { text-align: right; }
    .penilaian-tbl td.mono  { font-family: var(--mono); font-size: 0.945rem; }
    .penilaian-tbl tr:last-child td { border-bottom: none; }
    .penilaian-tbl tr:hover td { background: #FAFAFA; }

    /* ── KOLOM GROUP HEADER ── */
    .penilaian-tbl th.group-hdr {
        text-align: center; background: #F1F5F9;
        border-left: 2px solid var(--c-border);
        border-right: 2px solid var(--c-border);
        color: var(--c-primary); letter-spacing: .04em;
    }
    .penilaian-tbl td.group-sep {
        border-left: 2px solid #F1F5F9;
    }

    /* tunggakan badge */
    .tb { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 99px; font-size: 0.875rem; font-weight: 600; }
    .tb-1 { background: #D1FAE5; color: #065F46; }
    .tb-2 { background: #FEF9C3; color: #92400E; }
    .tb-3 { background: #FFEDD5; color: #9A3412; }
    .tb-4 { background: #FEE2E2; color: #991B1B; }

    /* normalisasi bar */
    .norm-bar-wrap { display: flex; align-items: center; gap: 8px; min-width: 90px; }
    .norm-bar-bg   { flex: 1; height: 5px; border-radius: 99px; background: #E2E8F0; overflow: hidden; }
    .norm-bar-fill { height: 100%; border-radius: 99px; background: var(--c-primary); }
    .norm-val      { font-size: 0.875rem; font-weight: 600; color: var(--c-primary); font-family: var(--mono); white-space: nowrap; }

    /* total nilai chip */
    .vi-chip {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 3px 10px; border-radius: 99px;
        font-size: 0.875rem; font-weight: 700; font-family: var(--mono);
        background: #EFF6FF; color: var(--c-primary);
        border: 1px solid #BFDBFE;
    }

    /* ── PAGINATION ── */
    .pg-row { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; flex-wrap: wrap; gap: 8px; }
    .pg-info { font-size: 0.875rem; color: var(--c-muted); }

    /* ── EMPTY ── */
    .empty-box {
        padding: 52px 24px; text-align: center;
        border: 1px dashed var(--c-border); border-radius: var(--radius-sm);
    }
    .empty-box svg { opacity: .25; display: block; margin: 0 auto 10px; }
    .empty-box p   { font-size: 1rem; color: var(--c-muted); margin: 0; }

    /* ── BULAN CARD GRID ── */
    .bulan-card {
        padding: 10px 16px; min-width: 100px; text-align: center;
        border: 1.5px solid var(--c-border); border-radius: var(--radius-sm);
        background: #fff; transition: all .15s; user-select: none;
    }
    .bulan-card:hover { border-color: var(--c-primary); background: #F0F7FF; }
    .bulan-card.selected {
        border-color: var(--c-primary); background: #EFF6FF;
        box-shadow: 0 0 0 3px rgba(0,91,170,.1);
    }
    .bulan-card-month { font-size: 1rem; font-weight: 700; color: var(--c-text); line-height: 1; }
    .bulan-card-year  { font-size: 0.875rem; color: var(--c-muted); margin-top: 2px; }
    .bulan-card-count { font-size: 0.825rem; color: var(--c-primary); font-weight: 600; margin-top: 4px; font-family: var(--mono); }

    /* ── GENERATE FORM ── */
    .gen-form-card {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); padding: 22px 24px; margin-bottom: 14px;
    }
    .gen-steps { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
    .gen-step {
        display: flex; align-items: center; gap: 7px;
        padding: 7px 13px; background: #fff;
        border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        font-size: 0.9375rem; color: var(--c-text);
    }
    .gen-step-num {
        width: 20px; height: 20px; background: var(--c-text); color: #fff;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }

    /* context info */
    .ctx-info {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 7px 12px; border-radius: var(--radius-sm);
        font-size: 0.9375rem; border: 1px solid var(--c-border);
        background: var(--c-surface); color: var(--c-muted);
        transition: all .2s; margin-bottom: 18px;
    }
    .ctx-info svg { width: 13px; height: 13px; flex-shrink: 0; }

    /* current data strip */
    .current-strip {
        display: flex; gap: 20px; flex-wrap: wrap; align-items: center;
        padding: 10px 16px; margin-top: 14px;
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); font-size: 0.9375rem; color: var(--c-muted);
    }
    .current-strip strong { color: var(--c-text); }
    .current-strip .div   { color: var(--c-border); }

    .warn-box {
        margin-top: 14px; padding: 11px 16px;
        background: #FFFBEB; border: 1px solid #FCD34D;
        border-radius: var(--radius-sm); font-size: 0.9375rem; color: #92400E;
    }

    /* ── BUTTONS ── */
    .btn-dark {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 20px; border-radius: var(--radius-sm);
        border: none; background: var(--c-text); color: #fff;
        font-size: 1rem; font-weight: 600; cursor: pointer; transition: background .15s;
        height: 38px;
    }
    .btn-dark:hover    { background: #374151; }
    .btn-dark:disabled { opacity: .4; cursor: not-allowed; }
    .btn-dark svg      { width: 15px; height: 15px; }

    /* ── LOADING ── */
    .loading-overlay { display: none; position: fixed; inset: 0; background: rgba(249,250,251,.85); align-items: center; justify-content: center; z-index: 9999; }
    .loading-overlay.show { display: flex; }
    .loading-card { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius); padding: 28px 44px; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,.08); }
    .loading-card p { font-size: 1rem; color: var(--c-muted); margin: 12px 0 0; }
    .spin { width: 32px; height: 32px; margin: 0 auto; border: 2.5px solid var(--c-border); border-top-color: var(--c-text); border-radius: 50%; animation: spin .75s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── LEGEND ── */
    .legend-strip {
        display: flex; gap: 14px; flex-wrap: wrap; align-items: center;
        padding: 8px 14px; margin-bottom: 14px;
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); font-size: 0.875rem; color: var(--c-muted);
    }
    .legend-item { display: flex; align-items: center; gap: 5px; }
    .legend-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

    @media (max-width: 640px) {
        .pane-body  { padding: 16px 14px 20px; }
        .bobot-strip { flex-wrap: wrap; }
        .bobot-cell  { border-right: none; border-bottom: 1px solid var(--c-border); }
        .bobot-cell:last-child { border-bottom: none; }
        .filter-bar { flex-direction: column; }
        .fg input, .fg select { min-width: 100%; }
        .norm-bar-wrap { min-width: 70px; }
    }
</style>
@endpush

@section('content')

{{-- Tampilan loading overlay saat generate data penilaian --}}
<div class="loading-overlay" id="loading">
    <div class="loading-card">
        <div class="spin"></div>
        <p>Menghitung data penilaian...</p>
    </div>
</div>

<div class="px-4 py-4">

    {{-- Header halaman data penilaian --}}
    <div class="page-hdr">
        <h1>Data Penilaian</h1>
        <p>Matriks keputusan SAW · C1–C4 per pelanggan beserta hasil normalisasi</p>
    </div>

    {{-- Menampilkan notifikasi sukses atau error --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small mb-3">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small mb-3">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="main-card">

        {{-- Header kartu utama dan tombol Generate Penilaian --}}
        <div class="tab-bar" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 18px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; color: var(--c-text);">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                    <line x1="3" y1="9"  x2="21" y2="9"  stroke="currentColor" stroke-width="2"/>
                    <line x1="3" y1="15" x2="21" y2="15" stroke="currentColor" stroke-width="2"/>
                    <line x1="9" y1="9"  x2="9"  y2="21" stroke="currentColor" stroke-width="2"/>
                </svg>
                Matriks &amp; Normalisasi
                <span class="tab-badge">{{ $stats['total_pelanggan'] }}</span>
            </div>

            <form method="POST" action="{{ route('datapenilaian.generate') }}" style="margin: 0; display: flex; gap: 8px; align-items: center;" onsubmit="document.getElementById('loading').classList.add('show'); document.getElementById('btn-generate').disabled=true;">
                @csrf
                <input type="month" name="periode" required value="{{ request('periode') ?? date('Y-m') }}" style="height:34px; border:1px solid var(--c-border); border-radius:var(--radius-sm); padding:0 10px; font-size:0.9375rem;">
                <button id="btn-generate" type="submit" class="btn-dark" style="height: 34px; padding: 0 16px; font-size: 0.9375rem;">
                    <svg fill="none" viewBox="0 0 24 24">
                        <polyline points="23 4 23 10 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Generate Penilaian
                </button>
            </form>
        </div>
        {{-- Form pencarian, filter, dan pengurutan data penilaian --}}
        <div class="filter-bar" id="filter-section">
            <form method="GET" action="{{ request()->url() }}"
                  style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;width:100%">

                <div class="fg">
                    <label>Periode</label>
                    <input type="month" name="periode" value="{{ request('periode') }}">
                </div>

                <div class="fg">
                    <label>Cari IDPEL</label>
                    <input type="text" name="search"
                           value="{{ request('search') }}"
                           placeholder="Masukkan IDPEL...">
                </div>

                {{-- Filter berdasarkan kategori prioritas SAW --}}
                <div class="fg">
                    <label>Kategori Prioritas</label>
                    <select name="kategori">
                        <option value="">Semua</option>
                        <option value="Sangat Prioritas" {{ request('kategori')=='Sangat Prioritas' ? 'selected' : '' }}>🔴 Sangat Prioritas (≥ 0.80)</option>
                        <option value="Prioritas" {{ request('kategori')=='Prioritas' ? 'selected' : '' }}>🟠 Prioritas (≥ 0.60)</option>
                        <option value="Sedang" {{ request('kategori')=='Sedang' ? 'selected' : '' }}>🟡 Sedang (≥ 0.40)</option>
                        <option value="Rendah" {{ request('kategori')=='Rendah' ? 'selected' : '' }}>🟢 Rendah (&lt; 0.40)</option>
                    </select>
                </div>

                <div class="fg">
                    <label>Urutkan</label>
                    <select name="sort">
                        <option value=""        {{ request('sort')==''        ? 'selected' : '' }}>Default (IDPEL)</option>
                        <option value="c1_desc" {{ request('sort')=='c1_desc' ? 'selected' : '' }}>C1 Terbesar (Historis)</option>
                        <option value="c2_desc" {{ request('sort')=='c2_desc' ? 'selected' : '' }}>C2 Terbesar (Tunggakan)</option>
                        <option value="c3_desc" {{ request('sort')=='c3_desc' ? 'selected' : '' }}>C3 Terlama (Lembar)</option>
                        <option value="c4_desc" {{ request('sort')=='c4_desc' ? 'selected' : '' }}>C4 Terbesar (Tagihan)</option>
                        <option value="vi_desc" {{ request('sort')=='vi_desc' ? 'selected' : '' }}>Vi Tertinggi (Prioritas)</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter">
                    <svg fill="none" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Terapkan
                </button>

                @if(request('search') || request('kategori') || request('sort'))
                <a href="{{ request()->url() }}" class="btn-reset">Reset</a>
                @endif

            </form>
        </div>

        {{-- Bagian body utama matriks & normalisasi --}}
        <div>
            <div class="pane-body">

                {{-- Menampilkan ringkasan statistik penilaian --}}
                <div class="stat-row">

            <div class="stat-chip">
                <div class="stat-chip-lbl">
                    Total Pelanggan
                </div>

                <div class="stat-chip-val">
                   {{ number_format($summary['total_pelanggan'],0,',','.') }}
                </div>
            </div>

            <div class="stat-chip">
                <div class="stat-chip-lbl">
                    Total Tunggakan
                </div>

                <div class="stat-chip-val">
                    Rp {{ number_format($summary['total_tunggakan'],0,',','.') }}
                </div>
            </div>

            <div class="stat-chip">
                <div class="stat-chip-lbl">
                    Rata-rata Lembar
                </div>

                <div class="stat-chip-val">
                   {{ round($summary['avg_lembar'],1) }}
                </div>
            </div>

            <div class="stat-chip">
                <div class="stat-chip-lbl">
                    Total RPBK
                </div>

                <div class="stat-chip-val">
                 Rp {{ number_format($summary['total_rpbk'] ?? 0,0,',','.') }}
                </div>
            </div>

            <div class="stat-chip">
                <div class="stat-chip-lbl">
                    Avg Historis
                </div>

                <div class="stat-chip-val">
                    {{ round($summary['avg_historis'] ?? 0, 1) }} x
                </div>
            </div>

        </div>

                        {{-- Menampilkan strip bobot masing-masing kriteria --}}
                        @if($kriteria->count())
                        <div class="bobot-strip">
                            @foreach($kriteria as $k)
                            <div class="bobot-cell">
                                <div class="bobot-cell-lbl">C{{ $loop->iteration }} — {{ $k->nama }}</div>
                                <div class="bobot-cell-val">{{ $k->bobot }}%</div>
                                <div class="bobot-cell-tipe {{ $k->tipe == 'benefit' ? 'tipe-b' : 'tipe-c' }}">
                                    {{ ucfirst($k->tipe) }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                @endif

                {{-- Legenda keterangan kolom matriks C1 - C4 --}}
                <div class="legend-strip">
                    <span style="font-weight:600;color:var(--c-text);font-size: 0.875rem">Keterangan kolom:</span>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#005baa"></div>
                        <span><strong>C1</strong> = Historis Tunggakan (kali) — benefit</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#7C3AED"></div>
                        <span><strong>C2</strong> = Besar Tunggakan (Rp) — benefit</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#0891B2"></div>
                        <span><strong>C3</strong> = Lama Tunggakan (bulan/lembar) — benefit</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#059669"></div>
                        <span><strong>C4</strong> = Total Tagihan (Rp) — benefit</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#D97706"></div>
                        <span><strong>Vi</strong> = Total Nilai SAW</span>
                    </div>
                </div>

                {{-- Tabel daftar hasil penilaian dan nilai Vi --}}
                @if($penilaian->isEmpty())
                <div class="empty-box">
                    <svg width="36" height="36" fill="none" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    <p>
                        @if(request('search') || request('kategori'))
                            Tidak ada data yang cocok dengan filter ini.
                        @else
                            Belum ada data. Klik tab <strong>Generate</strong> untuk menghitung.
                        @endif
                    </p>
                </div>
                @else
                <div class="tbl-wrap">
                    <table class="penilaian-tbl">
                        <thead>
                            {{-- Baris group header --}}
                            <tr>
                                <th rowspan="2" style="width:36px;vertical-align:bottom">#</th>
                                <th rowspan="2" style="vertical-align:bottom">Periode</th>
                                <th rowspan="2" style="vertical-align:bottom">IDPEL</th>
                                <th colspan="2" class="group-hdr" title="C1 — Historis Tunggakan (kali) · Benefit">C1 — Historis (kali)</th>
                                <th colspan="2" class="group-hdr" title="C2 — Besar Tunggakan (Rp) · Benefit">C2 — Tunggakan (Rp)</th>
                                <th colspan="2" class="group-hdr" title="C3 — Lama Tunggakan (bulan) · Benefit">C3 — Lembar (bln)</th>
                                <th colspan="2" class="group-hdr" title="C4 — Total Tagihan (Rp) · Benefit">C4 — Tagihan (Rp)</th>
                                <th rowspan="2" style="vertical-align:bottom">Kategori</th>
                                <th rowspan="2" class="right" style="vertical-align:bottom" title="Total Nilai SAW">Vi</th>
                            </tr>
                            <tr>
                                <th class="right" style="font-size: 0.775rem">Raw</th>
                                <th class="right" style="font-size: 0.775rem">Norm</th>
                                <th class="right group-sep" style="font-size: 0.775rem">Raw</th>
                                <th class="right" style="font-size: 0.775rem">Norm</th>
                                <th class="right group-sep" style="font-size: 0.775rem">Raw</th>
                                <th class="right" style="font-size: 0.775rem">Norm</th>
                                <th class="right group-sep" style="font-size: 0.775rem">Raw</th>
                                <th class="right" style="font-size: 0.775rem">Norm</th>
                            </tr>
                        </thead>
                        <tbody>
                        {{-- Looping baris data penilaian --}}
                        @foreach($penilaian as $row)
                        <tr>
                            {{-- No urut --}}
                            <td class="mono" style="color:var(--c-muted);font-size: 0.875rem">
                                {{ ($penilaian->currentPage() - 1) * $penilaian->perPage() + $loop->iteration }}
                            </td>

                            {{-- Periode --}}
                            <td class="mono" style="font-size: 0.875rem"><span class="badge" style="background:#E2E8F0; color:#1E293B; padding:2px 6px; border-radius:4px;">{{ $row->periode }}</span></td>

                            {{-- IDPEL --}}
                            <td class="mono">{{ $row->idpel }}</td>

                            {{-- C1: Historis Tunggakan (kali) --}}
                            <td class="right mono">
                                {{ number_format($row->c1, 0) }} x
                            </td>
                            <td class="right">
                                <div class="norm-bar-wrap">
                                    <div class="norm-bar-bg">
                                        <div class="norm-bar-fill"
                                             style="width:{{ min(($row->norm_c1 ?? 0)*100, 100) }}%;background:#005baa"></div>
                                    </div>
                                    <span class="norm-val" style="color:#005baa">
                                        {{ number_format(($row->norm_c1 ?? 0)*100, 1) }}%
                                    </span>
                                </div>
                            </td>

                            {{-- C2: Besar Tunggakan (Rp) --}}
                            <td class="right mono group-sep">
                                Rp {{ number_format($row->c2, 0, ',', '.') }}
                            </td>
                            <td class="right">
                                <div class="norm-bar-wrap">
                                    <div class="norm-bar-bg">
                                        <div class="norm-bar-fill"
                                             style="width:{{ min(($row->norm_c2 ?? 0)*100, 100) }}%;background:#7C3AED"></div>
                                    </div>
                                    <span class="norm-val" style="color:#7C3AED">
                                        {{ number_format(($row->norm_c2 ?? 0)*100, 1) }}%
                                    </span>
                                </div>
                            </td>

                            {{-- C3: Lama Tunggakan (lembar/bulan) --}}
                            <td class="right mono group-sep">
                                {{ number_format($row->c3, 0) }} bln
                            </td>
                            <td class="right">
                                <div class="norm-bar-wrap">
                                    <div class="norm-bar-bg">
                                        <div class="norm-bar-fill"
                                             style="width:{{ min(($row->norm_c3 ?? 0)*100, 100) }}%;background:#0891B2"></div>
                                    </div>
                                    <span class="norm-val" style="color:#0891B2">
                                        {{ number_format(($row->norm_c3 ?? 0)*100, 1) }}%
                                    </span>
                                </div>
                            </td>

                            {{-- C4: Total Tagihan (Rp) --}}
                            <td class="right mono group-sep">
                                Rp {{ number_format($row->c4, 0, ',', '.') }}
                            </td>
                            <td class="right">
                                <div class="norm-bar-wrap">
                                    <div class="norm-bar-bg">
                                        <div class="norm-bar-fill"
                                             style="width:{{ min(($row->norm_c4 ?? 0)*100, 100) }}%;background:#059669"></div>
                                    </div>
                                    <span class="norm-val" style="color:#059669">
                                        {{ number_format(($row->norm_c4 ?? 0)*100, 1) }}%
                                    </span>
                                </div>
                            </td>

                            {{-- Kategori Prioritas SAW --}}
                            <td>
                                @php
                                    $katVal = $row->kategori ?? 'Rendah';
                                    $katStyles = [
                                        'Sangat Prioritas' => 'tb-4',
                                        'Prioritas'        => 'tb-3',
                                        'Sedang'           => 'tb-2',
                                        'Rendah'           => 'tb-1',
                                    ];
                                @endphp
                                <span class="tb {{ $katStyles[$katVal] ?? 'tb-1' }}">
                                    {{ $katVal }}
                                </span>
                            </td>

                            {{-- Vi: Total Nilai SAW --}}
                            <td class="right">
                                <span class="vi-chip">
                                    {{ number_format($row->total_nilai ?? 0, 4) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Link pagination tabel --}}
                <div class="pg-row">
                    <span class="pg-info">
                        Menampilkan {{ $penilaian->firstItem() }}–{{ $penilaian->lastItem() }}
                        dari {{ number_format($penilaian->total()) }} pelanggan
                        @if(request('search') || request('kat_c4'))
                            <span style="color:var(--c-primary)">(difilter)</span>
                        @endif
                    </span>
                    <div>{{ $penilaian->links() }}</div>
                </div>
                @endif

            </div>
        </div>

    </div>{{-- /main-card --}}
</div>
@endsection

@push('scripts')
<script>

</script>
@endpush
