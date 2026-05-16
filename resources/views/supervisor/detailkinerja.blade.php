{{-- resources/views/supervisor/detail_kinerja.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Kinerja — {{ $petugas->nama }}')

@push('styles')
<style>
    :root {
        --c-bg:         #F0F4F8;
        --c-surface:    #FFFFFF;
        --c-border:     #E2E8F0;
        --c-primary:    #2563EB;
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
    .page-wrap { max-width: 1100px; padding: 24px 20px 40px; }

    .breadcrumb-bar {
        font-size: .8125rem; color: var(--c-muted);
        margin-bottom: 14px; display: flex; align-items: center; gap: 6px;
    }
    .breadcrumb-bar a { color: var(--c-primary); text-decoration: none; }
    .breadcrumb-bar .sep { color: var(--c-light); }

    /* ── Profile header ──────────────────────────────────────────────────── */
    .profile-header {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); padding: 24px 28px;
        box-shadow: var(--shadow); margin-bottom: 20px;
        display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
    }
    .avatar {
        width: 56px; height: 56px; border-radius: 14px;
        background: var(--c-primary-lt); color: var(--c-primary);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.375rem; font-weight: 700; flex-shrink: 0;
        border: 1px solid #BFDBFE;
    }
    .profile-info { flex: 1; }
    .profile-info h2 { font-size: 1.125rem; font-weight: 700; margin: 0 0 3px; }
    .profile-info p  { font-size: .875rem; color: var(--c-muted); margin: 0; }
    .profile-meta { display: flex; gap: 20px; flex-wrap: wrap; }
    .profile-meta-item { text-align: right; }
    .profile-meta-item .pm-label { font-size: .75rem; color: var(--c-muted); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; }
    .profile-meta-item .pm-val   { font-size: 1rem; font-weight: 700; font-family: var(--mono); color: var(--c-text); }

    /* ── Kinerja meter ───────────────────────────────────────────────────── */
    .kinerja-meter {
        display: flex; align-items: center; gap: 16px;
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); padding: 18px 24px;
        box-shadow: var(--shadow); margin-bottom: 20px;
    }
    .meter-ring { flex-shrink: 0; }
    .meter-ring svg { display: block; }
    .meter-info { flex: 1; }
    .meter-info h3 { font-size: 1.75rem; font-weight: 700; margin: 0 0 2px; font-family: var(--mono); }
    .meter-info p  { font-size: .875rem; color: var(--c-muted); margin: 0; }
    .meter-stats { display: flex; gap: 24px; margin-top: 10px; flex-wrap: wrap; }
    .ms-item .ms-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--c-muted); }
    .ms-item .ms-val   { font-size: 1.125rem; font-weight: 700; font-family: var(--mono); }
    .ms-item.green .ms-val { color: var(--c-success); }
    .ms-item.red   .ms-val { color: var(--c-danger); }
    .ms-item.blue  .ms-val { color: var(--c-primary); }

    /* ── Saldo cards ─────────────────────────────────────────────────────── */
    .saldo-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 20px; }
    .sc {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); padding: 16px 20px; box-shadow: var(--shadow);
    }
    .sc .sc-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--c-muted); margin-bottom: 4px; }
    .sc .sc-val   { font-size: 1.25rem; font-weight: 700; font-family: var(--mono); }
    .sc.rpta  .sc-val { color: var(--c-text); }
    .sc.tagih .sc-val { color: var(--c-success); }
    .sc.sisa  .sc-val { color: var(--c-danger); }

    /* ── Table card ──────────────────────────────────────────────────────── */
    .table-card {
        background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
    }
    .table-card-header {
        padding: 16px 20px; border-bottom: 1px solid var(--c-border);
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .table-card-header h3 { font-size: .9375rem; font-weight: 600; margin: 0; }

    /* Filter pill tabs */
    .pill-tabs { display: flex; gap: 6px; }
    .pill-tab {
        padding: 5px 14px; border-radius: 99px; font-size: .8125rem; font-weight: 600;
        cursor: pointer; border: 1px solid var(--c-border); background: #F8FAFC; color: var(--c-muted);
        transition: all .15s;
    }
    .pill-tab.active { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }
    .pill-tab:hover:not(.active) { background: #F1F5F9; }

    .tbl-wrap { overflow-x: auto; }
    .detail-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
    .detail-table thead th {
        background: #F8FAFC; padding: 10px 14px; text-align: left;
        font-size: .75rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: .05em; color: var(--c-muted);
        border-bottom: 1px solid var(--c-border); white-space: nowrap;
    }
    .detail-table thead th.right { text-align: right; }
    .detail-table tbody tr { border-bottom: 1px solid #F1F5F9; transition: background .1s; }
    .detail-table tbody tr:hover { background: #F8FAFC; }
    .detail-table tbody tr:last-child { border-bottom: none; }
    .detail-table tbody tr.row-belum { background: #FFFBEB; }
    .detail-table tbody tr.row-belum:hover { background: #FEF9C3; }
    .detail-table td { padding: 11px 14px; vertical-align: middle; white-space: nowrap; }
    .detail-table td.right { text-align: right; }
    .detail-table td.mono  { font-family: var(--mono); font-size: .82rem; }

    .pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 99px; font-size: .75rem; font-weight: 600;
    }
    .pill.sudah { background: var(--c-success-lt); color: var(--c-success); border: 1px solid var(--c-success-bd); }
    .pill.belum { background: var(--c-warning-lt); color: var(--c-warning); border: 1px solid var(--c-warning-bd); }

    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: var(--radius-sm);
        font-size: .8125rem; font-weight: 600; cursor: pointer;
        border: 1px solid transparent; transition: all .15s;
        text-decoration: none; line-height: 1;
    }
    .btn-outline { background: #fff; color: var(--c-muted); border-color: var(--c-border); }
    .btn-outline:hover { background: #F8FAFC; color: var(--c-text); }

    @media (max-width: 640px) {
        .profile-header { flex-direction: column; }
        .profile-meta   { flex-direction: column; gap: 8px; }
        .profile-meta-item { text-align: left; }
        .kinerja-meter  { flex-direction: column; text-align: center; }
        .meter-stats    { justify-content: center; }
        .saldo-grid     { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid page-wrap">

    {{-- Breadcrumb --}}
    <div class="breadcrumb-bar">
        <a href="/dashboard">Dashboard</a>
        <span class="sep">›</span>
        <a href="{{ route('monitoring.index', ['bulan' => $bulan]) }}">Monitoring Kinerja</a>
        <span class="sep">›</span>
        <span>{{ $petugas->nama }}</span>
    </div>

    {{-- Profile header --}}
    <div class="profile-header">
        <div class="avatar">{{ strtoupper(substr($petugas->nama, 0, 2)) }}</div>
        <div class="profile-info">
            <h2>{{ $petugas->nama }}</h2>
            <p>{{ $petugas->kode_petugas }} &nbsp;·&nbsp; Wilayah {{ $petugas->wilayah }}</p>
        </div>
        <div class="profile-meta">
            <div class="profile-meta-item">
                <div class="pm-label">Bulan</div>
                <div class="pm-val">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('M Y') }}
                </div>
            </div>
        </div>
        <a href="{{ route('monitoring.index', ['bulan' => $bulan]) }}" class="btn btn-outline">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Kembali
        </a>
    </div>

    {{-- Kinerja meter --}}
    @php
        $persen   = $stats['persen'] ?? 0;
        $pilColor = $persen >= 80 ? '#16A34A' : ($persen >= 60 ? '#D97706' : '#DC2626');
        $kategori = $persen >= 80 ? 'Baik' : ($persen >= 60 ? 'Cukup' : 'Kurang');
        // SVG circle params
        $r = 38; $circ = 2 * M_PI * $r;
        $dash = $circ * ($persen / 100);
        $gap  = $circ - $dash;
    @endphp
    <div class="kinerja-meter">
        <div class="meter-ring">
            <svg width="100" height="100" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="{{ $r }}" fill="none" stroke="#E2E8F0" stroke-width="8"/>
                <circle cx="50" cy="50" r="{{ $r }}" fill="none"
                        stroke="{{ $pilColor }}" stroke-width="8"
                        stroke-dasharray="{{ $dash }} {{ $gap }}"
                        stroke-dashoffset="{{ $circ * 0.25 }}"
                        stroke-linecap="round"/>
                <text x="50" y="54" text-anchor="middle"
                      font-size="16" font-weight="700"
                      font-family="monospace" fill="{{ $pilColor }}">{{ $persen }}%</text>
            </svg>
        </div>
        <div class="meter-info">
            <h3 style="color:{{ $pilColor }}">{{ $persen }}% — {{ $kategori }}</h3>
            <p>Persentase kinerja penagihan bulan ini</p>
            <div class="meter-stats">
                <div class="ms-item blue">
                    <div class="ms-label">Total Target</div>
                    <div class="ms-val">{{ number_format($stats['total'] ?? 0) }}</div>
                </div>
                <div class="ms-item green">
                    <div class="ms-label">Sudah</div>
                    <div class="ms-val">{{ number_format($stats['sudah'] ?? 0) }}</div>
                </div>
                <div class="ms-item red">
                    <div class="ms-label">Belum</div>
                    <div class="ms-val">{{ number_format($stats['belum'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Saldo cards --}}
    <div class="saldo-grid">
        <div class="sc rpta">
            <div class="sc-label">Total RPTA (Target)</div>
            <div class="sc-val">Rp {{ number_format($stats['total_rptag'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="sc tagih">
            <div class="sc-label">Berhasil Ditagih</div>
            <div class="sc-val">Rp {{ number_format($stats['total_tagih'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="sc sisa">
            <div class="sc-label">Sisa Saldo Belum Tertagih</div>
            <div class="sc-val">Rp {{ number_format($stats['sisa_saldo'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Detail table --}}
    <div class="table-card">
        <div class="table-card-header">
            <h3>Detail Pelanggan</h3>
            <div class="pill-tabs">
                <button class="pill-tab active" onclick="filterTable('semua', this)">Semua ({{ $stats['total'] ?? 0 }})</button>
                <button class="pill-tab" onclick="filterTable('sudah', this)">Sudah ({{ $stats['sudah'] ?? 0 }})</button>
                <button class="pill-tab" onclick="filterTable('belum', this)">Belum ({{ $stats['belum'] ?? 0 }})</button>
            </div>
        </div>
        <div class="tbl-wrap">
            <table class="detail-table" id="detail-tbl">
                <thead>
                    <tr>
                        <th>ID Pelanggan</th>
                        <th>Nama</th>
                        <th class="right">Daya</th>
                        <th class="right">Lembar</th>
                        <th class="right">RPTA</th>
                        <th class="right">Jumlah Ditagih</th>
                        <th class="right">Sisa</th>
                        <th>Tgl Tagih</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detail as $row)
                    <tr class="{{ $row->status === 'belum' ? 'row-belum' : '' }}" data-status="{{ $row->status }}">
                        <td class="mono">{{ $row->idpel }}</td>
                        <td style="font-weight:500">{{ $row->nama ?? '—' }}</td>
                        <td class="right mono">{{ number_format($row->daya ?? 0) }}</td>
                        <td class="right mono">{{ number_format($row->lembar ?? 0) }}</td>
                        <td class="right mono">{{ number_format($row->rptag ?? 0, 0, ',', '.') }}</td>
                        <td class="right mono" style="color:{{ $row->status === 'sudah' ? 'var(--c-success)' : 'var(--c-light)' }};font-weight:{{ $row->status === 'sudah' ? '600' : '400' }}">
                            {{ $row->status === 'sudah' ? number_format($row->jumlah_tagih ?? 0, 0, ',', '.') : '—' }}
                        </td>
                        <td class="right mono" style="color:var(--c-danger)">
                            @php $sisa = ($row->rptag ?? 0) - ($row->jumlah_tagih ?? 0); @endphp
                            {{ $sisa > 0 ? number_format($sisa, 0, ',', '.') : '—' }}
                        </td>
                        <td class="mono" style="color:var(--c-muted)">
                            {{ !empty($row->tgl_tagih) ? \Carbon\Carbon::parse($row->tgl_tagih)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <span class="pill {{ $row->status }}">
                                @if($row->status === 'sudah')
                                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24"><path d="M7 12l4 4 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                    Sudah
                                @else
                                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    Belum
                                @endif
                            </span>
                        </td>
                        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;color:var(--c-muted);font-size:.82rem">
                            {{ $row->keterangan ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function filterTable(status, btn) {
    // Update active tab
    document.querySelectorAll('.pill-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filter rows
    document.querySelectorAll('#detail-tbl tbody tr').forEach(row => {
        if (status === 'semua') {
            row.style.display = '';
        } else {
            row.style.display = row.dataset.status === status ? '' : 'none';
        }
    });
}
</script>
@endpush