@extends('layouts.app')
@section('title', 'Data Petugas')

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
    }

    .page-wrap   { max-width: 1000px; padding: 24px 20px 48px; }
    .page-hdr    { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
    .page-hdr h1 { font-size: 1.375rem; font-weight:700; margin:0 0 2px; }
    .page-hdr p  { font-size: 1rem; color:var(--c-muted); margin:0; }

    .btn-add {
        display:inline-flex; align-items:center; gap:7px;
        background:var(--c-primary); color:#fff;
        border:none; border-radius:var(--radius-sm);
        padding:8px 18px; font-size: 1rem; font-weight:600;
        cursor:pointer; text-decoration:none; white-space:nowrap;
        transition:background .15s;
    }
    .btn-add:hover { background:var(--c-primary-dk); color:#fff; }
    .btn-add svg   { width:15px; height:15px; }

    /* Filter card */
    .filter-card {
        background:#fff; border:1px solid var(--c-border);
        border-radius:var(--radius); padding:14px 18px;
        box-shadow:var(--shadow); margin-bottom:16px;
    }
    .filter-row { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
    .fg-f { display:flex; flex-direction:column; gap:4px; flex:1; min-width:140px; }
    .fg-f label {
        font-size: 0.825rem; font-weight:700; text-transform:uppercase;
        letter-spacing:.06em; color:var(--c-muted);
    }
    .fg-f input, .fg-f select {
        height:36px; padding:0 10px;
        border:1px solid var(--c-border); border-radius:var(--radius-sm);
        font-size: 1rem; color:var(--c-text); background:#F8FAFC;
        outline:none; transition:border-color .15s;
    }
    .fg-f input:focus, .fg-f select:focus { border-color:var(--c-primary); background:#fff; }

    .btn-filter {
        display:inline-flex; align-items:center; gap:6px;
        height:36px; padding:0 16px; border-radius:var(--radius-sm);
        border:1px solid var(--c-border); background:#fff;
        font-size: 1rem; color:var(--c-muted); cursor:pointer;
        transition:all .15s; white-space:nowrap;
    }
    .btn-filter:hover { background:var(--c-surface); color:var(--c-text); }
    .btn-filter svg   { width:14px; height:14px; }
    .btn-reset {
        display:inline-flex; align-items:center;
        height:36px; padding:0 14px; border-radius:var(--radius-sm);
        border:1px solid var(--c-border); background:#fff;
        font-size: 1rem; color:var(--c-muted); cursor:pointer;
        text-decoration:none; transition:background .15s;
    }
    .btn-reset:hover { background:var(--c-surface); }

    /* Table */
    .table-card {
        background:#fff; border:1px solid var(--c-border);
        border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden;
    }
    .pet-tbl { width:100%; border-collapse:collapse; table-layout:fixed; }
    .pet-tbl thead tr { background:var(--c-surface); border-bottom:2px solid var(--c-border); }
    .pet-tbl thead th {
        padding:10px 14px; font-size: 0.825rem; font-weight:700;
        text-transform:uppercase; letter-spacing:.06em; color:var(--c-muted);
    }
    .pet-tbl thead th:last-child { text-align:right; }
    .pet-tbl tbody tr { border-bottom:1px solid #F1F5F9; transition:background .1s; }
    .pet-tbl tbody tr:hover { background:var(--c-surface); }
    .pet-tbl tbody tr:last-child { border-bottom:none; }
    .pet-tbl td { padding:10px 14px; font-size: 1rem; vertical-align:middle; }
    .pet-tbl td:last-child { text-align:right; }

    .kode-chip {
        font-family:monospace; font-size: 0.925rem;
        background:#F1F5F9; color:#475569;
        padding:3px 8px; border-radius:6px;
        border:1px solid var(--c-border);
    }
    .ava {
        width:36px; height:36px; border-radius:50%; flex-shrink:0;
        background:#E6F1FB; color:#0C447C;
        display:flex; align-items:center; justify-content:center;
        font-size: 0.9375rem; font-weight:700;
    }
    .user-cell { display:flex; align-items:center; gap:10px; }
    .u-name  { font-weight:600; font-size: 1rem; }
    .u-email { font-size: 0.9rem; color:var(--c-muted); }

    .pill { display:inline-flex; align-items:center; padding:3px 10px; border-radius:99px; font-size: 0.875rem; font-weight:600; }
    .pill-aktif    { background:#EAF3DE; color:#27500A; }
    .pill-nonaktif { background:#FCEBEB; color:#791F1F; }

    .wilayah-tag {
        font-size: 0.925rem; color:#475569;
        display:inline-flex; align-items:center; gap:4px;
    }
    .wilayah-tag svg { width:13px; height:13px; opacity:.6; }

    .action-group {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.action-group form {
    margin: 0;
}

    .act-btn {
        display:inline-flex; align-items:center; justify-content:center;
        width:32px; height:32px; border-radius:var(--radius-sm);
        border:1px solid var(--c-border); background:#fff;
        cursor:pointer; text-decoration:none; color:var(--c-muted);
        transition:all .15s; margin-left:4px;
    }
    .act-btn:hover { background:var(--c-surface); }
    .act-btn.del   { color:#DC2626; }
    .act-btn svg   { width:14px; height:14px; }

    .tbl-footer {
        padding:12px 18px; border-top:1px solid var(--c-border);
        display:flex; justify-content:center;
    }

    @media (max-width: 768px) {
        .filter-row { flex-direction: column; align-items: stretch; }
        .fg-f { width: 100%; }
        .btn-filter, .btn-reset { width: 100%; justify-content: center; }
    }
    @media (max-width: 576px) {
        .page-hdr { flex-direction: column; gap: 12px; align-items: flex-start; }
        .page-hdr h1 { font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="px-4 py-4">

    {{-- Header halaman dan tombol tambah petugas --}}
    <div class="page-hdr">
        <div>
            <h1>Data Petugas</h1>
            <p>Kelola data petugas lapangan</p>
        </div>
        @permission('petugas:create')
        <a href="/datapetugas/create" class="btn-add">
            <svg fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Tambah Petugas
        </a>
        @endpermission
    </div>

    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small mb-3" role="alert">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Form pencarian dan filter data petugas --}}
    <div class="filter-card">
        <form method="GET" action="/datapetugas">
            <div class="filter-row">
                <div class="fg-f">
                    <label>Cari Nama</label>
                    <input type="text" name="search"
                           value="{{ request('search') }}"
                           placeholder="Nama petugas...">
                </div>
                @if($wilayahList->count() > 1)

<div class="fg-f">
    <label>Wilayah</label>
    <select name="wilayah">
        <option value="">Semua Wilayah</option>

        @foreach($wilayahList as $w)
        <option value="{{ $w->wilayah }}"
            {{ request('wilayah') == $w->wilayah ? 'selected' : '' }}>
            {{ $w->wilayah }}
        </option>
        @endforeach

    </select>
</div>

@else

<div class="fg-f">
    <label>Wilayah Kerja</label>
    <input type="text"
           value="{{ $wilayahList->first()->wilayah ?? 'PLN ULP Rivai' }}"
           readonly>
</div>

@endif
                <div class="fg-f">
                    <label>Status</label>
                    <select name="status">
                        <option value="">Semua Status</option>
                        <option value="aktif"    {{ request('status')=='aktif'?'selected':'' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status')=='nonaktif'?'selected':'' }}>Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="btn-filter" style="margin-top:20px">
                    <svg fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Filter
                </button>
                <a href="/datapetugas" class="btn-reset" style="margin-top:20px">Reset</a>
            </div>
        </form>
    </div>

    {{-- Kartu tabel yang menampilkan daftar petugas --}}
    <div class="table-card">
        <div class="table-responsive">
        <table class="pet-tbl">
            <colgroup>
                <col style="width:110px">
                <col style="width:220px">
                <col style="width:200px">
                <col style="width:150px">
                <col style="width:120px">
                <col style="width:100px">
            </colgroup>
            <thead>
                <tr>
                    <th class="ps-4">Kode</th>
                    <th>Nama</th>
                    <th>Wilayah</th>
                    <th>Status</th>
                    <th class="pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Looping untuk menampilkan data petugas dari database --}}
                @forelse($petugas as $p)
                <tr>
                    <td class="ps-4">
                        <span class="kode-chip">{{ $p->kode_petugas }}</span>
                    </td>
                    <td>
                        <div class="u-name">{{ $p->nama }}</div>
                    </td>
                    <td>
                        <span class="wilayah-tag">
                            <svg fill="none" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
                            {{ $p->wilayah }}
                        </span>
                    </td>
                    <td>
    <span class="pill pill-{{ $p->status }}">
        {{ ucfirst($p->status) }}
    </span>
</td>

<td class="pe-4">
    <div class="action-group">

        @permission('petugas:edit')
        <a href="/datapetugas/edit/{{ $p->id }}" class="act-btn" title="Edit">
            <svg fill="none" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"
                      stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                      stroke="currentColor" stroke-width="1.8"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        @endpermission

        {{-- Form untuk menghapus data petugas dengan alert konfirmasi --}}
        @permission('petugas:delete')
        <form action="/datapetugas/delete/{{ $p->id }}" method="POST">
            @csrf
            <button type="submit" class="act-btn del" title="Hapus"
                    onclick="return confirm('Yakin hapus petugas {{ $p->nama }}?')">
                <svg fill="none" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6"
                              stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"
                          stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M10 11v6M14 11v6"
                          stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"
                          stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </form>
        @endpermission

    </div>
</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5" style="color:var(--c-muted);font-size: 1rem">
                        Data petugas tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- Menampilkan link pagination jika data lebih dari satu halaman --}}
        <div class="tbl-footer">
            {{ $petugas->links() }}
        </div>
    </div>

</div>
@endsection
