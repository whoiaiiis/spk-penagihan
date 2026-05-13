@extends('layouts.app')

@section('title', 'Tambah Kriteria')
@section('page_title', 'Tambah Kriteria')

@section('content')

<div class="page-header">
    <h1>Tambah Kriteria</h1>
    <p>Tambahkan kriteria baru untuk perhitungan SAW</p>
</div>

<div class="row g-4">

    {{-- FORM CARD --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle" style="color:var(--pln-blue);margin-right:6px"></i>
                Form Tambah Kriteria
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="pln-alert pln-alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>
                            @foreach($errors->all() as $e)
                                <div>{{ $e }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="/datakriteria/store" id="form-kriteria">
                    @csrf

                    {{-- Nama --}}
                    <div class="mb-4">
                        <label class="pln-label" for="nama">Nama Kriteria <span style="color:#C0392B">*</span></label>
                        <input type="text"
                               id="nama"
                               name="nama"
                               class="pln-input"
                               placeholder="Contoh: Total Jumlah Bayar"
                               value="{{ old('nama') }}"
                               required>
                        <p style="font-size:11px;color:var(--text-muted);margin-top:5px">
                            Nama harus jelas dan deskriptif.
                        </p>
                    </div>

                    {{-- Tipe --}}
                    <div class="mb-4">
                        <label class="pln-label" for="tipe">Tipe Kriteria <span style="color:#C0392B">*</span></label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                            <label class="tipe-option" id="opt-benefit">
                                <input type="radio" name="tipe" value="benefit"
                                       {{ old('tipe')=='benefit' ? 'checked' : '' }} required>
                                <div class="tipe-card">
                                    <div class="tipe-icon" style="background:#E8F8F0;color:#1A7A4A">
                                        <i class="bi bi-arrow-up-circle-fill"></i>
                                    </div>
                                    <div class="tipe-label">Benefit</div>
                                    <div class="tipe-desc">Nilai besar = lebih baik</div>
                                </div>
                            </label>
                            <label class="tipe-option" id="opt-cost">
                                <input type="radio" name="tipe" value="cost"
                                       {{ old('tipe')=='cost' ? 'checked' : '' }}>
                                <div class="tipe-card">
                                    <div class="tipe-icon" style="background:#FEE8E8;color:#C0392B">
                                        <i class="bi bi-arrow-down-circle-fill"></i>
                                    </div>
                                    <div class="tipe-label">Cost</div>
                                    <div class="tipe-desc">Nilai kecil = lebih baik</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Bobot --}}
                    <div class="mb-4">
                        <label class="pln-label" for="bobot">
                            Bobot (%) <span style="color:#C0392B">*</span>
                            <span id="bobot-live"
                                  style="float:right;font-size:13px;font-weight:700;color:var(--pln-blue);font-family:var(--font-mono)">0%</span>
                        </label>
                        <input type="range"
                               id="bobot-range"
                               min="0" max="100" step="1"
                               value="{{ old('bobot', 0) }}"
                               style="width:100%;accent-color:var(--pln-blue);margin-bottom:8px">
                        <input type="number"
                               id="bobot"
                               name="bobot"
                               class="pln-input"
                               placeholder="0 – 100"
                               min="0" max="100"
                               value="{{ old('bobot') }}"
                               required>
                        <p style="font-size:11px;color:var(--text-muted);margin-top:5px">
                            Total bobot semua kriteria harus = 100%.
                        </p>
                    </div>

                    {{-- Keterangan --}}
                    <div class="mb-4">
                        <label class="pln-label" for="keterangan">Keterangan</label>
                        <textarea id="keterangan"
                                  name="keterangan"
                                  class="pln-input"
                                  rows="3"
                                  placeholder="Penjelasan singkat tentang kriteria ini...">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- Actions --}}
                    <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:4px">
                        <a href="/datakriteria" class="btn-pln-ghost">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn-pln"
                                onclick="document.getElementById('pln-loading').classList.add('show')">
                            <i class="bi bi-check-lg"></i> Simpan Kriteria
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- INFO PANEL --}}
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-info-circle" style="color:var(--pln-blue);margin-right:6px"></i>
                Panduan Pengisian
            </div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div style="display:flex;gap:10px">
                        <div style="width:28px;height:28px;background:var(--pln-blue-light);border-radius:7px;display:flex;align-items:center;justify-content:center;color:var(--pln-blue);font-size:12px;font-weight:700;flex-shrink:0">1</div>
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">Nama Kriteria</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Gunakan nama yang jelas, misal: "Total Jumlah Bayar", "Status Lunas"</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px">
                        <div style="width:28px;height:28px;background:var(--pln-blue-light);border-radius:7px;display:flex;align-items:center;justify-content:center;color:var(--pln-blue);font-size:12px;font-weight:700;flex-shrink:0">2</div>
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">Tipe Benefit / Cost</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px"><strong>Benefit:</strong> nilai besar lebih diutamakan (R = nilai/max)<br><strong>Cost:</strong> nilai kecil lebih diutamakan (R = min/nilai)</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px">
                        <div style="width:28px;height:28px;background:var(--pln-blue-light);border-radius:7px;display:flex;align-items:center;justify-content:center;color:var(--pln-blue);font-size:12px;font-weight:700;flex-shrink:0">3</div>
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">Bobot (%)</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Angka 0–100. <strong>Total semua bobot harus = 100%</strong> agar SAW akurat.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-calculator" style="color:var(--pln-blue);margin-right:6px"></i>
                Formula SAW
            </div>
            <div class="card-body">
                <div style="background:var(--bg-body);border-radius:var(--radius-md);padding:12px 14px;font-family:var(--font-mono);font-size:12px;color:var(--text-primary);line-height:1.8">
                    <div style="color:var(--text-muted);font-family:var(--font-sans);font-size:11px;margin-bottom:6px;font-weight:600">NORMALISASI</div>
                    Benefit → R = nilai / MAX<br>
                    Cost &nbsp;&nbsp;&nbsp;→ R = MIN / nilai<br>
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border);color:var(--text-muted);font-family:var(--font-sans);font-size:11px;font-weight:600;margin-bottom:4px">NILAI AKHIR</div>
                    Vi = Σ (bobot × R)
                </div>
            </div>
        </div>
    </div>

