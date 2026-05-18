@extends('layouts.app')

@section('title', 'Master Data Pelanggan')
@section('page_title', 'Master Data Pelanggan')

@section('content')

<style>
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
    }

    body { background: var(--c-bg); font-family: var(--font); color: var(--c-text); }
    .page-wrap { max-width: 1400px; padding: 24px 20px 40px; margin: 0 auto; }
    .page-header { margin-bottom: 28px; display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .page-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--c-text); }
    .page-header p  { font-size: .875rem; color: var(--c-muted); margin: 0; }

    .pln-alert {
        padding: 14px 16px; border-radius: var(--radius-sm); margin-bottom: 16px;
        display: flex; align-items: flex-start; gap: 10px; font-size: .875rem; font-weight: 500;
        animation: slideDown .3s ease;
    }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .pln-alert-success { background: var(--c-success-lt); color: var(--c-success); border: 1px solid var(--c-success-bd); }
    .pln-alert-danger  { background: var(--c-danger-lt);  color: var(--c-danger); border: 1px solid var(--c-danger-bd); }
    .pln-alert-warning { background: var(--c-warning-lt); color: var(--c-warning); border: 1px solid var(--c-warning-bd); }
    .pln-alert i { flex-shrink: 0; margin-top: 2px; font-size: 1.1rem; }

    .tab-bar {
        display: flex; gap: 8px; border-bottom: 2px solid var(--c-border);
        background: var(--c-surface); padding: 0 0 0 20px; margin-bottom: 24px;
        border-radius: var(--radius) var(--radius) 0 0; box-shadow: var(--shadow);
    }
    .tab-btn {
        padding: 12px 16px; border: none; background: none; cursor: pointer;
        font-size: .875rem; font-weight: 600; color: var(--c-muted);
        border-bottom: 3px solid transparent; transition: all .2s;
        display: flex; align-items: center; gap: 7px; white-space: nowrap;
        text-decoration: none;
    }
    .tab-btn:hover { color: var(--c-primary); }
    .tab-btn.active { color: var(--c-primary); border-bottom-color: var(--c-primary); }
    .tab-btn i { font-size: 1rem; }
    .tab-badge {
        font-size: .75rem; padding: 2px 6px; border-radius: 4px; background: var(--c-primary-lt);
        color: var(--c-primary); font-weight: 700;
    }
    .tab-badge-danger { background: var(--c-danger-lt); color: var(--c-danger); }

    .tab-pane { display: none; }
    .tab-pane.active { display: block; animation: fadeIn .2s; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px; }
    .summary-card {
        background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        padding: 16px 18px; box-shadow: var(--shadow); position: relative; overflow: hidden;
    }
    .summary-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    }
    .summary-card.blue::before   { background: var(--c-primary); }
    .summary-card.green::before  { background: var(--c-success); }
    .summary-card.red::before    { background: var(--c-danger); }
    .summary-card.orange::before { background: var(--c-warning); }
    .summary-card-label { font-size: .8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); margin-bottom: 4px; }
    .summary-card-value { font-size: 1.5rem; font-weight: 700; color: var(--c-text); }
    .summary-card-sub { font-size: .8125rem; color: var(--c-muted); margin-top: 4px; }

    .filter-bar {
        background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        padding: 16px 20px; display: flex; align-items: flex-end; gap: 14px; flex-wrap: wrap;
        box-shadow: var(--shadow); margin-bottom: 20px;
    }
    .filter-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 150px; }
    .filter-group label { font-size: .8125rem; font-weight: 600; color: var(--c-muted); text-transform: uppercase; letter-spacing: .05em; }
    .filter-group input, .filter-group select {
        height: 38px; padding: 0 12px; border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        font-size: .875rem; color: var(--c-text); background: #F8FAFC; outline: none;
        transition: all .15s; font-family: var(--font);
    }
    .filter-group input:focus, .filter-group select:focus { border-color: var(--c-primary); background: white; }

    .btn {
        display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; border: 1px solid transparent; transition: all .15s;
        text-decoration: none; line-height: 1; height: 38px;
    }
    .btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .btn-primary  { background: var(--c-primary); color: white; }
    .btn-primary:hover { background: var(--c-primary-dk); }
    .btn-outline  { background: white; color: var(--c-muted); border-color: var(--c-border); }
    .btn-outline:hover { background: #F8FAFC; color: var(--c-text); }
    .btn-sm { padding: 6px 12px; font-size: .8125rem; height: auto; }
    .btn-danger { background: var(--c-danger); color: white; }
    .btn-danger:hover { background: #B91C1C; }

    .drop-zone {
        border: 2px dashed var(--c-border); border-radius: var(--radius); padding: 40px;
        text-align: center; cursor: pointer; transition: all .2s;
        background: #F8FAFC;
    }
    .drop-zone:hover, .drop-zone.dragover { border-color: var(--c-primary); background: var(--c-primary-lt); }
    .drop-icon { font-size: 2.5rem; color: var(--c-primary); margin-bottom: 12px; }
    .drop-title { font-size: 1rem; font-weight: 600; color: var(--c-text); margin: 0 0 6px; }
    .drop-sub { font-size: .875rem; color: var(--c-muted); margin: 0; }
    .drop-or { font-size: .8125rem; color: var(--c-light); margin: 8px 0; display: block; }

    .file-chip {
        display: none; align-items: center; gap: 8px; padding: 8px 12px;
        background: var(--c-success-lt); border: 1px solid var(--c-success-bd); border-radius: var(--radius-sm);
        margin-top: 12px; font-size: .875rem; color: var(--c-success);
    }
    .chip-dot { width: 8px; height: 8px; background: var(--c-success); border-radius: 50%; }

    .table-wrap {
        background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-sm);
        overflow: hidden; box-shadow: var(--shadow); margin-bottom: 20px;
    }
    .table-sticky { overflow-x: auto; }
    .table {
        width: 100%; border-collapse: collapse; font-size: .875rem;
    }
    .table thead {
        background: #F8FAFC; border-bottom: 2px solid var(--c-border); position: sticky; top: 0; z-index: 10;
    }
    .table thead th {
        padding: 12px 16px; text-align: left; font-weight: 600; color: var(--c-muted);
        text-transform: uppercase; font-size: .8125rem; letter-spacing: .05em;
    }
    .table tbody tr {
        border-bottom: 1px solid var(--c-border); transition: background .15s;
    }
    .table tbody tr:hover { background: #F8FAFC; }
    .table tbody tr.error { background: var(--c-danger-lt); }
    .table td { padding: 12px 16px; vertical-align: middle; }
    .table td.text-muted { color: var(--c-muted); font-size: .8125rem; }

    .badge {
        display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px;
        border-radius: 4px; font-size: .75rem; font-weight: 600;
    }
    .badge-success { background: var(--c-success-lt); color: var(--c-success); }
    .badge-danger  { background: var(--c-danger-lt);  color: var(--c-danger); }
    .badge-warning { background: var(--c-warning-lt); color: var(--c-warning); }

    .pagination {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        margin: 24px 0; flex-wrap: wrap;
    }
    .page-link {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); text-decoration: none; color: var(--c-text);
        transition: all .15s; font-size: .875rem; font-weight: 500;
    }
    .page-link:hover { border-color: var(--c-primary); color: var(--c-primary); }
    .page-link.active { background: var(--c-primary); color: white; border-color: var(--c-primary); }
    .page-link.disabled { color: var(--c-light); cursor: not-allowed; }

    .empty-state {
        padding: 60px 20px; text-align: center; background: #F8FAFC;
        border: 1px dashed var(--c-border); border-radius: var(--radius-sm);
    }
    .empty-icon { font-size: 3rem; color: var(--c-light); margin-bottom: 12px; }
    .empty-title { font-size: 1.125rem; font-weight: 600; color: var(--c-text); margin: 0 0 4px; }
    .empty-text { font-size: .875rem; color: var(--c-muted); margin: 0; }

    .action-cell {
        display: flex; gap: 6px; align-items: center;
    }
    .action-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 6px; border: none;
        cursor: pointer; transition: all .15s; font-size: .875rem;
    }
    .action-btn.view   { background: #EFF6FF; color: var(--c-primary); }
    .action-btn.edit   { background: #FEF3C7; color: var(--c-warning); }
    .action-btn.delete { background: var(--c-danger-lt); color: var(--c-danger); }
    .action-btn:hover { transform: scale(1.05); }

    #pln-loading {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,.3); display: flex; align-items: center; justify-content: center;
        z-index: 2000; opacity: 0; pointer-events: none; transition: opacity .2s;
    }
    #pln-loading.show {
        opacity: 1; pointer-events: auto;
    }
    .spinner {
        width: 40px; height: 40px; border: 4px solid #f3f3f3;
        border-top: 4px solid var(--c-primary); border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    @media (max-width: 768px) {
        .filter-bar { flex-direction: column; }
        .filter-group { width: 100%; }
        .summary-grid { grid-template-columns: repeat(2, 1fr); }
        .table { font-size: .8125rem; }
        .table td, .table th { padding: 10px 12px; }
    }
</style>

<div class="page-wrap">

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div>
            <h1><i class="bi bi-people" style="margin-right: 8px;"></i>Master Data Pelanggan</h1>
            <p>Kelola dan impor data pelanggan dengan sistem UPSERT otomatis</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @if ($activeTab === 'data')
                <a href="{{ route('pelanggan.export') }}" class="btn btn-outline btn-sm">
                    <i class="bi bi-download"></i> Export CSV
                </a>
            @endif
        </div>
    </div>

    {{-- ALERTS --}}
    @if (session('save_success'))
        <div class="pln-alert pln-alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('save_success') }}
        </div>
    @endif
    @if (session('save_error'))
        <div class="pln-alert pln-alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('save_error') }}
        </div>
    @endif
    @if (session('upload_success'))
        <div class="pln-alert pln-alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('upload_success') }}
        </div>
    @endif
    @if (isset($fatal) && $fatal)
        <div class="pln-alert pln-alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $fatal }}
        </div>
    @endif

    {{-- TABS --}}
    <div class="tab-bar">
        <button class="tab-btn {{ $activeTab === 'data' ? 'active' : '' }}" data-tab="data">
            <i class="bi bi-table"></i> Data Pelanggan
            <span class="tab-badge">{{ $totalPelanggan }}</span>
        </button>
        <button class="tab-btn {{ $activeTab === 'import' ? 'active' : '' }}" data-tab="import">
            <i class="bi bi-upload"></i> Import CSV
        </button>
        <button class="tab-btn {{ $activeTab === 'errors' ? 'active' : '' }}" data-tab="errors">
            <i class="bi bi-exclamation-circle"></i> Error Logs
        </button>
    </div>

    {{-- TAB: DATA PELANGGAN --}}
    <div class="tab-pane {{ $activeTab === 'data' ? 'active' : '' }}" id="pane-data">

        {{-- SUMMARY CARDS --}}
        <div class="summary-grid">
            <div class="summary-card blue">
                <div class="summary-card-label">Total Pelanggan</div>
                <div class="summary-card-value">{{ number_format($totalPelanggan) }}</div>
            </div>
            <div class="summary-card green">
                <div class="summary-card-label">Total Daya</div>
                <div class="summary-card-value">{{ number_format($totalDaya) }}</div>
                <div class="summary-card-sub">kW</div>
            </div>
            <div class="summary-card orange">
                <div class="summary-card-label">Total Rptag</div>
                <div class="summary-card-value">Rp {{ number_format($totalRptag, 0) }}</div>
            </div>
            <div class="summary-card red">
                <div class="summary-card-label">Terakhir Import</div>
                <div class="summary-card-value" style="font-size: 1rem;">
                    @if ($importLogs->isNotEmpty())
                        {{ $importLogs->first()->import_date?->diffForHumans() ?? '-' }}
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>

        {{-- FILTER & SEARCH BAR --}}
        <form method="GET" action="{{ route('pelanggan.index', ['tab' => 'data']) }}" class="filter-bar" id="filter-form">

            <div class="filter-group" style="flex: 2;">
                <label>Cari (IDPEL / Nama)</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik idpel atau nama...">
            </div>

            <div class="filter-group">
                <label>Sort</label>
                <select name="sort" onchange="document.getElementById('filter-form').submit();">
                    <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ $sortBy === 'oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="nama_az" {{ $sortBy === 'nama_az' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="nama_za" {{ $sortBy === 'nama_za' ? 'selected' : '' }}>Nama Z-A</option>
                    <option value="daya_high" {{ $sortBy === 'daya_high' ? 'selected' : '' }}>Daya Tinggi</option>
                    <option value="daya_low" {{ $sortBy === 'daya_low' ? 'selected' : '' }}>Daya Rendah</option>
                    <option value="rptag_high" {{ $sortBy === 'rptag_high' ? 'selected' : '' }}>Rptag Tinggi</option>
                    <option value="rptag_low" {{ $sortBy === 'rptag_low' ? 'selected' : '' }}>Rptag Rendah</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Daya Min</label>
                <input type="number" name="daya_min" value="{{ $dayaMin }}" placeholder="Min">
            </div>

            <div class="filter-group">
                <label>Daya Max</label>
                <input type="number" name="daya_max" value="{{ $dayaMax }}" placeholder="Max">
            </div>

            <div class="filter-group">
                <label>Rptag Min</label>
                <input type="number" name="rptag_min" value="{{ $rptagMin }}" placeholder="Min">
            </div>

            <div class="filter-group">
                <label>Rptag Max</label>
                <input type="number" name="rptag_max" value="{{ $rptagMax }}" placeholder="Max">
            </div>

            <div class="filter-group">
                <label>Lembar</label>
                <select name="lembar">
                    <option value="">Semua</option>
                    @foreach ($lembars as $l)
                        <option value="{{ $l }}" {{ $lembar === (string)$l ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 8px; align-items: flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('pelanggan.index', ['tab' => 'data']) }}" class="btn btn-outline btn-sm">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>

        </form>

        {{-- DATA TABLE --}}
        @if ($pelanggan->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                <h3 class="empty-title">Tidak ada data</h3>
                <p class="empty-text">Silakan upload file CSV untuk menambah data pelanggan</p>
            </div>
        @else
            <div class="table-wrap">
                <div class="table-sticky">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 110px;">IDPEL</th>
                                <th>Nama</th>
                                <th style="width: 90px;">Daya</th>
                                <th style="width: 80px;">Lembar</th>
                                <th style="width: 110px;">RPTAG</th>
                                <th style="width: 110px;">RPBK</th>
                                <th style="width: 140px;">Dibuat</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = ($pelanggan->currentPage() - 1) * $pelanggan->perPage() + 1; @endphp
                            @foreach ($pelanggan as $p)
                                <tr>
                                    <td class="text-muted">{{ $no++ }}</td>
                                    <td><strong>{{ $p->idpel }}</strong></td>
                                    <td>{{ $p->nama }}</td>
                                    <td>{{ number_format($p->daya) }} kW</td>
                                    <td>{{ $p->lembar }}</td>
                                    <td>Rp {{ number_format($p->rptag, 0) }}</td>
                                    <td>Rp {{ number_format($p->rpbk, 0) }}</td>
                                    <td class="text-muted">{{ $p->created_at?->format('d M Y H:i') }}</td>
                                    <td>
                                        <div class="action-cell">
                                            <button class="action-btn view" title="Lihat" onclick="viewPelanggan({{ $p->id }})">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="action-btn edit" title="Edit" onclick="editPelanggan({{ $p->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="action-btn delete" title="Hapus" onclick="deletePelanggan({{ $p->id }}, '{{ addslashes($p->nama) }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINATION --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 12px;">
                <p style="font-size: .875rem; color: var(--c-muted); margin: 0;">
                    Menampilkan {{ $pelanggan->firstItem() }} - {{ $pelanggan->lastItem() }} dari {{ $pelanggan->total() }} data
                </p>
                {{ $pelanggan->links() }}
            </div>
        @endif

    </div>

    {{-- TAB: IMPORT CSV --}}
    <div class="tab-pane {{ $activeTab === 'import' ? 'active' : '' }}" id="pane-import">

        <div style="padding-bottom: 24px;">
            <h3 style="font-size: 1.125rem; margin: 0 0 12px; color: var(--c-text);">Upload File CSV</h3>
            <p style="font-size: .875rem; color: var(--c-muted); margin: 0;">
                Format: .csv atau .txt | Kolom: IDPEL, Nama, Daya, Lembar, RPTAG, RPBK | Maksimal 5 MB
            </p>
        </div>

        <form method="POST" action="{{ route('pelanggan.upload') }}" enctype="multipart/form-data" id="upload-form">
            @csrf

            <div class="drop-zone" id="drop-zone">
                <input type="file" name="csv_file" id="csv-file" accept=".csv,.txt" style="display: none;">
                <div class="drop-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                <p class="drop-title">Drag & drop file CSV di sini</p>
                <p class="drop-sub">atau klik untuk memilih file dari komputer</p>
                <span class="drop-or">— atau —</span>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('csv-file').click()">
                    <i class="bi bi-folder2-open"></i> Pilih File
                </button>
                <div class="file-chip" id="file-chip">
                    <span class="chip-dot"></span>
                    <span id="file-name"></span>
                </div>
            </div>

            @error('csv_file')
                <div class="pln-alert pln-alert-danger" style="margin-top: 12px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $message }}
                </div>
            @enderror
        </form>

        {{-- PREVIEW SETELAH UPLOAD --}}
        @if (!empty($preview))
            <div style="margin-top: 28px; padding-top: 28px; border-top: 2px solid var(--c-border);">
                <h3 style="font-size: 1.125rem; margin: 0 0 16px; color: var(--c-text);">Preview Data</h3>

                {{-- STATS --}}
                <div class="summary-grid">
                    <div class="summary-card blue">
                        <div class="summary-card-label">Total Baris</div>
                        <div class="summary-card-value">{{ $stats['total'] ?? 0 }}</div>
                    </div>
                    <div class="summary-card green">
                        <div class="summary-card-label">Valid</div>
                        <div class="summary-card-value">{{ ($stats['total'] ?? 0) - ($stats['error_rows'] ?? 0) }}</div>
                    </div>
                    <div class="summary-card red">
                        <div class="summary-card-label">Error</div>
                        <div class="summary-card-value">{{ $stats['error_rows'] ?? 0 }}</div>
                    </div>
                </div>

                {{-- TABLE PREVIEW --}}
                <div class="table-wrap">
                    <div class="table-sticky">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>IDPEL</th>
                                    <th>Nama</th>
                                    <th style="width: 90px;">Daya</th>
                                    <th style="width: 80px;">Lembar</th>
                                    <th style="width: 100px;">RPTAG</th>
                                    <th style="width: 100px;">RPBK</th>
                                    <th style="width: 90px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($preview as $row)
                                    <tr class="{{ ($row['_status'] ?? '') === 'error' ? 'error' : '' }}">
                                        <td class="text-muted">{{ $row['_row'] ?? '' }}</td>
                                        <td>{{ $row['idpel'] ?? '' }}</td>
                                        <td>{{ $row['nama'] ?? '' }}</td>
                                        <td>{{ $row['daya'] ?? '' }}</td>
                                        <td>{{ $row['lembar'] ?? '' }}</td>
                                        <td>{{ number_format($row['rptag'] ?? 0) }}</td>
                                        <td>{{ number_format($row['rpbk'] ?? 0) }}</td>
                                        <td>
                                            @if (($row['_status'] ?? '') === 'valid')
                                                <span class="badge badge-success">Valid</span>
                                            @else
                                                <span class="badge badge-danger" title="{{ implode(' | ', $row['_errors'] ?? []) }}">Error</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- BUTTONS --}}
                <div style="display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap;">
                    <form method="POST" action="{{ route('pelanggan.save') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-check"></i> Simpan ke Database (UPSERT)
                        </button>
                    </form>
                    <form method="POST" action="{{ route('pelanggan.reset') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline">
                            <i class="bi bi-arrow-clockwise"></i> Reset Preview
                        </button>
                    </form>
                </div>
            </div>
        @else
            @if ($activeTab === 'import')
                <div class="empty-state" style="margin-top: 28px;">
                    <div class="empty-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <h3 class="empty-title">Belum ada preview</h3>
                    <p class="empty-text">Upload file CSV untuk melihat preview data sebelum disimpan</p>
                </div>
            @endif
        @endif

    </div>

    {{-- TAB: ERROR LOGS --}}
    <div class="tab-pane {{ $activeTab === 'errors' ? 'active' : '' }}" id="pane-errors">

        @if (isset($errorLogs) && $errorLogs->count() > 0)
            <div class="table-wrap">
                <div class="table-sticky">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>File</th>
                                <th style="width: 80px;">Baris</th>
                                <th>Pesan Error</th>
                                <th style="width: 100px;">Tipe</th>
                                <th style="width: 140px;">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach ($errorLogs as $error)
                                <tr>
                                    <td class="text-muted">{{ $no++ }}</td>
                                    <td class="text-muted" style="font-size: .8125rem;">{{ $error->filename ?? '-' }}</td>
                                    <td class="text-muted">{{ $error->row_number ?? '-' }}</td>
                                    <td>{{ $error->error_message ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-warning">{{ $error->error_type ?? 'validation' }}</span>
                                    </td>
                                    <td class="text-muted">{{ $error->created_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-check-circle"></i></div>
                <h3 class="empty-title">Tidak ada error</h3>
                <p class="empty-text">Semua import data berjalan lancar tanpa error</p>
            </div>
        @endif

    </div>

</div>

{{-- LOADING INDICATOR --}}
<div id="pln-loading">
    <div class="spinner"></div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('pane-' + tab)?.classList.add('active');
            this.classList.add('active');
        });
    });

    const dropZone = document.getElementById('drop-zone');
    const csvFile = document.getElementById('csv-file');
    const fileChip = document.getElementById('file-chip');
    const fileName = document.getElementById('file-name');
    const uploadForm = document.getElementById('upload-form');

    if (dropZone && csvFile) {
        dropZone.addEventListener('click', () => csvFile.click());

        ['dragover', 'dragenter'].forEach(evt => {
            dropZone.addEventListener(evt, e => {
                e.preventDefault();
                dropZone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, () => {
                dropZone.classList.remove('dragover');
            });
        });

        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            if (e.dataTransfer.files[0]) {
                csvFile.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });

        csvFile.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            const file = csvFile.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileChip.style.display = 'flex';
                setTimeout(() => {
                    document.getElementById('pln-loading').classList.add('show');
                    uploadForm.submit();
                }, 300);
            }
        }
    }

    function viewPelanggan(id) {
        fetch(`/pelanggan/show/${id}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const p = res.data;
                    alert(`IDPEL: ${p.idpel}\nNama: ${p.nama}\nDaya: ${p.daya}\nLembar: ${p.lembar}\nRPTAG: ${p.rptag}\nRPBK: ${p.rpbk}`);
                }
            });
    }

    function editPelanggan(id) {
        alert('Edit form sedang dikembangkan. ID: ' + id);
    }

    function deletePelanggan(id, nama) {
        if (!confirm(`Hapus pelanggan "${nama}"? Tindakan ini tidak bisa dibatalkan.`)) return;

        fetch(`/pelanggan/destroy/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert(res.message);
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        })
        .catch(e => alert('Error: ' + e.message));
    }
</script>

@endsection
