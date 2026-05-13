@extends('layouts.app')

@section('title', 'Data Kriteria')
@section('page_title', 'Data Kriteria')

@section('content')

<style>
.bobot-bar-wrap { display:flex; align-items:center; gap:8px; }
.bobot-bar {
    height:6px; border-radius:99px;
    background: linear-gradient(90deg, var(--pln-blue), #4A9FE8);
    transition: width .4s ease;
}
.bobot-val { font-size:12px; font-weight:600; color:var(--text-primary); white-space:nowrap; font-family:var(--font-mono); }
.total-bobot-bar {
    height:8px; border-radius:99px; background:#E2E9F1; overflow:hidden;
}
.total-bobot-fill {
    height:100%; border-radius:99px;
    transition: width .5s ease;
}
.total-bobot-fill.ok   { background: linear-gradient(90deg,#1A7A4A,#2ECC71); }
.total-bobot-fill.warn { background: linear-gradient(90deg,#D4891A,#F5A623); }
.total-bobot-fill.over { background: linear-gradient(90deg,#C0392B,#E74C3C); }
</style>

{{-- LOADING --}}
<div class="pln-loading" id="pln-loading">
    <div class="pln-loading-card">
        <div class="pln-spinner"></div>
        <p>Memproses...</p>
    </div>
</div>

{{-- HEADER --}}
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1>Data Kriteria</h1>
        <p>Kelola kriteria dan bobot untuk perhitungan SAW</p>
    </div>
    <a href="/datakriteria/create" class="btn-pln">
        <i class="bi bi-plus-lg"></i>
        Tambah Kriteria
    </a>
</div>

{{-- ALERT --}}
@if(session('success'))
    <div class="pln-alert pln-alert-success">
        <i class="bi bi-check-circle-fill"></i>
        {{ session('success') }}
    </div>
@endif

{{-- SUMMARY CARDS --}}
@php
    $totalBobot   = $kriteria->sum('bobot');
    $totalKriteria = $kriteria->count();
    $jmlBenefit   = $kriteria->where('tipe','benefit')->count();
    $jmlCost      = $kriteria->where('tipe','cost')->count();
    $bobotStatus  = $totalBobot == 100 ? 'ok' : ($totalBobot > 100 ? 'over' : 'warn');
    $bobotLabel   = $totalBobot == 100 ? 'Bobot seimbang ✓' : ($totalBobot > 100 ? 'Melebihi 100%' : 'Kurang dari 100%');
@endphp

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-accent"></div>
            <div class="stat-card-icon blue"><i class="bi bi-sliders"></i></div>
            <div class="stat-card-label">Total Kriteria</div>
            <div class="stat-card-value">{{ $totalKriteria }}</div>
            <div class="stat-card-sub">{{ $jmlBenefit }} benefit · {{ $jmlCost }} cost</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon green"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="stat-card-label">Kriteria Benefit</div>
            <div class="stat-card-value">{{ $jmlBenefit }}</div>
            <div class="stat-card-sub">Nilai lebih besar = lebih baik</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon red"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="stat-card-label">Kriteria Cost</div>
            <div class="stat-card-value">{{ $jmlCost }}</div>
            <div class="stat-card-sub">Nilai lebih kecil = lebih baik</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon {{ $bobotStatus == 'ok' ? 'green' : 'yellow' }}">
                <i class="bi bi-{{ $bobotStatus == 'ok' ? 'check-circle' : 'exclamation-triangle' }}"></i>
            </div>
            <div class="stat-card-label">Total Bobot</div>
            <div class="stat-card-value">{{ $totalBobot }}%</div>
            <div class="stat-card-sub">{{ $bobotLabel }}</div>
        </div>
    </div>
</div>

{{-- BOBOT PROGRESS --}}
<div class="card mb-4">
    <div class="card-body" style="padding:16px 20px!important">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">
                Distribusi Total Bobot
            </span>
            <span style="font-size:13px;font-weight:700;color:var(--text-primary)">
                {{ $totalBobot }} / 100%
            </span>
        </div>
        <div class="total-bobot-bar">
            <div class="total-bobot-fill {{ $bobotStatus }}"
                 style="width:{{ min($totalBobot,100) }}%"></div>
        </div>
        @if($totalBobot != 100)
            <p style="font-size:12px;color:{{ $totalBobot > 100 ? '#C0392B' : '#B7600A' }};margin-top:6px;margin-bottom:0">
                <i class="bi bi-info-circle"></i>
                Total bobot harus = 100% agar perhitungan SAW akurat.
                Selisih: {{ abs(100 - $totalBobot) }}%
            </p>
        @endif
    </div>
</div>

{{-- TABLE --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>Daftar Kriteria SAW</span>
        <span class="badge-neutral">{{ $totalKriteria }} kriteria</span>
    </div>
    <div class="pln-table-wrap" style="border-radius:0 0 var(--radius-lg) var(--radius-lg);border-top:none">
        <table class="pln-table">
            <thead>
                <tr>
                    <th style="width:44px">#</th>
                    <th>Nama Kriteria</th>
                    <th style="width:100px">Tipe</th>
                    <th style="width:200px">Bobot</th>
                    <th>Keterangan</th>
                    <th style="width:100px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($kriteria as $k)
                <tr>
                    <td style="color:var(--text-muted);font-size:12px">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $k->nama }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px">ID: {{ $k->id }}</div>
                    </td>
                    <td>
                        @if($k->tipe == 'benefit')
                            <span class="badge-success">
                                <i class="bi bi-arrow-up"></i> Benefit
                            </span>
                        @else
                            <span class="badge-danger">
                                <i class="bi bi-arrow-down"></i> Cost
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="bobot-bar-wrap">
                            <div style="flex:1">
                                <div class="bobot-bar" style="width:{{ $k->bobot }}%"></div>
                            </div>
                            <span class="bobot-val">{{ $k->bobot }}%</span>
                        </div>
                    </td>
                    <td style="color:var(--text-secondary);font-size:13px">
                        {{ $k->keterangan ?? '—' }}
                    </td>
                    <td style="text-align:right">
                        <div style="display:flex;gap:6px;justify-content:flex-end">
                            <a href="/datakriteria/edit/{{ $k->id }}"
                               class="btn-pln-ghost" style="padding:5px 10px;font-size:12px"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="/datakriteria/delete/{{ $k->id }}"
                                  method="POST" style="display:inline"
                                  onsubmit="return confirm('Yakin hapus kriteria \'{{ $k->nama }}\'?')">
                                @csrf
                                <button type="submit"
                                        style="padding:5px 10px;font-size:12px;background:#FEE8E8;color:#C0392B;border:1px solid #F5AAAA;border-radius:var(--radius-md);cursor:pointer;display:inline-flex;align-items:center"
                                        title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:52px 24px;text-align:center">
                        <div style="width:40px;height:40px;background:var(--bg-body);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:var(--text-muted)">
                            <i class="bi bi-sliders" style="font-size:18px"></i>
                        </div>
                        <p style="font-size:13px;color:var(--text-muted);margin:0">
                            Belum ada kriteria. <a href="/datakriteria/create" style="color:var(--pln-blue)">Tambah sekarang</a>
                        </p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
