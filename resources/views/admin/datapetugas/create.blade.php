@extends('layouts.app')
@section('title', 'Tambah Petugas')

@push('styles')
<style>
    :root {
        --c-primary:#005baa; --c-primary-dk:#004080;
        --c-border:#E2E8F0; --c-surface:#F8FAFC;
        --c-muted:#64748B; --radius:12px; --radius-sm:8px;
        --shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);
    }
    .page-wrap { max-width:600px; padding:24px 20px 48px; }
    .form-card { background:#fff;border:1px solid var(--c-border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden; }
    .form-card-hdr { padding:16px 22px;border-bottom:1px solid var(--c-border);display:flex;align-items:center;gap:12px; }
    .form-icon { width:38px;height:38px;border-radius:var(--radius-sm);background:#E6F1FB;color:#0C447C;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .form-icon svg { width:18px;height:18px; }
    .form-card-hdr h2 { font-size:1rem;font-weight:700;margin:0 0 1px; }
    .form-card-hdr p  { font-size:.8125rem;color:var(--c-muted);margin:0; }
    .form-body { padding:22px; }
    .fg { margin-bottom:14px; }
    .fg label { display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);margin-bottom:5px; }
    .fg input,.fg select { width:100%;height:38px;padding:0 12px;border:1px solid var(--c-border);border-radius:var(--radius-sm);font-size:.875rem;background:#F8FAFC;color:#1E293B;outline:none;transition:border-color .15s; }
    .fg input:focus,.fg select:focus { border-color:var(--c-primary);background:#fff; }
    .fg-row { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
    .form-footer { padding:14px 22px;border-top:1px solid var(--c-border);background:var(--c-surface);display:flex;align-items:center;justify-content:space-between; }
    .btn-back { display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--radius-sm);border:1px solid var(--c-border);background:#fff;font-size:.875rem;color:var(--c-muted);text-decoration:none;cursor:pointer;transition:background .15s; }
    .btn-back:hover { background:var(--c-surface);color:#1E293B; }
    .btn-back svg { width:14px;height:14px; }
    .btn-simpan { display:inline-flex;align-items:center;gap:7px;padding:8px 22px;border-radius:var(--radius-sm);border:none;background:var(--c-primary);color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;transition:background .15s; }
    .btn-simpan:hover { background:var(--c-primary-dk); }
    .btn-simpan svg { width:15px;height:15px; }
</style>
@endpush

@section('content')
<div class="container-fluid page-wrap">
    <div class="form-card">
        <div class="form-card-hdr">
            <div class="form-icon">
                <svg fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div>
                <h2>Tambah Petugas Baru</h2>
                <p>Isi data petugas di bawah ini</p>
            </div>
        </div>

        <div class="form-body">
            @if($errors->any())
            <div class="alert alert-danger small py-2 mb-3">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
            @endif

            <form method="POST" action="/datapetugas/store">
                @csrf

                <div class="fg-row">
                    <div class="fg">
                        <label>Kode Petugas</label>
                        <input type="text" name="kode_petugas"
                               value="{{ old('kode_petugas') }}"
                               placeholder="cth: PTG-001" required>
                    </div>
                    <div class="fg">
                        <label>Status</label>
                        <select name="status">
                            <option value="aktif"    {{ old('status')=='aktif'?'selected':'' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status')=='nonaktif'?'selected':'' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="fg">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama"
                           value="{{ old('nama') }}"
                           placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="fg">
                    <label>Email</label>
                    <input type="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="contoh@email.com" required>
                </div>

                <div class="fg">
                    <label>Wilayah</label>
                    <input type="text" name="wilayah"
                           value="{{ old('wilayah') }}"
                           placeholder="cth: Wilayah Utara" required>
                </div>

        </div>{{-- /form-body --}}

        <div class="form-footer">
            <a href="/datapetugas" class="btn-back">
                <svg fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali
            </a>
            <button type="submit" class="btn-simpan">
                <svg fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M17 21v-8H7v8M7 3v5h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Simpan
            </button>
        </div>

            </form>
    </div>
</div>
@endsection