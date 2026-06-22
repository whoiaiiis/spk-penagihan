@extends('layouts.app')

@section('title', 'Input Hasil Penagihan')
@section('page_title', 'Input Hasil Penagihan')

@section('content')

<style>
/* ── TAB BAR ── */
.utab-bar{display:flex;align-items:center;gap:6px;padding:14px 20px;background:#F0F4F9;border-bottom:1px solid #E2E9F1;flex-wrap:wrap}
.utab-btn{display:inline-flex;align-items:center;gap:7px;padding:7px 16px;font-size: 15px;font-weight:500;color:#5A6A7E;background:transparent;border:1px solid transparent;border-radius:99px;cursor:pointer;font-family:inherit;transition:all .18s;white-space:nowrap}
.utab-btn:hover{background:#fff;color:#0D1B2E;border-color:#E2E9F1}
.utab-btn.active{background:#fff;color:#0055A5;border-color:#0055A5;box-shadow:0 0 0 3px rgba(0,85,165,.08);font-weight:600}
.utab-badge{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:99px;font-size: 12px;font-weight:700;line-height:1}
.utab-badge-success{background:#E8F8F0;color:#1A7A4A}
.utab-badge-danger{background:#FEE8E8;color:#C0392B}
.utab-pane{display:none}.utab-pane.active{display:block}
.utab-body{padding:26px 26px 30px}
.utab-sec-title{font-size: 13px;font-weight:600;color:#9AAABB;text-transform:uppercase;letter-spacing:.7px;margin:0 0 14px}
/* ── DROP ZONE ── */
.u-drop-zone{border:1.5px dashed #E2E9F1;border-radius:14px;padding:52px 32px;text-align:center;cursor:pointer;background:#F0F4F9;margin-bottom:14px;transition:border-color .18s,background .18s}
.u-drop-zone:hover,.u-drop-zone.dragover{border-color:#0055A5;background:#E8F1FC}
.u-drop-icon{width:44px;height:44px;margin:0 auto 14px;background:#fff;border:1px solid #E2E9F1;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#9AAABB;font-size: 22px}
.u-drop-title{font-size: 17px;font-weight:600;color:#0D1B2E;margin:0 0 5px}
.u-drop-sub{font-size: 15px;color:#9AAABB;margin:0}
.u-drop-or{display:inline-block;margin:10px 0;font-size: 14px;color:#9AAABB}
.u-file-chip{display:none;align-items:center;gap:7px;margin:14px auto 0;padding:5px 14px;background:#fff;border:1px solid #E2E9F1;border-radius:99px;font-size: 14px;font-weight:500;color:#0D1B2E;width:fit-content}
.u-chip-dot{width:7px;height:7px;border-radius:50%;background:#1A7A4A;flex-shrink:0}
.u-format-info{display:flex;align-items:center;gap:8px;padding:10px 14px;background:#F0F4F9;border:1px solid #E2E9F1;border-radius:10px;font-size: 14px;color:#5A6A7E}
/* ── STATS ── */
.u-stats-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.u-stat-chip{flex:1;min-width:100px;background:#F0F4F9;border:1px solid #E2E9F1;border-radius:10px;padding:10px 14px}
.u-stat-label{display:block;font-size: 12px;font-weight:600;color:#9AAABB;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.u-stat-val{display:block;font-size: 22px;font-weight:700;color:#0D1B2E;line-height:1}
/* ── TABLE ── */
.u-table-wrap{border:1px solid #E2E9F1;border-radius:14px;overflow-x:auto;margin-bottom:12px;-webkit-overflow-scrolling:touch}
.u-table{width:100%;border-collapse:collapse;font-size: 15px}
.u-table thead tr{background:#F5F8FC}
.u-table th{padding:10px 14px;text-align:left;font-size: 13px;font-weight:600;color:#9AAABB;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #E2E9F1;white-space:nowrap}
.u-table td{padding:10px 14px;color:#0D1B2E;border-bottom:1px solid #E2E9F1;font-size: 15px;vertical-align:middle}
.u-table tr:last-child td{border-bottom:none}
.u-table tr:hover td{background:#F8FAFE}
.u-row-error td{background:#FFF5F5!important}
/* ── BADGES ── */
.u-badge{display:inline-flex;align-items:center;padding:2px 9px;border-radius:99px;font-size: 13px;font-weight:600}
.u-badge-ok{background:#E8F8F0;color:#1A7A4A}
.u-badge-err{background:#FEE8E8;color:#C0392B}
/* ── EMPTY ── */
.u-empty{padding:52px 24px;text-align:center;border:1px dashed #E2E9F1;border-radius:14px;background:#F0F4F9}
.u-empty p{font-size: 15px;color:#9AAABB;margin:10px 0 0}
/* ── ERRORS ── */
.u-error-scroll{display:flex;flex-direction:column;gap:8px;max-height:400px;overflow-y:auto;padding-right:4px}
.u-error-card{display:flex;align-items:flex-start;gap:12px;padding:11px 14px;background:#F0F4F9;border:1px solid #E2E9F1;border-radius:10px}
.u-error-card:hover{background:#F8FAFE}
.u-error-num{min-width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;background:#FEE8E8;color:#C0392B;border-radius:6px;font-size: 13px;font-weight:700;flex-shrink:0;margin-top:1px}
/* ── BUTTONS ── */
.u-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#0055A5;color:#fff;border:none;border-radius:10px;font-size: 15px;font-weight:600;font-family:inherit;cursor:pointer;transition:background .18s}
.u-btn:hover{background:#003F7F}
.u-btn:disabled{opacity:.45;cursor:not-allowed}
@media(max-width:640px){.utab-body{padding:18px 16px 22px}.utab-bar{padding:12px 14px;overflow-x:auto}.u-stats-row{flex-direction:column}.u-drop-zone{padding:36px 20px}}
</style>

{{-- Tampilan loading indikator saat upload/simpan file --}}
<div class="pln-loading" id="pln-loading">
    <div class="pln-loading-card">
        <div class="pln-spinner"></div>
        <p>Memproses file...</p>
    </div>
</div>

{{-- Header halaman input hasil penagihan --}}
<div class="page-header">
    <h1>Input Hasil Penagihan</h1>
    <p>Upload file hasil pembayaran PLN, pratinjau data, dan simpan ke database.</p>
</div>

{{-- Notifikasi alert sukses atau error dari session --}}
@if(session('success'))
    <div class="pln-alert pln-alert-success">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="pln-alert pln-alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
    </div>
@endif
@if(!empty($fatal))
    <div class="pln-alert pln-alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ $fatal }}
    </div>
@endif

{{-- CARD --}}
<div class="card" style="overflow:hidden">

    {{-- Navigasi tab: Import File, Preview, dan Error Panel --}}
    <div class="utab-bar">
        <button id="ip-tab-import" class="utab-btn">
            <i class="bi bi-upload"></i> Import File
        </button>
        <button id="ip-tab-preview" class="utab-btn">
            <i class="bi bi-table"></i> Preview
            <span class="utab-badge utab-badge-success">{{ $stats['total'] ?? 0 }}</span>
        </button>
        <button id="ip-tab-errors" class="utab-btn">
            <i class="bi bi-exclamation-circle"></i> Error Panel
            <span class="utab-badge utab-badge-danger">{{ $stats['error_rows'] ?? 0 }}</span>
        </button>
    </div>

    {{-- Tab untuk upload file CSV hasil penagihan --}}
    <div id="ip-pane-import" class="utab-pane">
        <div class="utab-body">
            <p class="utab-sec-title">Upload File</p>
            {{-- Form pengunggahan file CSV ke server --}}
            <form id="ip-upload-form" method="POST" action="{{ route('penagihan.upload') }}" enctype="multipart/form-data">
                @csrf
                <div id="ip-drop-zone" class="u-drop-zone">
                    <input type="file" name="csv_file" id="ip-file-input" accept=".csv,.txt" style="display:none">
                    <div class="u-drop-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
                    <p class="u-drop-title">Drag &amp; drop file di sini</p>
                    <p class="u-drop-sub">Format: CSV · Maks. 10 MB</p>
                    <span class="u-drop-or">— atau —</span><br>
                    <button type="button" class="u-btn" style="margin-top:4px"
                            onclick="document.getElementById('ip-file-input').click()">
                        <i class="bi bi-folder2-open"></i> Pilih File
                    </button>
                    <div id="ip-file-chip" class="u-file-chip">
                        <span class="u-chip-dot"></span>
                        <span id="ip-file-name"></span>
                    </div>
                </div>
                @error('csv_file')
                    <div class="pln-alert pln-alert-danger" style="margin-top:10px">{{ $message }}</div>
                @enderror
            </form>

            {{-- Info format kolom yang dibutuhkan --}}
            <div class="u-format-info">
                <i class="bi bi-info-circle" style="flex-shrink:0"></i>
                <span>
                    Kolom yang dibaca: <strong>Kolom B = TglBuku</strong> (format YYYYMMDD, contoh: <code>20260302</code>)
                    &nbsp;·&nbsp; <strong>Kolom D = IDPEL</strong> (contoh: <code>141000020529</code>).
                    Kolom lain diabaikan.
                </span>
            </div>
        </div>
    </div>

    {{-- Tab pratinjau data penagihan sebelum disimpan --}}
    <div id="ip-pane-preview" class="utab-pane">
        <div class="utab-body">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px">
                <p class="utab-sec-title" style="margin:0">Pratinjau Data</p>
                @if(!empty($preview))
                    <div style="display:flex;gap:8px;align-items:center">
                        <form method="POST" action="{{ route('penagihan.reset') }}" style="display:inline">
                            @csrf
                            <button type="submit" class="u-btn" style="background:#E2E9F1;color:#5A6A7E">
                                <i class="bi bi-x-circle"></i> Batal
                            </button>
                        </form>
                        {{-- Form untuk menyimpan data valid ke database --}}
                        <form method="POST" action="{{ route('penagihan.save') }}" id="ip-save-form" style="display:inline">
                            @csrf
                            <button class="u-btn" id="ip-save-btn"
                                    {{ ($stats['valid'] ?? 0) == 0 ? 'disabled' : '' }}>
                                <i class="bi bi-cloud-check"></i>
                                Simpan {{ $stats['valid'] ?? 0 }} Data Valid
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            @php $preview = $preview ?? []; @endphp

            @if(empty($preview))
                <div class="u-empty">
                    <i class="bi bi-table" style="font-size: 30px;color:#9AAABB"></i>
                    <p>Belum ada data. Silakan upload file terlebih dahulu.</p>
                </div>
            @else
                {{-- Menampilkan ringkasan jumlah baris data, valid, dan error --}}
                <div class="u-stats-row">
                    <div class="u-stat-chip">
                        <span class="u-stat-label">Total Baris</span>
                        <span class="u-stat-val">{{ $stats['total'] ?? 0 }}</span>
                    </div>
                    <div class="u-stat-chip">
                        <span class="u-stat-label">Valid</span>
                        <span class="u-stat-val" style="color:#1A7A4A">{{ $stats['valid'] ?? 0 }}</span>
                    </div>
                    <div class="u-stat-chip">
                        <span class="u-stat-label">Error</span>
                        <span class="u-stat-val" style="color:#C0392B">{{ $stats['error_rows'] ?? 0 }}</span>
                    </div>
                    <div class="u-stat-chip">
                        <span class="u-stat-label">Siap Disimpan</span>
                        <span class="u-stat-val" style="color:#0055A5">{{ $stats['valid'] ?? 0 }}</span>
                    </div>
                </div>

                {{-- Tabel pratinjau data dari CSV --}}
                <div class="u-table-wrap">
                    <table class="u-table">
                      <thead>
<tr>
    <th>#</th>
    <th>IDPEL</th>
    <th>TGL BUKU</th>
    <th>STATUS</th>
    <th>KETERANGAN</th>
</tr>
</thead>
                        <tbody>
                        {{-- Looping baris data hasil pembacaan CSV --}}
                        @foreach($preview as $row)
                            <tr class="{{ $row['_status'] === 'error' ? 'u-row-error' : '' }}">
                                <td style="color:#9AAABB;font-size: 14px">{{ $row['_row'] }}</td>

                                {{-- IDPEL --}}
                                <td style="font-family:monospace;font-weight:500">
                                    {{ $row['idpel'] !== '' ? $row['idpel'] : '<em style="color:#9AAABB">kosong</em>' }}
                                </td>

                                {{-- TglBuku mentah dari CSV --}}
                                <td style="font-family:monospace">{{ $row['tgl_buku'] ?: '-' }}</td>

                                {{-- Status valid / error --}}
                                <td>
                                    @if($row['_status'] === 'valid')
                                        <span class="u-badge u-badge-ok">
                                            <i class="bi bi-check-circle" style="margin-right:3px"></i> Valid
                                        </span>
                                    @else
                                        <span class="u-badge u-badge-err">
                                            <i class="bi bi-x-circle" style="margin-right:3px"></i> Error
                                        </span>
                                    @endif
                                </td>

                                {{-- Pesan error jika ada --}}
                                <td style="font-size: 14px;color:#C0392B">
                                    @if(!empty($row['_errors']))
                                        {{ implode('; ', $row['_errors']) }}
                                    @else
                                        <span style="color:#1A7A4A">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <p style="font-size: 14px;color:#9AAABB">
                    <i class="bi bi-info-circle"></i>
                    Hanya baris <strong>valid</strong> yang akan disimpan ke database.
                    Baris error (latar merah) akan dilewati.
                </p>
            @endif
        </div>
    </div>

    {{-- Tab daftar error jika ada baris tidak valid pada file CSV --}}
    <div id="ip-pane-errors" class="utab-pane">
        <div class="utab-body">
            <p class="utab-sec-title">Daftar Error</p>
            @php $errorList = $errorList ?? []; @endphp
            @if(empty($errorList))
                <div class="u-empty" style="border-color:#A8DFC0;background:#F0FDF4">
                    <i class="bi bi-check-circle-fill" style="font-size: 30px;color:#1A7A4A"></i>
                    <p style="color:#1A7A4A;font-weight:500">Tidak ada error — semua baris valid.</p>
                </div>
            @else
                <div class="u-error-scroll">
                    @foreach($errorList as $i => $err)
                        <div class="u-error-card">
                            <div class="u-error-num">{{ $i + 1 }}</div>
                            <div>
                                <div style="font-size: 13px;color:#9AAABB;margin-bottom:2px;font-weight:500">Keterangan Error</div>
                                <div style="font-size: 15px;color:#0D1B2E">{{ $err }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

<script>
// Script untuk manajemen tab dan drag-drop file upload
document.addEventListener('DOMContentLoaded', function () {

    // ── Tab switching ────────────────────────────────────────────────────────
    function switchTab(name) {
        document.querySelectorAll('.utab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.utab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('ip-pane-' + name)?.classList.add('active');
        document.getElementById('ip-tab-'  + name)?.classList.add('active');
        sessionStorage.setItem('ip-tab', name);
    }
    document.querySelectorAll('.utab-btn').forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.id.replace('ip-tab-', '')));
    });
    // Prioritas: server → sessionStorage → default 'import'
    const serverTab = @json(session('active_tab', null));
    switchTab(serverTab || sessionStorage.getItem('ip-tab') || 'import');

    // ── Drop zone & file picker ──────────────────────────────────────────────
    const zone  = document.getElementById('ip-drop-zone');
    const input = document.getElementById('ip-file-input');
    const chip  = document.getElementById('ip-file-chip');
    const label = document.getElementById('ip-file-name');
    const form  = document.getElementById('ip-upload-form');
    const load  = document.getElementById('pln-loading');

    if (zone && input) {
        zone.addEventListener('click', e => {
            // Jangan trigger saat klik tombol "Pilih File" (sudah punya onclick sendiri)
            if (e.target.closest('button')) return;
            input.click();
        });

        ['dragover','dragenter'].forEach(e =>
            zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.add('dragover'); })
        );
        ['dragleave','drop'].forEach(e =>
            zone.addEventListener(e, () => zone.classList.remove('dragover'))
        );
        zone.addEventListener('drop', e => {
            e.preventDefault();
            if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]);
        });
        input.addEventListener('change', () => {
            if (input.files[0]) setFile(input.files[0]);
        });

        function setFile(file) {
            label.textContent = file.name;
            chip.style.display = 'inline-flex';
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            // Delay kecil agar chip sempat muncul sebelum submit
            setTimeout(() => { load.classList.add('show'); form.submit(); }, 350);
        }
    }

    // ── Save form ────────────────────────────────────────────────────────────
    const saveForm = document.getElementById('ip-save-form');
    const saveBtn  = document.getElementById('ip-save-btn');
    if (saveForm && saveBtn) {
        saveForm.addEventListener('submit', () => {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
            load?.classList.add('show');
        });
    }
});
</script>

@endsection
