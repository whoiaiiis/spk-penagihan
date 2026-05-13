{{-- resources/views/staff/cetakinvoice/cetakinvoice.blade.php --}}
@extends('layouts.app')

@section('title', 'Cetak Invoice')

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
    .page-wrap { max-width: 1100px; padding: 24px 20px 40px; }

    .breadcrumb-bar {
        font-size: .8125rem; color: var(--c-muted);
        margin-bottom: 14px; display: flex; align-items: center; gap: 6px;
    }
    .breadcrumb-bar a { color: var(--c-primary); text-decoration: none; }
    .breadcrumb-bar .sep { color: var(--c-light); }

    .page-header { margin-bottom: 20px; }
    .page-header h1 { font-size: 1.375rem; font-weight: 700; margin: 0 0 2px; letter-spacing: -.02em; }
    .page-header p  { font-size: .875rem; color: var(--c-muted); margin: 0; }

    /* Filter card */
    .filter-card {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); padding: 18px 22px;
        box-shadow: var(--shadow); margin-bottom: 20px;
    }
    .filter-row { display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; }
    .fg { display: flex; flex-direction: column; gap: 5px; }
    .fg label {
        font-size: .75rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: .05em; color: var(--c-muted);
    }
    .fg select, .fg input {
        height: 38px; padding: 0 12px; border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); font-size: .875rem; color: var(--c-text);
        background: #F8FAFC; outline: none; font-family: var(--font);
        transition: border-color .15s; min-width: 160px;
    }
    .fg select:focus, .fg input:focus { border-color: var(--c-primary); background: #fff; }

    /* Buttons */
    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 18px; border-radius: var(--radius-sm);
        font-size: .875rem; font-weight: 600; cursor: pointer;
        border: 1px solid transparent; transition: all .15s;
        text-decoration: none; line-height: 1; height: 38px;
        font-family: var(--font);
    }
    .btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .btn-primary { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }
    .btn-primary:hover { background: var(--c-primary-dk); }
    .btn-success { background: var(--c-success); color: #fff; border-color: var(--c-success); }
    .btn-success:hover { background: #15803D; }
    .btn-outline { background: #fff; color: var(--c-muted); border-color: var(--c-border); }
    .btn-outline:hover { background: #F8FAFC; color: var(--c-text); }
    .btn-sm { padding: 6px 12px; font-size: .8125rem; height: auto; }

    /* Stats bar */
    .stats-bar {
        display: flex; gap: 14px; margin-bottom: 16px; flex-wrap: wrap;
    }
    .stat-chip {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius-sm); padding: 10px 16px;
        display: flex; align-items: center; gap: 10px;
        box-shadow: var(--shadow);
    }
    .stat-chip .sc-val { font-size: 1.25rem; font-weight: 700; font-family: var(--mono); }
    .stat-chip .sc-lbl { font-size: .75rem; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .stat-chip.blue .sc-val { color: var(--c-primary); }

    /* Table */
    .table-card {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
    }
    .table-card-header {
        padding: 14px 20px; border-bottom: 1px solid var(--c-border);
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .table-card-header h3 { font-size: .9375rem; font-weight: 600; margin: 0; }
    .tbl-wrap { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; font-size: .875rem; }
    thead th {
        background: #F8FAFC; padding: 10px 14px;
        text-align: left; font-size: .75rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--c-muted); border-bottom: 1px solid var(--c-border);
        white-space: nowrap;
    }
    thead th.center { text-align: center; }
    thead th.right  { text-align: right; }
    tbody tr { border-bottom: 1px solid #F1F5F9; transition: background .1s; }
    tbody tr:hover { background: #F8FAFC; }
    tbody tr:last-child { border-bottom: none; }
    td { padding: 11px 14px; vertical-align: middle; white-space: nowrap; }
    td.mono { font-family: var(--mono); font-size: .82rem; }
    td.right { text-align: right; }
    td.center { text-align: center; }

    .pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 99px; font-size: .75rem; font-weight: 600;
    }
    .pill.sudah { background: var(--c-success-lt); color: var(--c-success); border: 1px solid var(--c-success-bd); }
    .pill.belum { background: var(--c-warning-lt); color: var(--c-warning); border: 1px solid #FDE68A; }

    /* Checkbox */
    .cb-wrap { display: flex; align-items: center; justify-content: center; }
    input[type="checkbox"] {
        width: 16px; height: 16px; cursor: pointer;
        accent-color: var(--c-primary);
    }

    /* Print action bar */
    .print-bar {
        display: none; position: sticky; bottom: 16px;
        background: var(--c-primary); color: #fff;
        border-radius: var(--radius); padding: 14px 20px;
        box-shadow: 0 8px 32px rgba(0,91,170,.35);
        align-items: center; justify-content: space-between; gap: 12px;
        z-index: 100; margin-top: 12px;
    }
    .print-bar.visible { display: flex; }
    .print-bar span { font-size: .9375rem; font-weight: 600; }
    .btn-white {
        background: #fff; color: var(--c-primary); border: none;
        padding: 8px 18px; border-radius: var(--radius-sm);
        font-size: .875rem; font-weight: 700; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: background .15s;
    }
    .btn-white:hover { background: #EFF6FF; }

    .empty-state { padding: 48px 24px; text-align: center; color: var(--c-muted); }
    .empty-state svg { opacity: .3; margin: 0 auto 12px; display: block; }
    .empty-state p { margin: 0; font-size: .9375rem; }

    @media (max-width: 640px) {
        .filter-row { flex-direction: column; }
        .fg select, .fg input { min-width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="px-4 py-4">

    <div class="breadcrumb-bar">
        <a href="/dashboard">Dashboard</a>
        <span class="sep">›</span>
        <span>Cetak Invoice</span>
    </div>

    <div class="page-header">
        <h1>Cetak Invoice Tagihan</h1>
        <p>Pilih data penagihan yang ingin dicetak, 2 invoice per lembar A4</p>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" action="/invoice" id="filter-form">
            <div class="filter-row">
                <div class="fg">
                    <label>Bulan Tagih</label>
                    <input type="month" name="bulan" value="{{ $bulan }}">
                </div>
                <div class="fg">
                    <label>Petugas</label>
                    <select name="petugas_id">
                        <option value="">Semua Petugas</option>
                        @foreach($petugasList as $p)
                            <option value="{{ $p->id }}" {{ $petugasId == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="fg">
                    <label>Cari Pelanggan</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama / ID Pelanggan...">
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg fill="none" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Filter
                </button>
                <a href="/invoice" class="btn btn-outline">Reset</a>
            </div>
        </form>
    </div>

    {{-- Stats --}}
    @if($tagihan->total() > 0)
    <div class="stats-bar">
        <div class="stat-chip blue">
            <div>
                <div class="sc-val">{{ $tagihan->total() }}</div>
                <div class="sc-lbl">Total Tagihan</div>
            </div>
        </div>
        <div class="stat-chip">
            <div>
                <div class="sc-val" style="color:var(--c-success)">
                    {{ $tagihan->where('status','sudah')->count() }}
                </div>
                <div class="sc-lbl">Sudah Bayar</div>
            </div>
        </div>
        <div class="stat-chip">
            <div>
                <div class="sc-val" style="color:var(--c-warning)">
                    {{ $tagihan->where('status','belum')->count() }}
                </div>
                <div class="sc-lbl">Belum Bayar</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <form id="print-form" method="GET" action="/invoice/print" target="_blank">
        <input type="hidden" name="bulan"      value="{{ $bulan }}">
        <input type="hidden" name="petugas_id" value="{{ $petugasId }}">
        <input type="hidden" name="ids"        id="selected-ids" value="">

        <div class="table-card">
            <div class="table-card-header">
                <h3>Daftar Tagihan</h3>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <label style="font-size:.8125rem;color:var(--c-muted);display:flex;align-items:center;gap:6px;cursor:pointer">
                        <input type="checkbox" id="check-all" onchange="toggleAll(this)"> Pilih Semua
                    </label>
                    <button type="button" class="btn btn-success btn-sm" onclick="printAll()">
                        <svg fill="none" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Cetak Semua
                    </button>
                </div>
            </div>

            @if($tagihan->isEmpty())
            <div class="empty-state">
                <svg width="52" height="52" fill="none" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <p>Tidak ada data tagihan untuk filter ini.</p>
            </div>
            @else
            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="center" style="width:40px">
                                <input type="checkbox" id="check-all-head" onchange="toggleAll(this)">
                            </th>
                            <th>ID Pelanggan</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Petugas</th>
                            <th class="right">Daya</th>
                            <th class="right">RPTA (Rp)</th>
                            <th>Tgl Tagih</th>
                            <th>Status</th>
                            <th class="center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tagihan as $t)
                        <tr>
                            <td class="center">
                                <div class="cb-wrap">
                                    <input type="checkbox" class="row-cb" value="{{ $t->tagih_id }}"
                                           onchange="updateSelection()">
                                </div>
                            </td>
                            <td class="mono">{{ $t->idpel }}</td>
                            <td style="font-weight:500">{{ $t->nama }}</td>
                            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;color:var(--c-muted);font-size:.82rem">
                                {{ $t->alamat ?: '—' }}
                            </td>
                            <td style="font-size:.82rem;color:var(--c-muted)">{{ $t->nama_petugas }}</td>
                            <td class="right mono">{{ number_format($t->daya) }}</td>
                            <td class="right mono" style="font-weight:600">
                                {{ number_format($t->rpta, 0, ',', '.') }}
                            </td>
                            <td class="mono" style="color:var(--c-muted)">
                                {{ $t->tanggal_tagih ? \Carbon\Carbon::parse($t->tanggal_tagih)->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                <span class="pill {{ $t->status }}">
                                    {{ $t->status === 'sudah' ? 'Sudah' : 'Belum' }}
                                </span>
                            </td>
                            <td class="center">
                                <a href="/invoice/print?ids={{ $t->tagih_id }}&bulan={{ $bulan }}"
                                   target="_blank" class="btn btn-outline btn-sm">
                                    <svg fill="none" viewBox="0 0 24 24" width="13" height="13"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    Cetak
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div style="padding:14px 20px;border-top:1px solid var(--c-border);">
                {{ $tagihan->links() }}
            </div>
            @endif
        </div>
    </form>

    {{-- Sticky print bar --}}
    <div class="print-bar" id="print-bar">
        <span id="selected-count">0 invoice dipilih</span>
        <div style="display:flex;gap:10px;">
            <button type="button" class="btn-white" onclick="clearSelection()">
                Batal Pilih
            </button>
            <button type="button" class="btn-white" onclick="printSelected()">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Cetak Pilihan
            </button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let selectedIds = new Set();

function toggleAll(cb) {
    document.querySelectorAll('.row-cb').forEach(c => {
        c.checked = cb.checked;
        if (cb.checked) selectedIds.add(c.value);
        else selectedIds.delete(c.value);
    });
    // sync both check-all boxes
    document.querySelectorAll('#check-all, #check-all-head').forEach(c => c.checked = cb.checked);
    updateBar();
}

function updateSelection() {
    selectedIds.clear();
    document.querySelectorAll('.row-cb:checked').forEach(c => selectedIds.add(c.value));
    updateBar();
}

function updateBar() {
    const bar   = document.getElementById('print-bar');
    const count = document.getElementById('selected-count');
    const n     = selectedIds.size;
    if (n > 0) {
        bar.classList.add('visible');
        count.textContent = n + ' invoice dipilih (' + Math.ceil(n / 2) + ' lembar A4)';
    } else {
        bar.classList.remove('visible');
    }
}

function clearSelection() {
    selectedIds.clear();
    document.querySelectorAll('.row-cb, #check-all, #check-all-head').forEach(c => c.checked = false);
    updateBar();
}

function printSelected() {
    if (selectedIds.size === 0) return;
    const ids = Array.from(selectedIds).join(',');
    const bulan = document.querySelector('input[name="bulan"]').value;
    window.open('/invoice/print?ids=' + ids + '&bulan=' + bulan, '_blank');
}

function printAll() {
    const bulan     = '{{ $bulan }}';
    const petugasId = '{{ $petugasId }}';
    window.open('/invoice/print?bulan=' + bulan + '&petugas_id=' + petugasId, '_blank');
}
</script>
@endpush