</div>

{{-- LOADING --}}
<div class="pln-loading" id="pln-loading">
    <div class="pln-loading-card">
        <div class="pln-spinner"></div>
        <p>Menyimpan kriteria...</p>
    </div>
</div>

<style>
.tipe-option input[type="radio"] { display:none }
.tipe-card {
    border:1.5px solid var(--border);
    border-radius:var(--radius-md);
    padding:14px 12px;
    cursor:pointer;
    transition:border-color .15s, box-shadow .15s, background .15s;
    text-align:center;
}
.tipe-card:hover { border-color:var(--pln-blue); background:var(--pln-blue-light); }
.tipe-option input:checked + .tipe-card {
    border-color:var(--pln-blue);
    background:var(--pln-blue-light);
    box-shadow:0 0 0 3px rgba(0,85,165,.1);
}
.tipe-icon { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;margin:0 auto 8px }
.tipe-label { font-size:13px;font-weight:700;color:var(--text-primary) }
.tipe-desc  { font-size:11px;color:var(--text-muted);margin-top:2px }
</style>

<script>
// Sync range slider ↔ number input
const range  = document.getElementById('bobot-range');
const number = document.getElementById('bobot');
const live   = document.getElementById('bobot-live');

function syncBobot(val) {
    range.value  = val;
    number.value = val;
    live.textContent = val + '%';
}

range.addEventListener('input',  () => syncBobot(range.value));
number.addEventListener('input', () => syncBobot(number.value));
syncBobot(number.value || 0);
</script>

@endsection
