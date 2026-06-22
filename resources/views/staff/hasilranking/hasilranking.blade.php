@extends('layouts.app')
@section('title', 'Dashboard Hasil Ranking')

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

    .page-wrap   { max-width: 1320px; padding: 24px 20px 48px; margin: 0 auto; }

    /* ── PAGE HEADER ── */
    .page-hdr { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
    .page-hdr-left h1 { font-size: 1.625rem; font-weight: 700; margin: 0 0 4px; }
    .page-hdr-left p { font-size: 1rem; color: var(--c-muted); margin: 0; }
    .page-hdr-right { display: flex; gap: 10px; align-items: flex-start; }

    .btn-gen {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: var(--radius-sm);
        background: var(--c-primary); color: #fff;
        border: none; font-size: 0.925rem; font-weight: 600;
        cursor: pointer; transition: all .15s;
        white-space: nowrap;
    }
    .btn-gen:hover { background: var(--c-primary-dk); box-shadow: 0 2px 8px rgba(0,91,170,.15); }
    .btn-gen svg { width: 14px; height: 14px; }

    /* ── STAT GRID ── */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 28px; }
    .stat-card { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius-sm); padding: 16px; box-shadow: var(--shadow); }
    .stat-card-label { font-size: 0.825rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--c-muted); margin-bottom: 8px; }
    .stat-card-val { font-size: 1.875rem; font-weight: 700; color: var(--c-text); line-height: 1; margin-bottom: 6px; }
    .stat-card-sub { font-size: 0.875rem; color: var(--c-muted); }
    .stat-card-val.danger  { color: #DC2626; }
    .stat-card-val.warning { color: #D97706; }
    .stat-card-val.success { color: #16A34A; }

    /* ── FILTER BAR ── */
    .filter-bar { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius-sm); padding: 14px 16px; margin-bottom: 20px; box-shadow: var(--shadow); display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
    .fg { display: flex; flex-direction: column; gap: 4px; }
    .fg label { font-size: 0.825rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--c-muted); }
    .fg input, .fg select { height: 32px; padding: 0 10px; border: 1px solid var(--c-border); border-radius: var(--radius-sm); font-size: 0.925rem; color: var(--c-text); background: #fff; outline: none; transition: border-color .15s; min-width: 140px; }
    .fg input:focus, .fg select:focus { border-color: var(--c-primary); }
    .btn-filter { height: 32px; padding: 0 16px; border-radius: var(--radius-sm); border: 1px solid var(--c-border); background: #fff; font-size: 0.925rem; color: var(--c-muted); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all .15s; white-space: nowrap; }
    .btn-filter:hover { background: var(--c-surface); color: var(--c-text); }
    .btn-filter svg { width: 13px; height: 13px; }
    .btn-reset { height: 32px; padding: 0 12px; border-radius: var(--radius-sm); border: 1px solid var(--c-border); background: #fff; font-size: 0.925rem; color: var(--c-muted); cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; transition: background .15s; }
    .btn-reset:hover { background: var(--c-surface); }

    /* ── MAIN CARD ── */
    .main-card { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: 20px; }

    /* ── SECTION HEADER ── */
    .sec-hdr { padding: 16px; border-bottom: 1px solid var(--c-border); background: var(--c-surface); display: flex; justify-content: space-between; align-items: center; }
    .sec-hdr h2 { font-size: 1.025rem; font-weight: 700; margin: 0; }

    /* ── TABLE ── */
    .tbl-wrap { overflow: auto; }
    .result-tbl { width: 100%; border-collapse: collapse; font-size: 1rem; }
    .result-tbl thead { background: var(--c-surface); }
    .result-tbl th { padding: 11px 12px; text-align: left; font-size: 0.825rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); white-space: nowrap; border-bottom: 2px solid var(--c-border); }
    .result-tbl th.r { text-align: right; }
    .result-tbl td { padding: 10px 12px; border-bottom: 1px solid #F1F5F9; }
    .result-tbl td.r { text-align: right; }
    .result-tbl td.mono { font-family: var(--mono); font-size: 0.925rem; }
    .result-tbl tr:hover td { background: #FAFAFA; }
    .result-tbl tr:last-child td { border-bottom: none; }

    .row-high   { border-left: 3px solid #DC2626; }
    .row-medium { border-left: 3px solid #D97706; }
    .row-low    { border-left: 3px solid #16A34A; }

    /* ── RANK BADGE ── */
    .rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: var(--radius-sm); font-size: 0.875rem; font-weight: 700; }
    .rank-1 { background: #FEF9C3; color: #92400E; border: 1px solid #FCD34D; }
    .rank-2 { background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; }
    .rank-3 { background: #FFF7ED; color: #9A3412; border: 1px solid #FED7AA; }
    .rank-n { background: var(--c-surface); color: var(--c-muted); border: 1px solid var(--c-border); }

    /* ── RISIKO PILL ── */
    .risiko-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 9px; border-radius: 99px; font-size: 0.875rem; font-weight: 600; }
    .risiko-tinggi { background: #FEE2E2; color: #991B1B; }
    .risiko-sedang { background: #FEF9C3; color: #92400E; }
    .risiko-rendah { background: #D1FAE5; color: #065F46; }

    /* ── VI BAR ── */
    .vi-bar-wrap { display: flex; align-items: center; gap: 8px; }
    .vi-bar-bg { width: 80px; height: 5px; border-radius: 99px; background: #E2E8F0; overflow: hidden; flex-shrink: 0; }
    .vi-bar-fill { height: 100%; border-radius: 99px; }
    .vi-val { font-size: 0.875rem; font-weight: 700; font-family: var(--mono); min-width: 40px; }

    /* ── EMPTY STATE ── */
    .empty-box { padding: 60px 40px; text-align: center; background: var(--c-surface); }
    .empty-box-icon { opacity: .3; display: block; margin: 0 auto 16px; width: 52px; height: 52px; }
    .empty-box h3 { font-size: 1.125rem; font-weight: 600; margin: 0 0 8px; color: var(--c-text); }
    .empty-box p { font-size: 1rem; color: var(--c-muted); margin: 0 0 20px; line-height: 1.6; }
    .empty-box .btn-gen { margin: 0 auto; }

    /* ── COLLAPSIBLE ── */
    .collapsible-hdr { padding: 16px; background: var(--c-surface); border-bottom: 1px solid var(--c-border); display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none; }
    .collapsible-hdr h3 { font-size: 1.025rem; font-weight: 700; margin: 0; }
    .collapsible-toggle { font-size: 1.325rem; transition: transform .2s; }
    .collapsible-toggle.open { transform: rotate(180deg); }
    .collapsible-body { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
    .collapsible-body.open { max-height: 1000px; }
    .collapsible-content { padding: 16px; }

    /* ── FORMULA STRIP ── */
    .formula-strip { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 0.875rem; color: var(--c-muted); margin-bottom: 12px; line-height: 1.8; overflow-x: auto; }
    .formula-strip code { background: #E2E8F0; padding: 2px 6px; border-radius: 4px; font-size: 0.825rem; color: var(--c-text); font-family: var(--mono); white-space: nowrap; }
    .formula-strip strong { color: var(--c-text); }

    /* ── PAGINATION ── */
    .pagination-wrap { display: flex; justify-content: space-between; align-items: center; padding: 16px; border-top: 1px solid var(--c-border); background: var(--c-surface); font-size: 0.925rem; }
    .pagination-info { color: var(--c-muted); }
    .pagination-links { display: flex; gap: 4px; }
    .pagination-links a, .pagination-links span { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 1px solid var(--c-border); border-radius: var(--radius-sm); font-size: 0.875rem; text-decoration: none; transition: all .15s; }
    .pagination-links a { color: var(--c-text); background: #fff; cursor: pointer; }
    .pagination-links a:hover { border-color: var(--c-primary); background: var(--c-surface); }
    .pagination-links span { color: var(--c-muted); background: var(--c-surface); }
    .pagination-links .active span { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }

    /* ── ALERTS ── */
    .alert { padding: 10px 14px; border-radius: var(--radius-sm); font-size: 1rem; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; }
    .alert-danger  { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
    .alert svg { width: 16px; height: 16px; flex-shrink: 0; }

    /* ── LOADING ── */
    .loading-overlay { display: none; position: fixed; inset: 0; background: rgba(249,250,251,.9); align-items: center; justify-content: center; z-index: 9999; }
    .loading-overlay.show { display: flex; }
    .loading-card { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius); padding: 28px 44px; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,.1); }
    .loading-card p { font-size: 1rem; color: var(--c-muted); margin: 12px 0 0; }
    .spin { width: 32px; height: 32px; margin: 0 auto; border: 2.5px solid var(--c-border); border-top-color: var(--c-text); border-radius: 50%; animation: spin .75s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 1024px) {
        .page-wrap { padding: 20px 18px 40px; }
        .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .page-hdr h1 { font-size: 1.375rem; }
        .result-tbl { font-size: 0.925rem; }
        .result-tbl th, .result-tbl td { padding: 9px 10px; }
    }
    @media (max-width: 768px) {
        .page-wrap { padding: 16px 12px 32px; }
        .page-hdr { flex-direction: column; gap: 12px; }
        .page-hdr h1 { font-size: 1.25rem; }
        .page-hdr-right { width: 100%; flex-wrap: wrap; }
        .btn-gen { width: 100%; justify-content: center; }
        .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px; }
        .stat-card { padding: 14px; }
        .stat-card-val { font-size: 1.625rem; }
        .filter-bar { flex-direction: column; gap: 10px; padding: 12px 14px; margin-bottom: 16px; }
        .fg { width: 100%; }
        .fg input, .fg select { min-width: 100%; height: 40px; font-size: 0.925rem; }
        .btn-filter, .btn-reset { width: 100%; }
        .tbl-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    }
    @media (max-width: 576px) {
        .stat-grid { grid-template-columns: 1fr; }
        .result-tbl { font-size: 0.875rem; }
        .result-tbl th, .result-tbl td { padding: 6px 8px; }
    }
</style>
@endpush

@section('content')

{{-- Tampilan loading overlay saat generate data ranking --}}
<div class="loading-overlay" id="loading">
    <div class="loading-card">
        <div class="spin"></div>
        <p>Menghitung normalisasi & ranking SAW...</p>
    </div>
</div>

<div class="page-wrap">

    {{-- Header halaman hasil ranking dan tombol generate --}}
    <div class="page-hdr">
        <div class="page-hdr-left">
            <h1>Hasil Ranking SAW</h1>
            <p>Normalisasi · Nilai Akhir (Vi) · Peringkat berdasarkan risiko tunggakan</p>
        </div>
        <div class="page-hdr-right">
            @if($hasData)
            <form method="POST" action="{{ route('hasilranking.generate') }}" style="margin:0; display:flex; gap:8px; align-items:center;">
                @csrf
                <input type="month" name="periode" required value="{{ request('periode') ?? date('Y-m') }}" style="height:32px; border:1px solid var(--c-border); border-radius:var(--radius-sm); padding:0 10px; font-size:0.925rem;">
                <button type="submit" class="btn-gen"
                    onclick="document.getElementById('loading').classList.add('show');">
                    <svg fill="none" viewBox="0 0 24 24">
                        <polyline points="23 4 23 10 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Generate Ulang
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Menampilkan notifikasi sukses atau error --}}
    @if(session('success'))
    <div class="alert alert-success">
        <svg fill="none" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">
        <svg fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Tampilan jika data ranking masih kosong --}}
    @if(!$hasData)
    <div class="main-card">
        <div class="empty-box">
            <svg class="empty-box-icon" fill="none" viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3>Belum Ada Data Ranking</h3>
            <p>Klik tombol di bawah untuk generate ranking dari data penilaian yang sudah ada.</p>
            <form method="POST" action="{{ route('hasilranking.generate') }}" style="display:inline-flex; gap:8px; align-items:center;">
                @csrf
                <input type="month" name="periode" required value="{{ request('periode') ?? date('Y-m') }}" style="height:32px; border:1px solid var(--c-border); border-radius:var(--radius-sm); padding:0 10px; font-size:0.925rem;">
                <button type="submit" class="btn-gen"
                    onclick="document.getElementById('loading').classList.add('show');">
                    <svg fill="none" viewBox="0 0 24 24">
                        <polyline points="23 4 23 10 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Generate Ranking
                </button>
            </form>
        </div>
    </div>

    @else

    {{-- Menampilkan ringkasan total pelanggan dan kategori prioritas --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card-label">Total Pelanggan</div>
            <div class="stat-card-val">{{ $stats['total'] }}</div>
            <div class="stat-card-sub">Data aktif</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Sangat Prioritas</div>
            <div class="stat-card-val danger">{{ $stats['sangat_prioritas'] }}</div>
            <div class="stat-card-sub">Vi ≥ 0.80</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Prioritas</div>
            <div class="stat-card-val warning">{{ $stats['prioritas'] }}</div>
            <div class="stat-card-sub">Vi ≥ 0.60</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Sedang & Rendah</div>
            <div class="stat-card-val success">{{ $stats['sedang'] + $stats['rendah'] }}</div>
            <div class="stat-card-sub">Vi < 0.60</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Terakhir Generate</div>
            <div class="stat-card-val" style="font-size: 1.125rem;">
                @if($stats['generated_at'])
                    {{ \Carbon\Carbon::parse($stats['generated_at'])->format('d M Y H:i') }}
                @else
                    —
                @endif
            </div>
            <div class="stat-card-sub">Waktu update</div>
        </div>
    </div>

    {{-- Form pencarian, filter data, dan tombol export --}}
    <div class="filter-bar">
        <form method="GET" action="{{ request()->url() }}"
              style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;width:100%">

            <div class="fg">
                <label>Periode</label>
                <input type="month" name="periode" value="{{ request('periode') }}">
            </div>

            <div class="fg">
                <label>Cari IDPEL / Nama</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari...">
            </div>

            <div class="fg">
                <label>Petugas</label>
                <select name="petugas_id">
                    <option value="">Semua Petugas</option>
                    @foreach($petugasList as $p)
                        <option value="{{ $p->id }}" {{ request('petugas_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="fg">
                <label>Kategori Prioritas</label>
                <select name="kategori">
                    <option value="">Semua</option>
                    <option value="Sangat Prioritas" {{ request('kategori')=='Sangat Prioritas' ? 'selected' : '' }}>🔴 Sangat Prioritas</option>
                    <option value="Prioritas"        {{ request('kategori')=='Prioritas'        ? 'selected' : '' }}>🟠 Prioritas</option>
                    <option value="Sedang"           {{ request('kategori')=='Sedang'           ? 'selected' : '' }}>🟡 Sedang</option>
                    <option value="Rendah"           {{ request('kategori')=='Rendah'           ? 'selected' : '' }}>🟢 Rendah</option>
                </select>
            </div>

            <div class="fg">
                <label>Tampilkan Top</label>
                <select name="top">
                    <option value=""   {{ request('top')==''   ? 'selected' : '' }}>Semua</option>
                    <option value="10" {{ request('top')=='10' ? 'selected' : '' }}>Top 10</option>
                    <option value="25" {{ request('top')=='25' ? 'selected' : '' }}>Top 25</option>
                    <option value="50" {{ request('top')=='50' ? 'selected' : '' }}>Top 50</option>
                </select>
            </div>

            <button type="submit" class="btn-filter">
                <svg fill="none" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Terapkan
            </button>

            <a href="{{ request()->url() }}" class="btn-reset">Reset</a>

            {{-- Export CSV --}}
            <a href="{{ route('hasilranking.exportCsv', request()->query()) }}"
               class="btn-filter"
               style="margin-left: auto; border-color: var(--c-primary); color: var(--c-primary);">
                <svg fill="none" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Export CSV
            </a>

            {{-- Export PDF --}}
            <a href="{{ route('hasilranking.exportPdf', request()->query()) }}"
               class="btn-filter"
               style="border-color: #DC2626; color: #DC2626;">
                <svg fill="none" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Export PDF
            </a>

        </form>
    </div>

    {{-- Tabel utama daftar hasil perhitungan ranking --}}
    <div class="main-card">
        <div class="sec-hdr">
            <h2>Peringkat Ranking (Vi = Σ wj × Rij)</h2>
        </div>

        {{-- Bobot strip --}}
        <div style="padding: 14px 16px; background: var(--c-surface); border-bottom: 1px solid var(--c-border);
                    font-size: 0.875rem; color: var(--c-muted); overflow-x: auto;">
            @foreach($kriteria as $k)
                <span style="display: inline-block; margin-right: 16px; white-space: nowrap;">
                    <strong>w{{ $loop->iteration }}</strong> = {{ $k->bobot }}% ({{ ucfirst($k->tipe) }})
                </span>
            @endforeach
        </div>

        <div class="tbl-wrap">
            @if($ranking->isEmpty())
            <div style="padding: 40px; text-align: center; color: var(--c-muted);">
                <p>Tidak ada data yang sesuai dengan filter.</p>
            </div>
            @else
            <table class="result-tbl">
                <thead>
                    <tr>
                        <th style="width: 8px;"></th>
                        <th style="width: 56px;">Rank</th>
                        <th>Periode</th>
                        <th>IDPEL / Nama</th>
                        {{-- ✅ Unitap menggantikan RBM --}}
                        <th>Unitap</th>
                        <th>Petugas</th>
                        @foreach($kriteria as $k)
                        <th class="r">w{{ $loop->iteration }}×R</th>
                        @endforeach
                        <th class="r" style="min-width: 80px;">Vi</th>
                        <th style="min-width: 120px;">Risiko</th>
                        <th style="min-width: 100px;">Grafis</th>
                    </tr>
                </thead>
                <tbody>
                {{-- Looping baris data hasil ranking --}}
                @foreach($ranking as $row)
                @php
                    $n   = $normalisasi[$row->idpel] ?? null;
                    $rc  = $row->ranking <= 3
                        ? ['rank-1','rank-2','rank-3'][$row->ranking - 1]
                        : 'rank-n';
                    $pct = $stats['max_nilai'] > 0
                        ? ($row->nilai_akhir / $stats['max_nilai']) * 100
                        : 0;
                    $kat = $row->kategori ?? 'Rendah';
                    $categoryColors = [
                        'Sangat Prioritas' => ['class'=>'row-high',   'pill'=>'risiko-tinggi', 'label'=>'Sangat Prioritas', 'color'=>'#DC2626'],
                        'Prioritas'        => ['class'=>'row-high',   'pill'=>'risiko-tinggi', 'label'=>'Prioritas',        'color'=>'#EA580C'],
                        'Sedang'           => ['class'=>'row-medium', 'pill'=>'risiko-sedang', 'label'=>'Sedang',           'color'=>'#D97706'],
                        'Rendah'           => ['class'=>'row-low',    'pill'=>'risiko-rendah', 'label'=>'Rendah',           'color'=>'#16A34A'],
                    ];
                    $catInfo = $categoryColors[$kat] ?? $categoryColors['Rendah'];
                @endphp
                <tr class="{{ $catInfo['class'] }}">
                    <td style="padding:0;width:3px;"></td>
                    <td><span class="rank-badge {{ $rc }}">{{ $row->ranking }}</span></td>
                    <td>
                        <span class="badge" style="background:#E2E8F0; color:#1E293B; padding:2px 6px; border-radius:4px; font-size:0.825rem;">
                            {{ $row->periode }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:600; font-family:var(--mono); font-size: 0.925rem;">{{ $row->idpel }}</div>
                        @if($row->nama)
                        <div style="font-size: 0.875rem; color:var(--c-muted);">{{ Str::limit($row->nama, 30) }}</div>
                        @endif
                    </td>

                    {{-- ✅ Unitap — ganti $row->rbm --}}
                    <td>
                        <span style="background:#E2E8F0; padding:2px 6px; border-radius:4px;
                                     font-size: 0.825rem; font-weight:600;">
                            {{ $row->unitap ?: '-' }}
                        </span>
                    </td>

                    <td>
                        <div style="font-weight:600; font-size: 0.925rem;">{{ $row->kode_petugas ?: '-' }}</div>
                        <div style="font-size: 0.875rem; color:var(--c-muted);">{{ $row->nama_petugas ?: '-' }}</div>
                    </td>

                    @if($n)
                        <td class="r mono">{{ number_format(($kriteria[0]->bobot ?? 0) / 100 * $n->r1, 4) }}</td>
                        <td class="r mono">{{ number_format(($kriteria[1]->bobot ?? 0) / 100 * $n->r2, 4) }}</td>
                        <td class="r mono">{{ number_format(($kriteria[2]->bobot ?? 0) / 100 * $n->r3, 4) }}</td>
                        <td class="r mono">{{ number_format(($kriteria[3]->bobot ?? 0) / 100 * $n->r4, 4) }}</td>
                    @else
                        <td class="r" colspan="4" style="color:var(--c-muted);">—</td>
                    @endif

                    <td class="r mono" style="font-weight:700; color:{{ $catInfo['color'] }};">
                        {{ number_format($row->nilai_akhir, 4) }}
                    </td>
                    <td>
                        <span class="risiko-pill {{ $catInfo['pill'] }}">{{ $catInfo['label'] }}</span>
                    </td>
                    <td>
                        <div class="vi-bar-wrap">
                            <div class="vi-bar-bg">
                                <div class="vi-bar-fill"
                                     style="width:{{ round($pct) }}%; background:{{ $catInfo['color'] }};"></div>
                            </div>
                            <span class="vi-val" style="color:{{ $catInfo['color'] }};">
                                {{ number_format($pct, 1) }}%
                            </span>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>

        {{-- Link pagination tabel ranking --}}
        @if($ranking->hasPages())
        <div class="pagination-wrap">
            <div class="pagination-info">
                Menampilkan {{ $ranking->firstItem() }}–{{ $ranking->lastItem() }}
                dari {{ $ranking->total() }} data
            </div>
            <div class="pagination-links">
                @if($ranking->onFirstPage())
                    <span>←</span>
                @else
                    <a href="{{ $ranking->previousPageUrl() }}">←</a>
                @endif

                @foreach($ranking->getUrlRange(1, $ranking->lastPage()) as $page => $url)
                    @if($page == $ranking->currentPage())
                        <span class="active"><span>{{ $page }}</span></span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($ranking->hasMorePages())
                    <a href="{{ $ranking->nextPageUrl() }}">→</a>
                @else
                    <span>→</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Bagian tabel matriks normalisasi yang dapat dilipat --}}
    @if(!$normalisasi->isEmpty())
    <div class="main-card">
        <div class="collapsible-hdr" onclick="toggleNormalisasi()">
            <h3>Matriks Normalisasi (R)</h3>
            <div class="collapsible-toggle" id="norm-toggle">▼</div>
        </div>
        <div class="collapsible-body" id="norm-body">
            <div class="collapsible-content">
                <div class="formula-strip">
                    <span><strong>Benefit</strong> → R = nilai / MAX</span>
                    &nbsp;·&nbsp;
                    <span><strong>Cost</strong> → R = MIN / nilai</span>
                </div>
                <div class="tbl-wrap">
                    <table class="result-tbl">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>IDPEL</th>
                                @foreach($kriteria as $k)
                                <th class="r">R{{ $loop->iteration }} — {{ Str::limit($k->nama, 15) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                        {{-- Looping baris data normalisasi --}}
                        @foreach($normalisasi as $n)
                            <tr>
                                <td class="mono" style="color:var(--c-muted);">{{ $loop->iteration }}</td>
                                <td class="mono">{{ $n->idpel }}</td>
                                <td class="r mono">{{ number_format($n->r1, 4) }}</td>
                                <td class="r mono">{{ number_format($n->r2, 4) }}</td>
                                <td class="r mono">{{ number_format($n->r3, 4) }}</td>
                                <td class="r mono">{{ number_format($n->r4, 4) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <p style="font-size: 0.875rem; color:var(--c-muted); margin:12px 0 0;">
                    Nilai normalisasi hasil perhitungan menggunakan metode SAW.
                </p>
            </div>
        </div>
    </div>
    @endif

    @endif {{-- end hasData --}}

</div>

@endsection

@push('scripts')
<script>
// Script untuk fungsi toggle lipatan tabel matriks normalisasi
function toggleNormalisasi() {
    const body   = document.getElementById('norm-body');
    const toggle = document.getElementById('norm-toggle');
    body.classList.toggle('open');
    toggle.classList.toggle('open');
}
</script>
@endpush
