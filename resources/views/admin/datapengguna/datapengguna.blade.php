@extends('layouts.app')
@section('title', 'Data Pengguna')

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

    .page-wrap   { max-width: 860px; padding: 24px 20px 48px; }
    .page-hdr    { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
    .page-hdr h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 2px; }
    .page-hdr p  { font-size: .875rem; color: var(--c-muted); margin: 0; }

    .btn-add {
        display: inline-flex; align-items: center; gap: 7px;
        background: var(--c-primary); color: #fff;
        border: none; border-radius: var(--radius-sm);
        padding: 8px 18px; font-size: .875rem; font-weight: 600;
        cursor: pointer; text-decoration: none; white-space: nowrap;
        transition: background .15s;
    }
    .btn-add:hover { background: var(--c-primary-dk); color: #fff; }
    .btn-add svg   { width: 15px; height: 15px; }

    .table-card {
        background: #fff; border: 1px solid var(--c-border);
        border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
    }

    .user-tbl { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .user-tbl thead tr {
        background: var(--c-surface);
        border-bottom: 2px solid var(--c-border);
    }
    .user-tbl thead th {
        padding: 10px 14px;
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em; color: var(--c-muted);
    }
    .user-tbl thead th:last-child { text-align: right; }

    .user-tbl tbody tr { border-bottom: 1px solid #F1F5F9; transition: background .1s; }
    .user-tbl tbody tr:hover { background: var(--c-surface); }
    .user-tbl tbody tr:last-child { border-bottom: none; }
    .user-tbl td { padding: 11px 14px; vertical-align: middle; font-size: .875rem; }
    .user-tbl td:last-child { text-align: right; }

    .user-cell { display: flex; align-items: center; gap: 11px; }
    .ava {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .8125rem; font-weight: 700;
    }
    .ava-admin      { background: #E6F1FB; color: #0C447C; }
    .ava-staff      { background: #EAF3DE; color: #27500A; }
    .ava-supervisor { background: #FAEEDA; color: #633806; }
    .ava-default    { background: #F1EFE8; color: #444441; }

    .u-name  { font-weight: 600; font-size: .875rem; color: var(--c-text); }
    .u-email { font-size: .775rem; color: var(--c-muted); }

    .pill {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 99px;
        font-size: .75rem; font-weight: 600;
    }
    .pill-admin      { background: #E6F1FB; color: #0C447C; }
    .pill-staff      { background: #EAF3DE; color: #27500A; }
    .pill-supervisor { background: #FAEEDA; color: #633806; }
    .pill-aktif      { background: #EAF3DE; color: #27500A; }
    .pill-nonaktif   { background: #FCEBEB; color: #791F1F; }

    .act-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: var(--radius-sm);
        border: 1px solid var(--c-border); background: #fff;
        cursor: pointer; text-decoration: none; color: var(--c-muted);
        transition: all .15s; margin-left: 4px;
    }
    .act-btn:hover { background: var(--c-surface); }
    .act-btn svg   { width: 14px; height: 14px; }
    .act-btn.del   { color: #DC2626; }
</style>
@endpush

@section('content')
<div class="px-4 py-4">

    <div class="page-hdr">
        <div>
            <h1>Data Pengguna</h1>
            <p>Kelola akun pengguna sistem</p>
        </div>
        @permission('users:create')
        <a href="/datapengguna/create" class="btn-add">
            <svg fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Tambah User
        </a>
        @endpermission
    </div>

    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small mb-3" role="alert">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="table-card">
        <div class="table-responsive">
        <table class="user-tbl">
            <colgroup>
                <col style="width:260px">
                <col style="width:120px">
                <col style="width:110px">
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th class="ps-4">Pengguna</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="ps-4">
                        <div class="user-cell">
                            @php
                                $roleClass = match($user->role) {
                                    'admin'      => 'ava-admin',
                                    'staff'      => 'ava-staff',
                                    'supervisor' => 'ava-supervisor',
                                    default      => 'ava-default',
                                };
                            @endphp
                            <div class="ava {{ $roleClass }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="u-name">{{ $user->name }}</div>
                                <div class="u-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <span class="pill pill-{{ $user->role }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>

                    <td>
                        <span class="pill pill-{{ $user->status }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>

                    <td class="pe-4">
                        @permission('users:edit')
                        <a href="/datapengguna/edit/{{ $user->id }}" class="act-btn" title="Edit">
                            <svg fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                        @endpermission
                        @permission('users:delete')
                        <form action="/datapengguna/delete/{{ $user->id }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="act-btn del" title="Hapus"
                                    onclick="return confirm('Yakin ingin hapus user {{ $user->name }}?')">
                                <svg fill="none" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </button>
                        </form>
                        @endpermission
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted" style="font-size:.875rem">
                        Belum ada data pengguna.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

</div>
@endsection