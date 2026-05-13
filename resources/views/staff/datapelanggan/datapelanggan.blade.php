@extends('layouts.app')

@section('title', 'Data Pelanggan')
@section('page_title', 'Data Pelanggan')

@section('content')

<style>
/* ── TAB BAR ── */
.utab-bar{display:flex;align-items:center;gap:6px;padding:14px 20px;background:#F0F4F9;border-bottom:1px solid #E2E9F1;flex-wrap:wrap}
.utab-btn{display:inline-flex;align-items:center;gap:7px;padding:7px 16px;font-size:13px;font-weight:500;color:#5A6A7E;background:transparent;border:1px solid transparent;border-radius:99px;cursor:pointer;font-family:inherit;transition:all .18s;white-space:nowrap}
.utab-btn:hover{background:#fff;color:#0D1B2E;border-color:#E2E9F1}
.utab-btn.active{background:#fff;color:#0055A5;border-color:#0055A5;box-shadow:0 0 0 3px rgba(0,85,165,.08);font-weight:600}
.utab-badge{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:99px;font-size:10px;font-weight:700;line-height:1}
.utab-badge-success{background:#E8F8F0;color:#1A7A4A}
.utab-badge-danger{background:#FEE8E8;color:#C0392B}
.utab-pane{display:none}.utab-pane.active{display:block}
.utab-body{padding:26px 26px 30px}
.utab-sec-title{font-size:11px;font-weight:600;color:#9AAABB;text-transform:uppercase;letter-spacing:.7px;margin:0 0 14px}
/* ── DROP ZONE ── */
.u-drop-zone{border:1.5px dashed #E2E9F1;border-radius:14px;padding:52px 32px;text-align:center;cursor:pointer;background:#F0F4F9;margin-bottom:14px;transition:border-color .18s,background .18s}
.u-drop-zone:hover,.u-drop-zone.dragover{border-color:#0055A5;background:#E8F1FC}
.u-drop-icon{width:44px;height:44px;margin:0 auto 14px;background:#fff;border:1px solid #E2E9F1;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#9AAABB;font-size:20px}
.u-drop-title{font-size:15px;font-weight:600;color:#0D1B2E;margin:0 0 5px}
.u-drop-sub{font-size:13px;color:#9AAABB;margin:0}
.u-drop-or{display:inline-block;margin:10px 0;font-size:12px;color:#9AAABB}
.u-file-chip{display:none;align-items:center;gap:7px;margin:14px auto 0;padding:5px 14px;background:#fff;border:1px solid #E2E9F1;border-radius:99px;font-size:12px;font-weight:500;color:#0D1B2E;width:fit-content}
.u-chip-dot{width:7px;height:7px;border-radius:50%;background:#1A7A4A;flex-shrink:0}
.u-format-info{display:flex;align-items:center;gap:8px;padding:10px 14px;background:#F0F4F9;border:1px solid #E2E9F1;border-radius:10px;font-size:12px;color:#5A6A7E}
/* ── STATS ── */
.u-stats-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.u-stat-chip{flex:1;min-width:100px;background:#F0F4F9;border:1px solid #E2E9F1;border-radius:10px;padding:10px 14px}
.u-stat-label{display:block;font-size:10px;font-weight:600;color:#9AAABB;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.u-stat-val{display:block;font-size:20px;font-weight:700;color:#0D1B2E;line-height:1}
/* ── TABLE ── */
.u-table-wrap{border:1px solid #E2E9F1;border-radius:14px;overflow:hidden;margin-bottom:12px}
.u-table{width:100%;border-collapse:collapse;font-size:13px}
.u-table thead tr{background:#F5F8FC}
.u-table th{padding:10px 14px;text-align:left;font-size:11px;font-weight:600;color:#9AAABB;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #E2E9F1;white-space:nowrap}
.u-table td{padding:10px 14px;color:#0D1B2E;border-bottom:1px solid #E2E9F1;font-size:13px;vertical-align:middle}
.u-table tr:last-child td{border-bottom:none}
.u-table tr:hover td{background:#F8FAFE}
.u-row-error td{background:#FFF5F5!important}
/* ── TOOLTIP ── */
.u-tooltip{position:relative;display:inline-flex}
.u-tooltip-box{display:none;position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#0D1B2E;color:#fff;padding:6px 10px;border-radius:6px;font-size:11px;white-space:nowrap;z-index:100;pointer-events:none}
.u-tooltip:hover .u-tooltip-box{display:block}
/* ── BADGES ── */
.u-badge{display:inline-flex;align-items:center;padding:2px 9px;border-radius:99px;font-size:11px;font-weight:600}
.u-badge-ok{background:#E8F8F0;color:#1A7A4A}
.u-badge-err{background:#FEE8E8;color:#C0392B}
/* ── EMPTY ── */
.u-empty{padding:52px 24px;text-align:center;border:1px dashed #E2E9F1;border-radius:14px;background:#F0F4F9}
.u-empty p{font-size:13px;color:#9AAABB;margin:10px 0 0}
/* ── ERRORS ── */
.u-error-scroll{display:flex;flex-direction:column;gap:8px;max-height:400px;overflow-y:auto;padding-right:4px}
.u-error-card{display:flex;align-items:flex-start;gap:12px;padding:11px 14px;background:#F0F4F9;border:1px solid #E2E9F1;border-radius:10px}
.u-error-card:hover{background:#F8FAFE}
.u-error-num{min-width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;background:#FEE8E8;color:#C0392B;border-radius:6px;font-size:11px;font-weight:700;flex-shrink:0;margin-top:1px}
/* ── BUTTONS ── */
.u-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#0055A5;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;transition:background .18s}
.u-btn:hover{background:#003F7F}
.u-btn:disabled{opacity:.45;cursor:not-allowed}
@media(max-width:640px){.utab-body{padding:18px 16px 22px}.utab-bar{padding:12px 14px;overflow-x:auto}.u-stats-row{flex-direction:column}.u-drop-zone{padding:36px 20px}}
</style>

{{-- LOADING --}}
<div class="pln-loading" id="pln-loading">
    <div class="pln-loading-card">
        <div class="pln-spinner"></div>
        <p>Memproses CSV...</p>
    </div>
</div>

{{-- HEADER --}}
<div class="page-header">
    <h1>Data Pelanggan</h1>
    <p>Impor file CSV, pratinjau data, lalu simpan ke database.</p>
</div>

{{-- ALERTS --}}
@if(session('save_success'))
    <div class="pln-alert pln-alert-success">
        <i class="bi bi-check-circle-fill"></i> {{ session('save_success') }}
    </div>
@endif
@if(session('save_error'))
    <div class="pln-alert pln-alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('save_error') }}
    </div>
@endif
@if(isset($fatal) && $fatal)
    <div class="pln-alert pln-alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ $fatal }}
    </div>
@endif

{{-- CARD --}}
<div class="card" style="overflow:hidden">

    <div class="utab-bar">
        <button id="dp-tab-import" class="utab-btn">
            <i class="bi bi-upload"></i> Import
        </button>
        <button id="dp-tab-preview" class="utab-btn">
            <i class="bi bi-table"></i> Preview
            <span class="utab-badge utab-badge-success">{{ $stats['total'] }}</span>
        </button>
        <button id="dp-tab-errors" class="utab-btn">
            <i class="bi bi-exclamation-circle"></i> Errors
            <span class="utab-badge utab-badge-danger">{{ $stats['error_rows'] }}</span>
        </button>
    </div>

    {{-- TAB IMPORT --}}
    <div id="dp-pane-import" class="utab-pane">
        <div class="utab-body">
            <p class="utab-sec-title">Upload File</p>
            <form id="dp-upload-form" method="POST" action="/datapelanggan/upload" enctype="multipart/form-data">
                @csrf
                <div id="dp-drop-zone" class="u-drop-zone">
                    <input type="file" name="csv_file" id="dp-file-input" accept=".csv,.txt" style="display:none">
                    <div class="u-drop-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
                    <p class="u-drop-title">Drag &amp; drop file CSV di sini</p>
                    <p class="u-drop-sub">atau klik untuk pilih file dari komputer</p>
                    <span class="u-drop-or">— atau —</span><br>
                    <button type="button" class="u-btn" style="margin-top:4px"
                            onclick="document.getElementById('dp-file-input').click()">
                        <i class="bi bi-folder2-open"></i> Pilih File
                    </button>
                    <div id="dp-file-chip" class="u-file-chip">
                        <span class="u-chip-dot"></span>
                        <span id="dp-file-name"></span>
                    </div>
                </div>
                @error('csv_file')
                    <div class="pln-alert pln-alert-danger" style="margin-top:10px">{{ $message }}</div>
                @enderror
            </form>
            <div class="u-format-info">
                <i class="bi bi-info-circle" style="flex-shrink:0"></i>
               Format: <strong>.csv, .txt</strong> · Maks. 10 MB · Kolom: IDPEL, Nama, Daya, Lembar, RPTAG, RPBK
            </div>
        </div>
    </div>

    {{-- TAB PREVIEW --}}
    <div id="dp-pane-preview" class="utab-pane">
        <div class="utab-body">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <p class="utab-sec-title" style="margin:0">Pratinjau Data</p>
                @if(!empty($preview))
                    <form method="POST" action="/datapelanggan/save" id="dp-save-form">
                        @csrf
                        <button class="u-btn" id="dp-save-btn">
                            <i class="bi bi-cloud-check"></i> Simpan ke DB
                        </button>
                    </form>
                @endif
            </div>

            @if(empty($preview))
                <div class="u-empty">
                    <i class="bi bi-table" style="font-size:28px;color:#9AAABB"></i>
                    <p>Belum ada data. Silakan impor file CSV terlebih dahulu.</p>
                </div>
            @else
                <div class="u-stats-row">
                    <div class="u-stat-chip">
                        <span class="u-stat-label">Total Baris</span>
                        <span class="u-stat-val">{{ $stats['total'] }}</span>
                    </div>
                    <div class="u-stat-chip">
                        <span class="u-stat-label">Valid</span>
                        <span class="u-stat-val" style="color:#1A7A4A">{{ $stats['total'] - $stats['error_rows'] }}</span>
                    </div>
                    <div class="u-stat-chip">
                        <span class="u-stat-label">Error</span>
                        <span class="u-stat-val" style="color:#C0392B">{{ $stats['error_rows'] }}</span>
                    </div>
                </div>

                <div class="u-table-wrap">
                    <table class="u-table">
                        <thead>
                            <tr>
                                <th style="width:44px">#</th>
                                <th>IDPEL</th>
                                <th>Nama</th>
                                <th style="width:80px">Daya</th>
                                <th style="width:70px">Lembar</th>
                                <th style="width:90px">RPTAG</th>
<th style="width:90px">RPBK</th>
                                <th style="width:90px">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($preview as $row)
                            <tr class="{{ $row['_status']=='error' ? 'u-row-error' : '' }}">
                                <td style="color:#9AAABB;font-size:12px">{{ $row['_row'] }}</td>
                                <td>{{ $row['idpel'] }}</td>
                                <td>{{ $row['nama'] }}</td>
                                <td>{{ $row['daya'] }}</td>
                                <td>{{ $row['lembar'] }}</td>
                               <td>{{ $row['rptag'] ?? 0 }}</td>
<td>{{ $row['rpbk'] ?? 0 }}</td>
                                <td>
                                    @if($row['_status']=='valid')
                                        <span class="u-badge u-badge-ok">Valid</span>
                                    @else
                                        <span class="u-tooltip">
                                            <span class="u-badge u-badge-err">Error</span>
                                            <span class="u-tooltip-box">{{ implode(' · ', $row['_errors']) }}</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <p style="font-size:12px;color:#9AAABB">
                    <i class="bi bi-info-circle"></i> Hanya baris <strong>valid</strong> yang akan disimpan.
                </p>
            @endif
        </div>
    </div>

    {{-- TAB ERRORS --}}
    <div id="dp-pane-errors" class="utab-pane">
        <div class="utab-body">
            <p class="utab-sec-title">Daftar Error</p>
            @php $errorList = $errorList ?? []; @endphp
            @if(empty($errorList))
                <div class="u-empty" style="border-color:#A8DFC0;background:#F0FDF4">
                    <i class="bi bi-check-circle-fill" style="font-size:28px;color:#1A7A4A"></i>
                    <p style="color:#1A7A4A;font-weight:500">Tidak ada error — semua baris valid.</p>
                </div>
            @else
                <div class="u-error-scroll">
                    @foreach($errorList as $i => $err)
                        <div class="u-error-card">
                            <div class="u-error-num">{{ $i + 1 }}</div>
                            <div>
                                <div style="font-size:11px;color:#9AAABB;margin-bottom:2px;font-weight:500">Keterangan Error</div>
                                <div style="font-size:13px;color:#0D1B2E">{{ $err }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function switchTab(name) {
        document.querySelectorAll('.utab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.utab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('dp-pane-' + name)?.classList.add('active');
        document.getElementById('dp-tab-'  + name)?.classList.add('active');
        sessionStorage.setItem('dp-tab', name);
    }
    document.querySelectorAll('.utab-btn').forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.id.replace('dp-tab-', '')));
    });
    switchTab(@json(session('active_tab', 'import')) || sessionStorage.getItem('dp-tab') || 'import');

    const zone = document.getElementById('dp-drop-zone');
    const input = document.getElementById('dp-file-input');
    const chip  = document.getElementById('dp-file-chip');
    const label = document.getElementById('dp-file-name');
    const form  = document.getElementById('dp-upload-form');
    const load  = document.getElementById('pln-loading');

    if (zone && input) {
        zone.addEventListener('click', () => input.click());
        ['dragover','dragenter'].forEach(e => zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.add('dragover'); }));
        ['dragleave','drop'].forEach(e => zone.addEventListener(e, () => zone.classList.remove('dragover')));
        zone.addEventListener('drop', e => { e.preventDefault(); if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]); });
        input.addEventListener('change', () => { if (input.files[0]) setFile(input.files[0]); });
        function setFile(file) {
            label.textContent = file.name;
            chip.style.display = 'inline-flex';
            const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
            setTimeout(() => { load.classList.add('show'); form.submit(); }, 300);
        }
    }

    const saveForm = document.getElementById('dp-save-form');
    const saveBtn  = document.getElementById('dp-save-btn');
    if (saveForm) {
        saveForm.addEventListener('submit', () => {
            if (saveBtn) { saveBtn.disabled = true; saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...'; }
            load.classList.add('show');
        });
    }
});
</script>

@endsection
