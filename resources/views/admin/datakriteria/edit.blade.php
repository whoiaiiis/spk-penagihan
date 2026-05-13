@extends('layouts.app')

@section('title', 'Edit Kriteria')
@section('page_title', 'Edit Kriteria')

@section('content')

<div class="page-header">
    <h1>Edit Kriteria</h1>
    <p>Perbarui data kriteria untuk perhitungan SAW</p>
</div>

<div class="px-4 py-4">

    {{-- FORM --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-square" style="color:var(--pln-blue);margin-right:6px"></i>
                Form Edit Kriteria
            </div>

            <div class="card-body">

                <form method="POST" action="/datakriteria/update/{{ $kriteria->id }}">
                    @csrf

                    {{-- Nama --}}
                    <div class="mb-4">
                        <label class="pln-label">Nama Kriteria</label>
                        <input type="text"
                               name="nama"
                               class="pln-input"
                               value="{{ $kriteria->nama }}"
                               required>
                    </div>

                    {{-- Tipe --}}
                    <div class="mb-4">
                        <label class="pln-label">Tipe Kriteria</label>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">

                            {{-- BENEFIT --}}
                            <label class="tipe-option">
                                <input type="radio" name="tipe" value="benefit"
                                    {{ $kriteria->tipe=='benefit' ? 'checked' : '' }}>

                                <div class="tipe-card">
                                    <div class="tipe-icon" style="background:#E8F8F0;color:#1A7A4A">
                                        ↑
                                    </div>
                                    <div class="tipe-label">Benefit</div>
                                    <div class="tipe-desc">Semakin besar semakin baik</div>
                                </div>
                            </label>

                            {{-- COST --}}
                            <label class="tipe-option">
                                <input type="radio" name="tipe" value="cost"
                                    {{ $kriteria->tipe=='cost' ? 'checked' : '' }}>

                                <div class="tipe-card">
                                    <div class="tipe-icon" style="background:#FEE8E8;color:#C0392B">
                                        ↓
                                    </div>
                                    <div class="tipe-label">Cost</div>
                                    <div class="tipe-desc">Semakin kecil semakin baik</div>
                                </div>
                            </label>

                        </div>
                    </div>

                    {{-- BOBOT --}}
                    <div class="mb-4">
                        <label class="pln-label">
                            Bobot (%)
                            <span id="bobot-live"
                                  style="float:right;font-weight:700;color:var(--pln-blue)">
                                {{ $kriteria->bobot }}%
                            </span>
                        </label>

                        <input type="range"
                               id="bobot-range"
                               min="0" max="100"
                               value="{{ $kriteria->bobot }}"
                               style="width:100%;margin-bottom:8px">

                        <input type="number"
                               id="bobot"
                               name="bobot"
                               class="pln-input"
                               value="{{ $kriteria->bobot }}"
                               required>
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="mb-4">
                        <label class="pln-label">Keterangan</label>
                        <textarea name="keterangan"
                                  class="pln-input"
                                  rows="3">{{ $kriteria->keterangan }}</textarea>
                    </div>

                    {{-- BUTTON --}}
                    <div style="display:flex;justify-content:space-between">

                        <a href="/datakriteria" class="btn-pln-ghost">
                            ← Kembali
                        </a>

                        <button class="btn-pln">
                            Update Kriteria
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

</div>

{{-- STYLE --}}
<style>
.tipe-option input { display:none }

.tipe-card {
    border:1.5px solid #ddd;
    border-radius:10px;
    padding:12px;
    text-align:center;
    cursor:pointer;
}

.tipe-option input:checked + .tipe-card {
    border-color:#0055A5;
    background:#eef6ff;
}
</style>

{{-- SCRIPT --}}
<script>
const range  = document.getElementById('bobot-range');
const number = document.getElementById('bobot');
const live   = document.getElementById('bobot-live');

function sync(val){
    range.value = val;
    number.value = val;
    live.innerText = val + '%';
}

range.addEventListener('input', () => sync(range.value));
number.addEventListener('input', () => sync(number.value));

sync(number.value);
</script>

@endsection