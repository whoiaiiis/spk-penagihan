{{-- resources/views/staff/invoice/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Daftar Invoice')

@push('styles')
<style>
    :root {
        --c-bg:         #F0F4F8;
        --c-surface:    #FFFFFF;
        --c-border:     #E2E8F0;
        --c-primary:    #005baa;
        --c-primary-dk: #004080;
        --c-primary-lt: #EFF6FF;
        --c-success:    #16A34A;
        --c-success-lt: #F0FDF4;
        --c-success-bd: #BBF7D0;
        --c-danger:     #DC2626;
        --c-danger-lt:  #FEF2F2;
        --c-danger-bd:  #FECACA;
        --c-warning:    #D97706;
        --c-warning-lt: #FFFBEB;
        --c-text:       #1E293B;
        --c-muted:      #64748B;
        --c-light:      #94A3B8;
        --radius:       12px;
        --radius-sm:    8px;
        --shadow:       0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
        --font:         'Segoe UI', system-ui, sans-serif;
        --mono:         'Courier New', monospace;
    }

    body { background: var(--c-bg); }
    .page-wrap { max-width: 1280px; padding: 24px 20px 40px; }

    .page-header { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; }
    .page-header-text h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 2px; letter-spacing: -.02em; }
    .page-header-text p  { font-size: 1rem; color: var(--c-muted); margin: 0; }
    .page-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

    /* Filter card */
    .filter-card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius); padding: 18px 22px; box-shadow: var(--shadow); margin-bottom: 20px; }
    .filter-row { display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; }
    .fg { display: flex; flex-direction: column; gap: 5px; }
    .fg label { font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); }
    .fg select, .fg input { height: 38px; padding: 0 12px; border: 1px solid var(--c-border); border-radius: var(--radius-sm); font-size: 1rem; color: var(--c-text); background: #F8FAFC; outline: none; font-family: var(--font); transition: border-color .15s; min-width: 140px; }
    .fg select:focus, .fg input:focus { border-color: var(--c-primary); background: #fff; }

    /* Buttons */
    .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--radius-sm); font-size: 1rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; transition: all .15s; text-decoration: none; line-height: 1; height: 38px; font-family: var(--font); }
    .btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .btn-primary { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }
    .btn-primary:hover { background: var(--c-primary-dk); }
    .btn-success { background: var(--c-success); color: #fff; border-color: var(--c-success); }
    .btn-success:hover { background: #15803D; }
    .btn-outline { background: #fff; color: var(--c-muted); border-color: var(--c-border); }
    .btn-outline:hover { background: #F8FAFC; color: var(--c-text); }
    .btn-sm { padding: 6px 12px; font-size: 0.9375rem; height: auto; }

    /* Stats bar */
    .stats-bar { display: flex; gap: 14px; margin-bottom: 16px; flex-wrap: wrap; }
    .stat-chip { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-sm); padding: 10px 16px; display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow); }
    .stat-chip .sc-val { font-size: 1.375rem; font-weight: 700; font-family: var(--mono); }
    .stat-chip .sc-lbl { font-size: 0.875rem; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .stat-chip.blue .sc-val { color: var(--c-primary); }

    /* Table */
    .table-card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .table-card-header { padding: 14px 20px; border-bottom: 1px solid var(--c-border); display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .table-card-header h3 { font-size: 1.0625rem; font-weight: 600; margin: 0; }
    .tbl-wrap { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; font-size: 1rem; }
    thead th { background: #F8FAFC; padding: 10px 14px; text-align: left; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); border-bottom: 1px solid var(--c-border); white-space: nowrap; }
    thead th.center { text-align: center; }
    thead th.right  { text-align: right; }
    tbody tr { border-bottom: 1px solid #F1F5F9; transition: background .1s; }
    tbody tr:hover { background: #F8FAFC; }
    tbody tr:last-child { border-bottom: none; }
    td { padding: 11px 14px; vertical-align: middle; white-space: nowrap; }
    td.mono { font-family: var(--mono); font-size: 0.945rem; }
    td.right { text-align: right; }
    td.center { text-align: center; }

    .pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 0.875rem; font-weight: 600; }
    .pill.sudah { background: var(--c-success-lt); color: var(--c-success); border: 1px solid var(--c-success-bd); }
    .pill.belum { background: var(--c-warning-lt); color: var(--c-warning); border: 1px solid #FDE68A; }

    /* Checkbox */
    .cb-wrap { display: flex; align-items: center; justify-content: center; }
    input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: var(--c-primary); }

    /* Sticky action bar */
    .action-bar { display: none; position: sticky; bottom: 16px; background: var(--c-primary); color: #fff; border-radius: var(--radius); padding: 14px 20px; box-shadow: 0 8px 32px rgba(0,91,170,.35); align-items: center; justify-content: space-between; gap: 12px; z-index: 100; margin-top: 12px; }
    .action-bar.visible { display: flex; }
    .action-bar span { font-size: 1.0625rem; font-weight: 600; }
    .btn-white { background: #fff; color: var(--c-primary); border: none; padding: 8px 16px; border-radius: var(--radius-sm); font-size: 1rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background .15s; }
    .btn-white:hover { background: #EFF6FF; }

    .alert { padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: var(--c-success-lt); color: var(--c-success); border: 1px solid var(--c-success-bd); }
    .alert-error   { background: var(--c-danger-lt);  color: var(--c-danger);  border: 1px solid var(--c-danger-bd); }
    .alert-warning { background: var(--c-warning-lt); color: var(--c-warning); border: 1px solid #FDE68A; }
    .alert svg { width: 18px; height: 18px; flex-shrink: 0; }

    .empty-state { padding: 48px 24px; text-align: center; color: var(--c-muted); }
    .empty-state svg { opacity: .3; margin: 0 auto 12px; display: block; }
    .empty-state p { margin: 0; font-size: 1.0625rem; }

    @media (max-width: 768px) {
        .page-wrap { padding: 16px 12px 28px; }
        .page-header { flex-direction: column; gap: 12px; }
        .page-header-actions { width: 100%; flex-wrap: wrap; }
        .page-header-actions .btn { flex: 1 1 100%; }
        .filter-row { flex-direction: column; gap: 10px; }
        .fg { width: 100%; }
        .fg select, .fg input { min-width: 100%; height: 40px; }
        .stats-bar { gap: 10px; }
        .tbl-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .action-bar { flex-direction: column; align-items: flex-start; }
    }
    @media (max-width: 576px) {
        .page-wrap { padding: 14px 10px 24px; }
        .page-header-text h1 { font-size: 1.125rem; }
        .stats-bar { gap: 8px; }
        .stat-chip .sc-val { font-size: 1.125rem; }
        table { font-size: 0.875rem; }
        td, thead th { padding: 8px 10px; }
    }
</style>
@endpush

@section('content')
<div class="px-4 py-4">

    {{-- Header halaman daftar invoice dan tombol generate --}}
    <div class="page-header">
        <div class="page-header-text">
            <h1>Daftar Invoice</h1>
            <p>Kelola invoice dari hasil ranking pelanggan SPK</p>
        </div>
        <div class="page-header-actions">
            <form action="{{ route('invoice.generate') }}" method="POST"
                  onsubmit="return confirm('Generate semua invoice dari ranking untuk periode ini?')">
                @csrf
                <div style="display:flex; gap:8px;">
                    <input type="month" name="periode" required value="{{ $periode ?? date('Y-m') }}" style="height:38px; border:1px solid var(--c-border); border-radius:var(--radius-sm); padding:0 12px; font-family:var(--font);">
                    <button type="submit" class="btn btn-primary">
                        <svg fill="none" viewBox="0 0 24 24">
                            <path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Generate Semua Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Menampilkan notifikasi sukses, error, atau peringatan --}}
    @if(session('success'))
    <div class="alert alert-success">
        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-error">
        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning">
        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
        <span>{{ session('warning') }}</span>
    </div>
    @endif

    {{-- Form pencarian dan filter daftar invoice --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('invoice.index') }}">
            <div class="filter-row">
                <div class="fg">
                    <label>Periode</label>
                    <select name="periode">
                        <option value="">Semua Periode</option>
                        @foreach($periodeList ?? [] as $p)
                            <option value="{{ $p }}" {{ request('periode', $periode) === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fg">
                    <label>Cari Pelanggan</label>
                    <input type="text" name="search" value="{{ request('search', '') }}" placeholder="Nama / IDPEL...">
                </div>
                <div class="fg">
                    <label>Status Cetak</label>
                    <select name="status_cetak">
                        <option value="">Semua Status</option>
                        <option value="belum" {{ request('status_cetak') === 'belum' ? 'selected' : '' }}>Belum Cetak</option>
                        <option value="sudah" {{ request('status_cetak') === 'sudah' ? 'selected' : '' }}>Sudah Cetak</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Petugas</label>
                    <select name="kode_petugas">
                        <option value="">Semua Petugas</option>
                        @foreach($petugasList as $p)
                            <option value="{{ $p->kode_petugas }}" {{ request('kode_petugas') === $p->kode_petugas ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="fg">
                    <label>Ranking (dari)</label>
                    <input type="number" name="ranking_min" min="1" value="{{ request('ranking_min', '') }}" placeholder="Min">
                </div>
                <div class="fg">
                    <label>Ranking (sampai)</label>
                    <input type="number" name="ranking_max" min="1" value="{{ request('ranking_max', '') }}" placeholder="Max">
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg fill="none" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Filter
                </button>
                <a href="{{ route('invoice.index') }}" class="btn btn-outline">Reset</a>
            </div>
        </form>
    </div>

    {{-- Menampilkan ringkasan total invoice dan status cetak --}}
    @if($invoice->total() > 0)
    <div class="stats-bar">
        <div class="stat-chip blue">
            <div>
                <div class="sc-val">{{ $stats['total'] }}</div>
                <div class="sc-lbl">Total Invoice</div>
            </div>
        </div>
        <div class="stat-chip">
            <div>
                <div class="sc-val" style="color:var(--c-success)">
                    {{ $stats['sudah'] }}
                </div>
                <div class="sc-lbl">Sudah Cetak</div>
            </div>
        </div>
        <div class="stat-chip">
            <div>
                <div class="sc-val" style="color:var(--c-warning)">
                    {{ $stats['belum'] }}
                </div>
                <div class="sc-lbl">Belum Cetak</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabel daftar invoice pelanggan dari hasil ranking --}}
    <form id="action-form">
        <div class="table-card">
            {{-- Header tabel dan tombol aksi massal (Cetak Pilihan, Export PDF) --}}
            <div class="table-card-header">
                <h3>Hasil Ranking Pelanggan ({{ $invoice->total() }})</h3>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <label style="font-size: 0.9375rem;color:var(--c-muted);display:flex;align-items:center;gap:6px;cursor:pointer">
                        <input type="checkbox" id="check-all" onchange="toggleAll(this)"> Pilih Semua
                    </label>
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkPrint()">
                        <svg fill="none" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Cetak Pilihan
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="bulkExportPdf()">
                        <svg fill="none" viewBox="0 0 24 24"><path d="M12 2v16M7 13l5 5 5-5M4 20h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Export PDF
                    </button>
                </div>
            </div>

            @if($invoice->isEmpty())
            <div class="empty-state">
                <svg width="52" height="52" fill="none" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <p>Data ranking belum tersedia. Silakan generate ranking terlebih dahulu.</p>
            </div>
            @else
            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            {{-- 1. Checkbox --}}
                            <th class="center" style="width:40px">
                                <input type="checkbox" id="check-all-head" onchange="toggleAll(this)">
                            </th>
                            {{-- 2. Ranking --}}
                            <th class="right" style="width:70px">Ranking</th>
                            {{-- 3. ID Pelanggan --}}
                            <th>ID Pelanggan</th>
                            {{-- 4. Nama Pelanggan --}}
                            <th>Nama Pelanggan</th>
                            {{-- 5. Unitap (pengganti RBM) --}}
                            <th>Unitap</th>
                            {{-- 6. Petugas --}}
                            <th>Petugas</th>
                            {{-- 7. Daya (VA) --}}
                            <th class="right">Daya (VA)</th>
                            {{-- 8. RPTAG --}}
                            <th class="right">RPTAG (Rp)</th>
                            {{-- 9. Nilai Akhir SAW --}}
                            <th class="right">Nilai SAW</th>
                            {{-- 10. Status Cetak --}}
                            <th>Status Cetak</th>
                            {{-- 11. Aksi --}}
                            <th class="center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Looping daftar invoice pelanggan --}}
                        @foreach($invoice as $inv)
                        <tr>
                            {{-- 1. Checkbox --}}
                            <td class="center">
                                <div class="cb-wrap">
                                    <input type="checkbox" class="row-cb"
                                           value="{{ $inv->ranking_id }}"
                                           onchange="updateSelection()">
                                </div>
                            </td>

                            {{-- 2. Ranking --}}
                            <td class="right mono" style="font-weight:700;">
                                {{ $inv->ranking }}
                            </td>

                            {{-- 3. IDPEL --}}
                            <td class="mono">{{ $inv->idpel }}</td>

                            {{-- 4. Nama Pelanggan --}}
                            <td style="font-weight:500; max-width:180px; white-space:normal;">
                                {{ $inv->nama }}
                            </td>

                            {{-- 5. Unitap (✅ ganti rbm) --}}
                            <td>
                                <span style="background:#E2E8F0; padding:2px 6px; border-radius:4px;
                                             font-size: 0.825rem; font-weight:600;">
                                    {{ $inv->unitap ?: '-' }}
                                </span>
                            </td>

                            {{-- 6. Petugas --}}
                            <td>
                                <div style="font-weight:600; font-size: 0.925rem;">
                                    {{ $inv->kode_petugas ?: '-' }}
                                </div>
                                <div style="font-size: 0.875rem; color:var(--c-muted);">
                                    {{ $inv->nama_petugas ?: '-' }}
                                </div>
                            </td>

                            {{-- 7. Daya --}}
                            <td class="right mono">
                                {{ number_format($inv->daya, 0, ',', '.') }}
                            </td>

                            {{-- 8. RPTAG --}}
                            <td class="right mono" style="font-weight:600;">
                                {{ number_format($inv->rptag, 0, ',', '.') }}
                            </td>

                            {{-- 9. Nilai Akhir SAW --}}
                            <td class="right mono">
                                {{ number_format($inv->nilai_akhir, 6) }}
                            </td>

                            {{-- 10. Status Cetak --}}
                            <td>
                                @if($inv->status_cetak === 'sudah')
                                    <span class="pill sudah">✓ Sudah</span>
                                @else
                                    <span class="pill belum">Belum</span>
                                @endif
                            </td>

                            {{-- 11. Aksi --}}
                            <td class="center" style="white-space:normal;">
                                <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                    {{-- Cetak --}}
                                    <a href="{{ route('invoice.print') }}?idpel={{ $inv->idpel }}"
                                       target="_blank" class="btn btn-outline btn-sm">
                                        <svg fill="none" viewBox="0 0 24 24" width="13" height="13">
                                            <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        Cetak
                                    </a>
                                    {{-- Hapus --}}
                                    @if($inv->invoice_id)
                                    <form action="{{ route('invoice.destroy', $inv->invoice_id) }}"
                                          method="POST" style="display:inline;"
                                          onsubmit="return confirm('Hapus invoice ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm"
                                                style="color:var(--c-danger);border-color:var(--c-danger-bd);">
                                            <svg fill="none" viewBox="0 0 24 24" width="13" height="13">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Link pagination tabel invoice --}}
            <div style="padding:14px 20px; border-top:1px solid var(--c-border);">
                {{ $invoice->links() }}
            </div>
            @endif
        </div>
    </form>

    {{-- Action bar yang muncul di bawah layar saat ada baris yang dipilih --}}
    <div class="action-bar" id="action-bar">
        <span id="selected-count">0 pelanggan dipilih</span>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button type="button" class="btn-white" onclick="clearSelection()">Batal Pilih</button>
            <button type="button" class="btn-white" onclick="bulkPrint()">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                    <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Cetak Pilihan
            </button>
            <button type="button" class="btn-white" onclick="bulkExportPdf()">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                    <path d="M12 2v16M7 13l5 5 5-5M4 20h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Export PDF
            </button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Script untuk mengatur fitur pilih semua (checkbox) dan aksi cetak/export massal
let selectedIds = new Set();

function toggleAll(cb) {
    document.querySelectorAll('.row-cb').forEach(c => {
        c.checked = cb.checked;
        if (cb.checked) selectedIds.add(parseInt(c.value));
        else selectedIds.delete(parseInt(c.value));
    });
    document.querySelectorAll('#check-all, #check-all-head').forEach(c => c.checked = cb.checked);
    updateBar();
}

function updateSelection() {
    selectedIds.clear();
    document.querySelectorAll('.row-cb:checked').forEach(c => selectedIds.add(parseInt(c.value)));
    updateBar();
}

function updateBar() {
    const bar   = document.getElementById('action-bar');
    const count = document.getElementById('selected-count');
    const n     = selectedIds.size;
    if (n > 0) {
        bar.classList.add('visible');
        count.textContent = n + ' pelanggan dipilih';
    } else {
        bar.classList.remove('visible');
    }
}

function clearSelection() {
    selectedIds.clear();
    document.querySelectorAll('.row-cb, #check-all, #check-all-head').forEach(c => c.checked = false);
    updateBar();
}

function bulkPrint() {
    if (selectedIds.size === 0) { alert('Pilih minimal 1 pelanggan'); return; }
    window.open('{{ route("invoice.print") }}?ids=' + Array.from(selectedIds).join(','), '_blank');
}

function bulkExportPdf() {
    if (selectedIds.size === 0) { alert('Pilih minimal 1 pelanggan'); return; }
    window.location.href = '{{ route("invoice.exportPdf") }}?ids=' + Array.from(selectedIds).join(',');
}
</script>
@endpush
