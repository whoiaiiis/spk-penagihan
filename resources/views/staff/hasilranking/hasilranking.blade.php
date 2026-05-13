@extends('layouts.app')
@section('title', 'Hasil Ranking')

@push('styles')
<style>
    :root {
        --c-primary:    #005baa;
        --c-primary-dk: #004080;
        --c-border:     #E2E8F0;
        --c-surface:    #F8FAFC;
        --c-text:       #1E293B;
        --c-muted:      #64748B;
        --radius:       12px;
        --radius-sm:    8px;
        --shadow:       0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
        --mono:         'Courier New', monospace;
    }

    .page-wrap   { max-width: 1100px; padding: 24px 20px 48px; }
    .page-hdr    { margin-bottom: 20px; }
    .page-hdr h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 2px; }
    .page-hdr p  { font-size: .875rem; color: var(--c-muted); margin: 0; }

    .main-card {
        background: #fff; border: 1px solid var(--c-border);
        border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
    }

    /* ── TAB BAR ── */
    .tab-bar {
        display: flex; gap: 6px; padding: 12px 16px;
        background: var(--c-surface); border-bottom: 1px solid var(--c-border);
        flex-wrap: wrap;
    }
    .tab-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 7px 16px; font-size: .8125rem; font-weight: 500;
        color: var(--c-muted); background: transparent;
        border: 1px solid transparent; border-radius: 99px;
        cursor: pointer; transition: all .15s; white-space: nowrap;
    }
    .tab-btn:hover  { background: #fff; color: var(--c-text); border-color: var(--c-border); }
    .tab-btn.active {
        background: #fff; color: var(--c-text); font-weight: 600;
        border-color: var(--c-border); box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .tab-btn svg  { width: 14px; height: 14px; }
    .tab-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px; padding: 0 5px;
        border-radius: 99px; font-size: 10px; font-weight: 700;
        background: #D1FAE5; color: #065F46;
    }

    .tab-pane        { display: none; }
    .tab-pane.active { display: block; }
    .pane-body       { padding: 24px 26px 28px; }
    .sec-label {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .07em; color: var(--c-muted); margin: 0 0 14px;
    }

    /* ── FILTER BAR ── */
    .filter-bar {
        background: var(--c-surface); border-bottom: 1px solid var(--c-border);
        padding: 12px 18px;
        display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;
    }
    .fg { display: flex; flex-direction: column; gap: 4px; }
    .fg label {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: var(--c-muted);
    }
    .fg select, .fg input {
        height: 34px; padding: 0 10px;
        border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        font-size: .8125rem; color: var(--c-text); background: #fff;
        outline: none; transition: border-color .15s; min-width: 140px;
    }
    .fg select:focus, .fg input:focus { border-color: var(--c-primary); }

    .btn-filter {
        height: 34px; padding: 0 16px; border-radius: var(--radius-sm);
        border: 1px solid var(--c-border); background: #fff;
        font-size: .8125rem; color: var(--c-muted); cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all .15s; white-space: nowrap;
    }
    .btn-filter:hover { background: var(--c-surface); color: var(--c-text); }
    .btn-filter svg   { width: 13px; height: 13px; }
    .btn-filter-primary {
        background: var(--c-primary); color: #fff; border-color: var(--c-primary);
    }
    .btn-filter-primary:hover { background: var(--c-primary-dk); color: #fff; }
    .btn-reset {
        height: 34px; padding: 0 12px; border-radius: var(--radius-sm);
        border: 1px solid var(--c-border); background: #fff;
        font-size: .8125rem; color: var(--c-muted); cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: background .15s;
    }
    .btn-reset:hover { background: var(--c-surface); }

    /* ── RISIKO PILLS untuk filter aktif ── */
    .filter-active-bar {
        padding: 8px 18px; background: #fff;
        border-bottom: 1px solid var(--c-border);
        display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
    }
    .filter-active-bar span { font-size: .75rem; color: var(--c-muted); }
    .filter-chip {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 99px; font-size: .75rem; font-weight: 600;
        background: #E6F1FB; color: #0C447C;
        border: 1px solid #B5D4F4;
    }

    /* ── STAT CHIPS ── */
    .stat-row  { display: flex; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    .stat-chip {
        flex: 1; min-width: 130px;
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); padding: 11px 15px;
    }
    .stat-chip-lbl { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); margin-bottom: 4px; }
    .stat-chip-val { font-size: 1.25rem; font-weight: 700; color: var(--c-text); line-height: 1; }
    .stat-chip-val.sm { font-size: .875rem; padding-top: 3px; }
    .stat-chip-val.danger  { color: #DC2626; }
    .stat-chip-val.warning { color: #D97706; }
    .stat-chip-val.success { color: #16A34A; }

    /* ── FORMULA STRIP ── */
    .formula-strip {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); padding: 10px 16px;
        font-size: .8125rem; color: var(--c-muted);
        margin-bottom: 16px; line-height: 1.8;
        display: flex; gap: 20px; flex-wrap: wrap; align-items: center;
    }
    .formula-strip code {
        background: #E2E8F0; padding: 2px 7px; border-radius: 5px;
        font-size: .75rem; color: var(--c-text); font-family: var(--mono);
    }
    .formula-strip strong { color: var(--c-text); }

    /* ── TABLE ── */
    .tbl-wrap {
        border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        overflow: auto; margin-bottom: 12px;
    }
    .result-tbl { width: 100%; border-collapse: collapse; font-size: .875rem; }
    .result-tbl thead tr { background: var(--c-surface); border-bottom: 2px solid var(--c-border); }
    .result-tbl th {
        padding: 9px 12px; text-align: left;
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--c-muted); white-space: nowrap;
    }
    .result-tbl th.r { text-align: right; }
    .result-tbl td { padding: 9px 12px; border-bottom: 1px solid #F1F5F9; font-size: .875rem; }
    .result-tbl td.r { text-align: right; }
    .result-tbl td.mono { font-family: var(--mono); font-size: .8rem; }
    .result-tbl tr:last-child td { border-bottom: none; }
    .result-tbl tr:hover td { background: #FAFAFA; }
    .hi { background: #F0FDF4 !important; color: #065F46 !important; font-weight: 600; }

    /* Row warna risiko */
    .row-high   td { border-left: 3px solid #DC2626; }
    .row-medium td { border-left: 3px solid #D97706; }
    .row-low    td { border-left: 3px solid #16A34A; }

    /* ── RANK MEDAL ── */
    .rank-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: var(--radius-sm);
        font-size: .75rem; font-weight: 700;
    }
    .rank-1 { background: #FEF9C3; color: #92400E; border: 1px solid #FCD34D; }
    .rank-2 { background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; }
    .rank-3 { background: #FFF7ED; color: #9A3412; border: 1px solid #FED7AA; }
    .rank-n { background: var(--c-surface); color: var(--c-muted); border: 1px solid var(--c-border); }

    /* ── RISIKO PILL ── */
    .risiko-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 9px; border-radius: 99px; font-size: .75rem; font-weight: 600;
    }
    .risiko-pill svg { width: 10px; height: 10px; }
    .risiko-tinggi  { background: #FEE2E2; color: #991B1B; }
    .risiko-sedang  { background: #FEF9C3; color: #92400E; }
    .risiko-rendah  { background: #D1FAE5; color: #065F46; }

    /* ── VI BAR ── */
    .vi-bar-wrap { display: flex; align-items: center; gap: 8px; }
    .vi-bar-bg   { width: 80px; height: 5px; border-radius: 99px; background: #E2E8F0; overflow: hidden; flex-shrink: 0; }
    .vi-bar-fill { height: 100%; border-radius: 99px; }
    .vi-val      { font-size: .8rem; font-weight: 700; font-family: var(--mono); }

    /* ── EMPTY ── */
    .empty-box {
        padding: 52px 24px; text-align: center;
        border: 1px dashed var(--c-border); border-radius: var(--radius-sm);
    }
    .empty-box svg { opacity: .25; display: block; margin: 0 auto 10px; }
    .empty-box p   { font-size: .875rem; color: var(--c-muted); margin: 0; }

    /* ── LEGEND ── */
    .legend {
        display: flex; gap: 16px; flex-wrap: wrap;
        font-size: .75rem; color: var(--c-muted); margin-bottom: 12px;
        align-items: center;
    }
    .legend-item { display: flex; align-items: center; gap: 6px; }
    .legend-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

    /* ── GENERATE ── */
    .gen-box {
        border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        padding: 32px 24px; text-align: center; background: var(--c-surface);
    }
    .gen-box h3 { font-size: 1rem; font-weight: 700; margin: 0 0 8px; }
    .gen-box p  { font-size: .875rem; color: var(--c-muted); line-height: 1.7; margin: 0 0 22px; }
    .gen-steps  { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 22px; }
    .gen-step {
        display: flex; align-items: center; gap: 7px;
        padding: 7px 13px; background: #fff;
        border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        font-size: .8125rem; color: var(--c-text);
    }
    .gen-step-num {
        width: 20px; height: 20px; background: var(--c-text); color: #fff;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: .625rem; font-weight: 700; flex-shrink: 0;
    }
    .info-box {
        margin-top: 14px; padding: 12px 16px;
        background: #EFF6FF; border: 1px solid #BFDBFE;
        border-radius: var(--radius-sm); font-size: .8125rem; color: #1E40AF;
    }

    /* ── BUTTONS ── */
    .btn-dark {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 22px; border-radius: var(--radius-sm);
        border: none; background: var(--c-text); color: #fff;
        font-size: .875rem; font-weight: 600; cursor: pointer; transition: background .15s;
    }
    .btn-dark:hover    { background: #374151; }
    .btn-dark:disabled { opacity: .4; cursor: not-allowed; }
    .btn-dark svg      { width: 15px; height: 15px; }

    /* ── LOADING ── */
    .loading-overlay { display: none; position: fixed; inset: 0; background: rgba(249,250,251,.85); align-items: center; justify-content: center; z-index: 9999; }
    .loading-overlay.show { display: flex; }
    .loading-card { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius); padding: 28px 44px; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,.08); }
    .loading-card p { font-size: .875rem; color: var(--c-muted); margin: 12px 0 0; }
    .spin { width: 32px; height: 32px; margin: 0 auto; border: 2.5px solid var(--c-border); border-top-color: var(--c-text); border-radius: 50%; animation: spin .75s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 640px) {
        .filter-bar { flex-direction: column; }
        .fg select, .fg input { min-width: 100%; }
        .pane-body { padding: 16px 14px 20px; }
    }
</style>
@endpush

@section('content')

<div class="loading-overlay" id="loading">
    <div class="loading-card">
        <div class="spin"></div>
        <p>Menghitung normalisasi &amp; ranking SAW...</p>
    </div>
</div>

<div class="px-4 py-4">

    <div class="page-hdr">
        <h1>Hasil Ranking</h1>
        <p>Normalisasi SAW · Nilai akhir (Vi) · Peringkat pelanggan berdasarkan risiko tunggakan</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small mb-3">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small mb-3">
        {{ session('error') }}
    </div>
    @endif

    <div class="main-card">

        {{-- ── TAB BAR ── --}}
        <div class="tab-bar">
            <button id="tab-hasil" class="tab-btn">
                <svg fill="none" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Normalisasi &amp; Ranking
                <span class="tab-badge">{{ $stats['total'] }}</span>
            </button>
            <button id="tab-generate" class="tab-btn">
                <svg fill="none" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Generate
            </button>
        </div>

        {{-- ── FILTER BAR (hanya tampil di tab hasil) ── --}}
        <div class="filter-bar" id="filter-section">
            <form method="GET" action="{{ request()->url() }}"
                  style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;width:100%">

                <div class="fg">
                    <label>Cari IDPEL / Nama</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari...">
                </div>

                <div class="fg">
                    <label>Kategori Prioritas</label>
                    <select name="kategori">
                        <option value="">Semua Kategori</option>
                        <option value="Sangat Prioritas" {{ request('kategori')=='Sangat Prioritas' ? 'selected' : '' }}>🔴 Sangat Prioritas (≥ 0.80)</option>
                        <option value="Prioritas" {{ request('kategori')=='Prioritas' ? 'selected' : '' }}>🟠 Prioritas (≥ 0.60)</option>
                        <option value="Sedang" {{ request('kategori')=='Sedang' ? 'selected' : '' }}>🟡 Sedang (≥ 0.40)</option>
                        <option value="Rendah" {{ request('kategori')=='Rendah' ? 'selected' : '' }}>🟢 Rendah (&lt; 0.40)</option>
                    </select>
                </div>

                <div class="fg">
                    <label>Tampilkan</label>
                    <select name="top">
                        <option value=""   {{ request('top')==''   ? 'selected' : '' }}>Semua</option>
                        <option value="10" {{ request('top')=='10' ? 'selected' : '' }}>Top 10</option>
                        <option value="25" {{ request('top')=='25' ? 'selected' : '' }}>Top 25</option>
                        <option value="50" {{ request('top')=='50' ? 'selected' : '' }}>Top 50</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter btn-filter-primary">
                    <svg fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Terapkan
                </button>
                <a href="{{ request()->url() }}" class="btn-reset">Reset</a>

            </form>
        </div>

        {{-- Filter aktif chips --}}
        @if(request('search') || request('kategori') || request('top'))
        <div class="filter-active-bar">
            <span>Filter aktif:</span>
            @if(request('search'))
                <span class="filter-chip">Cari: {{ request('search') }}</span>
            @endif
            @if(request('kategori'))
                <span class="filter-chip">{{ request('kategori') }}</span>
            @endif
            @if(request('top'))
                <span class="filter-chip">Top {{ request('top') }}</span>
            @endif
        </div>
        @endif

        {{-- ══════════ PANE: NORMALISASI & RANKING ══════════ --}}
        <div id="pane-hasil" class="tab-pane">
            <div class="pane-body">

                {{-- Stat chips --}}
                @php
                    $totalSP     = $ranking->filter(fn($r) => ($r->kategori ?? '') === 'Sangat Prioritas')->count();
                    $totalP      = $ranking->filter(fn($r) => ($r->kategori ?? '') === 'Prioritas')->count();
                    $totalSedang = $ranking->filter(fn($r) => ($r->kategori ?? '') === 'Sedang')->count();
                    $totalRendah = $ranking->filter(fn($r) => ($r->kategori ?? '') === 'Rendah')->count();
                @endphp
                <div class="stat-row">
                    <div class="stat-chip">
                        <div class="stat-chip-lbl">Total Ditampilkan</div>
                        <div class="stat-chip-val">{{ $stats['total'] }}</div>
                    </div>
                    <div class="stat-chip">
                        <div class="stat-chip-lbl">Sangat Prioritas</div>
                        <div class="stat-chip-val danger">{{ $totalSP }}</div>
                    </div>
                    <div class="stat-chip">
                        <div class="stat-chip-lbl">Prioritas</div>
                        <div class="stat-chip-val warning">{{ $totalP }}</div>
                    </div>
                    <div class="stat-chip">
                        <div class="stat-chip-lbl">Sedang / Rendah</div>
                        <div class="stat-chip-val success">{{ $totalSedang + $totalRendah }}</div>
                    </div>
                    <div class="stat-chip">
                        <div class="stat-chip-lbl">Terakhir Di-generate</div>
                        <div class="stat-chip-val sm">
                            {{ $stats['generated_at']
                                ? \Carbon\Carbon::parse($stats['generated_at'])->format('d M Y H:i')
                                : '—' }}
                        </div>
                    </div>
                </div>

                @if($ranking->isEmpty())
                <div class="empty-box">
                    <svg width="36" height="36" fill="none" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p>Belum ada data. Klik tab <strong>Generate</strong> untuk menghitung ranking.</p>
                </div>
                @else

                {{-- ── NORMALISASI ── --}}
                <p class="sec-label">Matriks Normalisasi (R)</p>
                <div class="formula-strip">
                    <span>
                        <strong>Benefit</strong> → <code>R = nilai / MAX</code>
                        &nbsp;·&nbsp;
                        <strong>Cost</strong> → <code>R = MIN / nilai</code>
                    </span>
                    @foreach($kriteria as $k)
                    <span>
                        <strong>C{{ $loop->iteration }}</strong> {{ $k->nama }}
                        <code>{{ ucfirst($k->tipe) }}</code>
                    </span>
                    @endforeach
                </div>

                @if(!$normalisasi->isEmpty())
                <div class="tbl-wrap" style="margin-bottom:28px">
                    <table class="result-tbl">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>IDPEL</th>
                                @foreach($kriteria as $k)
                                <th class="r">R{{ $loop->iteration }} — {{ $k->nama }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($normalisasi as $row)
                        <tr>
                            <td class="mono" style="color:var(--c-muted)">{{ $loop->iteration }}</td>
                            <td class="mono">{{ $row->idpel }}</td>
                            <td class="r mono {{ $row->r1 == 1 ? 'hi' : '' }}">{{ number_format($row->r1, 4) }}</td>
                            <td class="r mono {{ $row->r2 == 1 ? 'hi' : '' }}">{{ number_format($row->r2, 4) }}</td>
                            <td class="r mono {{ $row->r3 == 1 ? 'hi' : '' }}">{{ number_format($row->r3, 4) }}</td>
                            <td class="r mono {{ $row->r4 == 1 ? 'hi' : '' }}">{{ number_format($row->r4, 4) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <p style="font-size:.75rem;color:var(--c-muted);margin:-20px 0 28px">
                    <span style="display:inline-block;width:10px;height:10px;background:#D1FAE5;border-radius:2px;margin-right:4px;vertical-align:middle"></span>
                    Nilai 1.0000 = terbaik pada kolom tersebut
                </p>
                @endif

                {{-- ── RANKING AKHIR ── --}}
                <p class="sec-label">Peringkat Akhir (Vi = Σ wj × Rij)</p>

                <div class="formula-strip" style="margin-bottom:12px">
                    @foreach($kriteria as $k)
                    <span><strong>w{{ $loop->iteration }}</strong> = {{ $k->bobot }}%</span>
                    @endforeach
                    <span style="color:var(--c-text)"><strong>Vi</strong> = Σ (bobot × R)</span>
                </div>

                {{-- Legend kategori --}}
                <div class="legend">
                    <span>Kategori prioritas:</span>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#DC2626"></div>
                        <span>Sangat Prioritas (Vi ≥ 0.80)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#EA580C"></div>
                        <span>Prioritas (Vi ≥ 0.60)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#D97706"></div>
                        <span>Sedang (Vi ≥ 0.40)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#16A34A"></div>
                        <span>Rendah (Vi &lt; 0.40)</span>
                    </div>
                </div>

                @php
                    $maxVal  = $ranking->max('nilai_akhir');
                    $normMap = $normalisasi->keyBy('idpel');
                    $bArr    = $kriteria->values();
                @endphp

                <div class="tbl-wrap">
                    <table class="result-tbl">
                        <thead>
                            <tr>
                                <th style="width:8px"></th>{{-- strip warna --}}
                                <th style="width:56px">Rank</th>
                                <th>IDPEL</th>
                                @foreach($kriteria as $k)
                                <th class="r">w{{ $loop->iteration }}×R{{ $loop->iteration }}</th>
                                @endforeach
                                <th class="r">Vi</th>
                                <th>Risiko</th>
                                <th>Visualisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($ranking->sortBy('ranking') as $row)
                        @php
                            $n   = $normMap[$row->idpel] ?? null;
                            $rc  = $row->ranking <= 3
                                ? ['rank-1','rank-2','rank-3'][$row->ranking - 1]
                                : 'rank-n';
                            $pct = $maxVal > 0 ? ($row->nilai_akhir / $maxVal) * 100 : 0;

                            $kat = $row->kategori ?? 'Rendah';
                            switch ($kat) {
                                case 'Sangat Prioritas':
                                    $risikoClass = 'risiko-tinggi';
                                    $risikoLabel = 'Sangat Prioritas';
                                    $rowClass    = 'row-high';
                                    $barColor    = '#DC2626';
                                    break;
                                case 'Prioritas':
                                    $risikoClass = 'risiko-tinggi';
                                    $risikoLabel = 'Prioritas';
                                    $rowClass    = 'row-high';
                                    $barColor    = '#EA580C';
                                    break;
                                case 'Sedang':
                                    $risikoClass = 'risiko-sedang';
                                    $risikoLabel = 'Sedang';
                                    $rowClass    = 'row-medium';
                                    $barColor    = '#D97706';
                                    break;
                                default:
                                    $risikoClass = 'risiko-rendah';
                                    $risikoLabel = 'Rendah';
                                    $rowClass    = 'row-low';
                                    $barColor    = '#16A34A';
                                    break;
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td style="padding:0;width:3px"></td>
                            <td><span class="rank-badge {{ $rc }}">{{ $row->ranking }}</span></td>
                            <td class="mono" style="font-weight:600">{{ $row->idpel }}</td>

                            @if($n)
                                <td class="r mono">{{ number_format(($bArr[0]->bobot ?? 0) / 100 * $n->r1, 4) }}</td>
                                <td class="r mono">{{ number_format(($bArr[1]->bobot ?? 0) / 100 * $n->r2, 4) }}</td>
                                <td class="r mono">{{ number_format(($bArr[2]->bobot ?? 0) / 100 * $n->r3, 4) }}</td>
                                <td class="r mono">{{ number_format(($bArr[3]->bobot ?? 0) / 100 * $n->r4, 4) }}</td>
                            @else
                                <td colspan="4" class="r" style="color:var(--c-muted)">—</td>
                            @endif

                            <td class="r mono" style="font-weight:700;color:{{ $barColor }}">
                                {{ number_format($row->nilai_akhir, 4) }}
                            </td>
                            <td>
                                <span class="risiko-pill {{ $risikoClass }}">{{ $risikoLabel }}</span>
                            </td>
                            <td>
                                <div class="vi-bar-wrap">
                                    <div class="vi-bar-bg">
                                        <div class="vi-bar-fill"
                                             style="width:{{ round($pct) }}%;background:{{ $barColor }}">
                                        </div>
                                    </div>
                                    <span class="vi-val" style="color:{{ $barColor }}">
                                        {{ number_format($pct, 1) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @endif

            </div>
        </div>

        {{-- ══════════ PANE: GENERATE ══════════ --}}
        <div id="pane-generate" class="tab-pane">
            <div class="pane-body">
                <p class="sec-label">Generate Ranking SAW</p>
                <div class="gen-box">
                    <h3>Hitung Normalisasi &amp; Ranking</h3>
                    <p>
                        Membaca matriks penilaian, normalisasi SAW,<br>
                        hitung Vi per pelanggan, urutkan dari skor tertinggi.<br>
                        <strong>Data normalisasi &amp; ranking sebelumnya akan direset.</strong>
                    </p>
                    <div class="gen-steps">
                        <div class="gen-step"><div class="gen-step-num">1</div> Baca tabel penilaian</div>
                        <div class="gen-step"><div class="gen-step-num">2</div> Cari MAX &amp; MIN tiap kriteria</div>
                        <div class="gen-step"><div class="gen-step-num">3</div> Normalisasi R (Benefit / Cost)</div>
                        <div class="gen-step"><div class="gen-step-num">4</div> Hitung Vi = Σ (wj × Rij)</div>
                        <div class="gen-step"><div class="gen-step-num">5</div> Urutkan &amp; beri peringkat</div>
                    </div>
                    <form method="POST" action="{{ route('hasilranking.generate') }}">
                        @csrf
                        <button type="submit" class="btn-dark"
                                onclick="document.getElementById('loading').classList.add('show');this.disabled=true">
                            <svg fill="none" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Generate Ranking
                        </button>
                    </form>
                </div>
                <div class="info-box">
                    ℹ️ Pastikan sudah menjalankan <strong>Generate Penilaian</strong>
                    di menu Data Penilaian sebelum generate ranking.
                </div>
            </div>
        </div>

    </div>{{-- /main-card --}}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function switchTab(name) {
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        const pane = document.getElementById('pane-' + name);
        const btn  = document.getElementById('tab-' + name);
        if (pane) pane.classList.add('active');
        if (btn)  btn.classList.add('active');
        sessionStorage.setItem('ranking-tab', name);

        // Sembunyikan filter bar di tab generate
        const fb = document.getElementById('filter-section');
        if (fb) fb.style.display = name === 'hasil' ? 'flex' : 'none';
    }

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.id.replace('tab-', '')));
    });

    const saved  = sessionStorage.getItem('ranking-tab');
    const server = @json(session('active_tab'));
    switchTab(server || saved || 'hasil');
});
</script>
@endpush