@extends('layouts.app')
@section('title', 'Manajemen Role & Hak Akses')
@section('page_title', 'Roles & Hak Akses')

@push('styles')
<style>
    /* ─── DESIGN TOKENS ─────────────────────────────────────────── */
    :root {
        --c-white:      #ffffff;
        --c-bg:         #f5f6f8;
        --c-surface:    #fafbfc;
        --c-border:     #e8eaed;
        --c-border-md:  #d1d5db;
        --c-text:       #1a1d23;
        --c-text-soft:  #4b5263;
        --c-muted:      #8b92a5;
        --c-navy:       #0a2d6e;   /* PLN Navy accent */
        --c-navy-lt:    #e8edf7;   /* Navy tint */
        --c-navy-md:    #1e4db7;   /* Slightly lighter for hover */
        --c-check:      #1a3a6c;   /* All checkboxes same color */
        --radius:       12px;
        --radius-sm:    8px;
        --sidebar-w:    240px;
    }

    /* ─── BASE ────────────────────────────────────────────────────── */
    body { background: var(--c-bg); }
    .page-wrap { padding-bottom: 72px; }

    /* ─── CARD ────────────────────────────────────────────────────── */
    .ent-card {
        background: var(--c-white);
        border: 1px solid var(--c-border);
        border-radius: var(--radius);
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }

    /* ─── HEADER CARD ─────────────────────────────────────────────── */
    .header-icon {
        width: 40px; height: 40px;
        background: var(--c-navy-lt);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        color: var(--c-navy);
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .header-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--c-text);
        letter-spacing: -0.01em;
        margin: 0;
    }
    .header-subtitle {
        font-size: 0.78rem;
        color: var(--c-muted);
        margin: 2px 0 0;
    }

    /* ─── SEARCH & TOOLBAR ────────────────────────────────────────── */
    .toolbar-input {
        height: 32px;
        font-size: 0.78rem;
        border: 1px solid var(--c-border);
        border-radius: 6px;
        padding: 0 10px;
        background: var(--c-surface);
        color: var(--c-text);
        width: 200px;
        transition: border-color .15s, box-shadow .15s;
    }
    .toolbar-input:focus {
        outline: none;
        border-color: var(--c-navy);
        box-shadow: 0 0 0 2px rgba(10,45,110,.1);
    }
    .toolbar-btn {
        height: 32px;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0 12px;
        border: 1px solid var(--c-border);
        background: var(--c-white);
        color: var(--c-text-soft);
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 5px;
        transition: background .12s, color .12s, border-color .12s;
        white-space: nowrap;
    }
    .toolbar-btn:hover {
        background: var(--c-surface);
        border-color: var(--c-border-md);
        color: var(--c-text);
    }

    /* ─── TABLE ───────────────────────────────────────────────────── */
    .perm-table {
        font-size: 0.8rem;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .perm-table thead th {
        background: var(--c-surface);
        font-weight: 600;
        font-size: 0.68rem;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        color: var(--c-text-soft);
        border-bottom: 1px solid var(--c-border);
        padding: 8px 10px;
        white-space: nowrap;
    }
    .perm-table thead {
        position: sticky;
        top: 0;
        z-index: 10;
        background: var(--c-surface);
    }
    .perm-table td {
        padding: 7px 10px;
        border-bottom: 1px solid var(--c-border);
        color: var(--c-text);
        vertical-align: middle;
    }
    .perm-table tbody tr:last-child td { border-bottom: none; }
    .perm-table tbody tr:hover td { background: #f8f9fb !important; }

    /* Sticky left column */
    .col-menu {
        position: sticky;
        left: 0;
        background: var(--c-white);
        z-index: 2;
        border-right: 1px solid var(--c-border);
        min-width: 180px;
        max-width: 220px;
    }
    .perm-table thead th.col-menu {
        z-index: 11;
        background: var(--c-surface);
    }
    .perm-table tbody tr:hover td.col-menu { background: #f8f9fb !important; }

    /* Scrollable wrapper */
    .table-scroll-wrapper {
        max-height: calc(100vh - 280px);
        overflow: auto;
        border-radius: var(--radius);
    }

    /* Role column divider */
    .role-divider { border-left: 1px solid var(--c-border) !important; }

    /* ─── ROLE BADGE ──────────────────────────────────────────────── */
    .role-badge {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        padding: 2px 8px;
        border-radius: 4px;
        border: 1px solid var(--c-border-md);
        background: var(--c-white);
        color: var(--c-text-soft);
    }

    /* ─── CATEGORY HEADER ROW ────────────────────────────────────── */
    .category-header-row td {
        background: var(--c-surface) !important;
        color: var(--c-muted);
        font-weight: 600;
        font-size: 0.65rem;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        padding: 6px 12px;
        border-top: 1px solid var(--c-border);
        border-bottom: 1px solid var(--c-border);
    }
    .category-toggle {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: inherit;
        text-decoration: none;
        user-select: none;
    }
    .category-toggle:hover { color: var(--c-navy); }
    .category-toggle .bi { font-size: 0.6rem; transition: transform .15s; }
    .collapsed-row { display: none !important; }

    /* ─── CHECKBOXES (all monochrome) ────────────────────────────── */
    .perm-cb {
        width: 15px;
        height: 15px;
        margin: 0 auto;
        display: block;
        cursor: pointer;
        accent-color: var(--c-check);
        border-radius: 3px;
        transition: transform .12s;
    }
    .perm-cb:hover { transform: scale(1.15); }
    .perm-cb:focus { outline: 2px solid rgba(10,45,110,.2); }

    /* Action column header checkbox */
    .cb-action-all {
        width: 12px; height: 12px;
        margin: 3px auto 0;
        display: block;
        cursor: pointer;
        accent-color: var(--c-check);
    }

    /* ─── ACTION LABELS ──────────────────────────────────────────── */
    .action-label {
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--c-muted);
        display: block;
        text-align: center;
        margin-bottom: 2px;
    }

    /* ─── SELECT-ALL ROLE ────────────────────────────────────────── */
    .sa-role-wrap {
        display: flex; align-items: center;
        justify-content: center; gap: 4px;
        margin-top: 4px;
    }
    .sa-role-cb {
        width: 12px; height: 12px;
        cursor: pointer;
        accent-color: var(--c-check);
    }
    .sa-role-lbl {
        font-size: 0.6rem;
        color: var(--c-muted);
        user-select: none;
        cursor: pointer;
    }

    /* ─── STICKY FOOTER ─────────────────────────────────────────── */
    .save-footer {
        position: fixed;
        bottom: 0;
        left: var(--sidebar-w);
        right: 0;
        height: 56px;
        background: var(--c-white);
        border-top: 1px solid var(--c-border);
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 990;
        transition: left .18s cubic-bezier(.4,0,.2,1);
    }
    @media (max-width: 768px) { .save-footer { left: 0; } }

    .footer-info {
        font-size: 0.75rem;
        color: var(--c-muted);
    }
    .footer-info strong { color: var(--c-text); }

    .btn-reset {
        height: 32px;
        font-size: 0.78rem;
        font-weight: 500;
        padding: 0 16px;
        border: 1px solid var(--c-border-md);
        background: var(--c-white);
        color: var(--c-text-soft);
        border-radius: 6px;
        cursor: pointer;
        transition: background .12s, color .12s;
    }
    .btn-reset:hover {
        background: var(--c-surface);
        color: var(--c-text);
    }
    .btn-save {
        height: 32px;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0 18px;
        border: none;
        background: var(--c-navy);
        color: #fff;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: background .12s;
    }
    .btn-save:hover { background: var(--c-navy-md); }
    .btn-save:disabled { opacity: .6; cursor: default; }

    /* ─── MOBILE ──────────────────────────────────────────────────── */
    .m-role-card {
        background: var(--c-white);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-sm);
        overflow: hidden;
        margin-bottom: 10px;
    }
    .m-role-header {
        padding: 10px 14px;
        background: var(--c-surface);
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--c-text);
        border-bottom: 1px solid var(--c-border);
    }
    .m-cat-header {
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: var(--c-muted);
        background: var(--c-surface);
        padding: 5px 12px;
        border-bottom: 1px solid var(--c-border);
    }
    .m-menu-item {
        padding: 9px 12px;
        border-bottom: 1px solid var(--c-border);
        font-size: 0.8rem;
    }
    .m-menu-item:last-child { border-bottom: none; }
    .m-menu-name {
        font-weight: 500;
        color: var(--c-text);
        margin-bottom: 6px;
        font-size: 0.78rem;
    }

    /* Mobile action pill buttons */
    .m-action-wrap { display: flex; flex-wrap: wrap; gap: 6px; }
    .m-cb-input { display: none; }
    .m-cb-label {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 4px;
        border: 1px solid var(--c-border-md);
        background: var(--c-white);
        color: var(--c-muted);
        cursor: pointer;
        user-select: none;
        transition: all .12s;
    }
    .m-cb-label:hover { background: var(--c-surface); }
    .m-cb-input:checked + .m-cb-label {
        background: var(--c-check) !important;
        color: #fff !important;
        border-color: var(--c-check) !important;
    }

    /* ─── TOAST ───────────────────────────────────────────────────── */
    .ent-toast {
        min-width: 280px;
        font-size: 0.8rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--c-border);
        background: var(--c-white);
        box-shadow: 0 4px 16px rgba(0,0,0,.1);
        color: var(--c-text);
    }
    .ent-toast.toast-success { border-left: 3px solid #1a6b3a; }
    .ent-toast.toast-error   { border-left: 3px solid #9b2020; }
    .ent-toast .toast-icon-ok  { color: #1a6b3a; }
    .ent-toast .toast-icon-err { color: #9b2020; }
</style>
@endpush

@section('content')
<div class="px-4 py-3 page-wrap">

    {{-- ── HEADER CARD ── --}}
    <div class="ent-card mb-3 px-4 py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

            <div class="d-flex align-items-center gap-3">
                <div class="header-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div>
                    <p class="header-title">Manajemen Role &amp; Hak Akses</p>
                    <p class="header-subtitle">Kelola hak akses per role secara granular</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <input type="text" id="searchMenu" class="toolbar-input" placeholder="Cari menu…">
                <button type="button" class="toolbar-btn" id="btnExpandAll">
                    <i class="bi bi-arrows-expand"></i> Buka
                </button>
                <button type="button" class="toolbar-btn" id="btnCollapseAll">
                    <i class="bi bi-arrows-collapse"></i> Tutup
                </button>
            </div>

        </div>
    </div>

    {{-- PHP DATA SETUP --}}
    @php
        $categories = [
            'MASTER DATA' => [
                'users'     => 'Data Pengguna',
                'petugas'   => 'Data Petugas',
                'pelanggan' => 'Data Pelanggan',
                'kriteria'  => 'Data Kriteria',
            ],
            'OPERASIONAL' => [
                'penagihan' => 'Input Penagihan',
                'invoice'   => 'Cetak Invoice',
            ],
            'ANALITIK' => [
                'penilaian'  => 'Data Penilaian',
                'ranking'    => 'Hasil Ranking',
                'monitoring' => 'Monitoring Kinerja',
            ],
            'DASHBOARD & UTILITIES' => [
                'dashboard' => 'Dashboard Utama',
                'roles'     => 'Roles & Hak Akses',
            ],
        ];

        $actions = [
            'view'   => ['label' => 'View',   'short' => 'V'],
            'create' => ['label' => 'Create', 'short' => 'C'],
            'edit'   => ['label' => 'Edit',   'short' => 'E'],
            'delete' => ['label' => 'Delete', 'short' => 'D'],
            'export' => ['label' => 'Export', 'short' => 'X'],
        ];
    @endphp

    <form id="permissionForm" method="POST" action="/roles/save">
        @csrf

        {{-- ── DESKTOP TABLE ── --}}
        <div class="ent-card d-none d-md-block mb-3">
            <div class="table-scroll-wrapper">
                <table class="perm-table">

                    <thead>
                        {{-- Row 1: Role names --}}
                        <tr>
                            <th rowspan="2" class="col-menu" style="padding-left:16px;">
                                MENU
                            </th>
                            @foreach($roles as $role)
                                <th colspan="{{ count($actions) }}" class="text-center role-divider">
                                    <span class="role-badge">{{ $role->name }}</span>
                                    <div class="sa-role-wrap">
                                        <input class="sa-role-cb select-all-role"
                                               type="checkbox"
                                               id="sa_role_{{ $role->id }}"
                                               data-role-id="{{ $role->id }}">
                                        <label class="sa-role-lbl" for="sa_role_{{ $role->id }}">Semua</label>
                                    </div>
                                </th>
                            @endforeach
                        </tr>

                        {{-- Row 2: Action labels --}}
                        <tr>
                            @foreach($roles as $role)
                                @foreach($actions as $actionKey => $actionVal)
                                    <th class="text-center {{ $loop->first ? 'role-divider' : '' }}" style="min-width:38px;">
                                        <span class="action-label">{{ $actionVal['short'] }}</span>
                                        <input class="cb-action-all select-all-action-role"
                                               type="checkbox"
                                               data-role-id="{{ $role->id }}"
                                               data-action="{{ $actionKey }}">
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($categories as $categoryName => $menus)

                            {{-- Category divider row --}}
                            <tr class="category-header-row category-group" data-category-id="{{ Str::slug($categoryName) }}">
                                <td colspan="{{ 1 + (count($roles) * count($actions)) }}" style="padding-left:16px;">
                                    <a class="category-toggle" data-category-id="{{ Str::slug($categoryName) }}">
                                        <i class="bi bi-chevron-down"></i>
                                        <span>{{ $categoryName }}</span>
                                    </a>
                                </td>
                            </tr>

                            @foreach($menus as $menuKey => $menuLabel)
                                <tr class="menu-row"
                                    data-category-id="{{ Str::slug($categoryName) }}"
                                    data-menu-key="{{ $menuKey }}"
                                    data-menu-name="{{ $menuLabel }}">

                                    <td class="col-menu" style="padding-left:24px;">
                                        <span class="text-muted me-1" style="font-size:.65rem;">›</span>
                                        {{ $menuLabel }}
                                    </td>

                                    @foreach($roles as $role)
                                        @foreach($actions as $actionKey => $actionVal)
                                            @php
                                                $permName = "{$menuKey}:{$actionKey}";
                                                $perm     = $permissions->firstWhere('name', $permName);
                                            @endphp
                                            <td class="text-center {{ $loop->first ? 'role-divider' : '' }}">
                                                @if($perm)
                                                    <input type="checkbox"
                                                           class="perm-cb desktop-cb"
                                                           name="permissions[{{ $role->id }}][]"
                                                           value="{{ $perm->id }}"
                                                           data-sync="cb_{{ $role->id }}_{{ $perm->id }}"
                                                           data-role-id="{{ $role->id }}"
                                                           data-action="{{ $actionKey }}"
                                                           data-menu="{{ $menuKey }}"
                                                           @if(isset($rolePermissions[$role->id]) && in_array($perm->id, $rolePermissions[$role->id])) checked @endif>
                                                @else
                                                    <span style="color:var(--c-border-md);font-size:.7rem;">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endforeach

                                </tr>
                            @endforeach

                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        {{-- ── MOBILE ACCORDION ── --}}
        <div class="d-block d-md-none mb-3">
            @foreach($roles as $role)
                <div class="m-role-card">
                    <div class="m-role-header"
                         data-bs-toggle="collapse"
                         data-bs-target="#m_collapse_{{ $role->id }}"
                         aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="role-badge">{{ $role->name }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation();">
                            <input class="sa-role-cb mobile-select-all-role"
                                   type="checkbox"
                                   id="m_sa_{{ $role->id }}"
                                   data-role-id="{{ $role->id }}">
                            <label class="sa-role-lbl" for="m_sa_{{ $role->id }}">Semua</label>
                            <i class="bi bi-chevron-down" style="font-size:.7rem;color:var(--c-muted);"></i>
                        </div>
                    </div>

                    <div id="m_collapse_{{ $role->id }}"
                         class="collapse {{ $loop->first ? 'show' : '' }}">
                        @foreach($categories as $categoryName => $menus)
                            <div class="mobile-category-group" data-category-id="{{ Str::slug($categoryName) }}">
                                <div class="m-cat-header">{{ $categoryName }}</div>
                                @foreach($menus as $menuKey => $menuLabel)
                                    <div class="m-menu-item mobile-menu-row"
                                         data-menu-key="{{ $menuKey }}"
                                         data-menu-name="{{ $menuLabel }}">
                                        <div class="m-menu-name">{{ $menuLabel }}</div>
                                        <div class="m-action-wrap">
                                            @foreach($actions as $actionKey => $actionVal)
                                                @php
                                                    $permName = "{$menuKey}:{$actionKey}";
                                                    $perm     = $permissions->firstWhere('name', $permName);
                                                @endphp
                                                @if($perm)
                                                    <input type="checkbox"
                                                           class="m-cb-input mobile-cb"
                                                           name="permissions[{{ $role->id }}][]"
                                                           value="{{ $perm->id }}"
                                                           id="m_cb_{{ $role->id }}_{{ $perm->id }}"
                                                           data-sync="cb_{{ $role->id }}_{{ $perm->id }}"
                                                           data-role-id="{{ $role->id }}"
                                                           data-action="{{ $actionKey }}"
                                                           data-menu="{{ $menuKey }}"
                                                           @if(isset($rolePermissions[$role->id]) && in_array($perm->id, $rolePermissions[$role->id])) checked @endif>
                                                    <label class="m-cb-label"
                                                           for="m_cb_{{ $role->id }}_{{ $perm->id }}"
                                                           title="{{ $actionVal['label'] }}">
                                                        {{ $actionVal['short'] }}
                                                    </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── STICKY FOOTER ── --}}
        <div class="save-footer">
            <div class="footer-info">
                <i class="bi bi-check2-square me-1"></i>
                <strong id="counterSelected">0</strong>
                <span> / {{ count($permissions) * count($roles) }} permission aktif</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn-reset" onclick="window.location.reload()">Reset</button>
                <button type="submit" class="btn-save" id="btnSavePermissions">
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    <i class="bi bi-cloud-arrow-up icon-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>

    </form>
</div>

{{-- Toast --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
    <div id="permissionToast" class="toast ent-toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex align-items-center p-3 gap-2">
            <i id="toastIcon" class="bi fs-5"></i>
            <span id="toastMessage" class="flex-grow-1" style="font-size:.8rem;"></span>
            <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── SYNC DESKTOP ↔ MOBILE ─────────────────────────── */
    document.querySelectorAll('input[data-sync]').forEach(cb => {
        cb.addEventListener('change', function () {
            const id = this.dataset.sync;
            document.querySelectorAll(`input[data-sync="${id}"]`).forEach(c => {
                if (c !== this) c.checked = this.checked;
            });
            updateStates();
        });
    });

    /* ── SELECT ALL ROLE (desktop + mobile) ────────────── */
    function bindSelectAllRole(selector) {
        document.querySelectorAll(selector).forEach(sa => {
            sa.addEventListener('change', function () {
                const rid = this.dataset.roleId;
                const checked = this.checked;
                document.querySelectorAll(`input[data-role-id="${rid}"]`).forEach(cb => {
                    if (!cb.classList.contains('select-all-role') &&
                        !cb.classList.contains('select-all-action-role') &&
                        !cb.classList.contains('mobile-select-all-role')) {
                        cb.checked = checked;
                        const sid = cb.dataset.sync;
                        if (sid) document.querySelectorAll(`input[data-sync="${sid}"]`).forEach(c => c.checked = checked);
                    }
                });
                updateStates();
            });
        });
    }
    bindSelectAllRole('.select-all-role');
    bindSelectAllRole('.mobile-select-all-role');

    /* ── SELECT ALL ACTION PER ROLE ─────────────────────── */
    document.querySelectorAll('.select-all-action-role').forEach(sa => {
        sa.addEventListener('change', function () {
            const rid = this.dataset.roleId;
            const action = this.dataset.action;
            const checked = this.checked;
            document.querySelectorAll(`.desktop-cb[data-role-id="${rid}"][data-action="${action}"]`).forEach(cb => {
                cb.checked = checked;
                const sid = cb.dataset.sync;
                if (sid) document.querySelectorAll(`input[data-sync="${sid}"]`).forEach(c => c.checked = checked);
            });
            updateStates();
        });
    });

    /* ── COUNTERS & HEADER STATES ───────────────────────── */
    function updateStates() {
        const checked = document.querySelectorAll('.desktop-cb:checked').length;
        document.getElementById('counterSelected').textContent = checked;

        @foreach($roles as $role)
        (function () {
            const rid = "{{ $role->id }}";
            const all = document.querySelectorAll(`.desktop-cb[data-role-id="${rid}"]`);
            const chk = document.querySelectorAll(`.desktop-cb[data-role-id="${rid}"]:checked`);
            const allOk = all.length > 0 && chk.length === all.length;
            const part  = chk.length > 0 && chk.length < all.length;

            ['sa_role_', 'm_sa_'].forEach(prefix => {
                const el = document.getElementById(prefix + rid);
                if (el) { el.checked = allOk; el.indeterminate = part; }
            });

            @foreach($actions as $actionKey => $actionVal)
            (function () {
                const act = "{{ $actionKey }}";
                const aAll = document.querySelectorAll(`.desktop-cb[data-role-id="${rid}"][data-action="${act}"]`);
                const aChk = document.querySelectorAll(`.desktop-cb[data-role-id="${rid}"][data-action="${act}"]:checked`);
                const el = document.querySelector(`.select-all-action-role[data-role-id="${rid}"][data-action="${act}"]`);
                if (el) {
                    el.checked = aAll.length > 0 && aChk.length === aAll.length;
                    el.indeterminate = aChk.length > 0 && aChk.length < aAll.length;
                }
            })();
            @endforeach
        })();
        @endforeach
    }

    /* ── COLLAPSIBLE CATEGORIES ─────────────────────────── */
    document.querySelectorAll('.category-toggle').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const cid = this.dataset.categoryId;
            const rows = document.querySelectorAll(`.menu-row[data-category-id="${cid}"]`);
            const icon = this.querySelector('.bi');
            const isCollapsed = rows[0]?.classList.contains('collapsed-row');
            rows.forEach(r => r.classList.toggle('collapsed-row', !isCollapsed));
            icon?.classList.toggle('bi-chevron-right', !isCollapsed);
            icon?.classList.toggle('bi-chevron-down', isCollapsed);
        });
    });

    document.getElementById('btnExpandAll').addEventListener('click', () => {
        document.querySelectorAll('.menu-row').forEach(r => r.classList.remove('collapsed-row'));
        document.querySelectorAll('.category-toggle .bi').forEach(i => {
            i.classList.replace('bi-chevron-right', 'bi-chevron-down');
        });
    });
    document.getElementById('btnCollapseAll').addEventListener('click', () => {
        document.querySelectorAll('.menu-row').forEach(r => r.classList.add('collapsed-row'));
        document.querySelectorAll('.category-toggle .bi').forEach(i => {
            i.classList.replace('bi-chevron-down', 'bi-chevron-right');
        });
    });

    /* ── SEARCH ─────────────────────────────────────────── */
    document.getElementById('searchMenu').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();

        // Desktop
        document.querySelectorAll('.category-group').forEach(grp => {
            const cid = grp.dataset.categoryId;
            const rows = document.querySelectorAll(`.menu-row[data-category-id="${cid}"]`);
            let vis = 0;
            rows.forEach(r => {
                const match = r.dataset.menuName.toLowerCase().includes(q) || r.dataset.menuKey.toLowerCase().includes(q);
                r.classList.toggle('collapsed-row', !match);
                if (match) vis++;
            });
            grp.classList.toggle('d-none', vis === 0 && q !== '');
            if (q === '') {
                const icon = grp.querySelector('.category-toggle .bi');
                icon?.classList.replace('bi-chevron-right', 'bi-chevron-down');
            }
        });

        // Mobile
        document.querySelectorAll('.mobile-category-group').forEach(grp => {
            const items = grp.querySelectorAll('.mobile-menu-row');
            let vis = 0;
            items.forEach(i => {
                const match = i.dataset.menuName.toLowerCase().includes(q) || i.dataset.menuKey.toLowerCase().includes(q);
                i.classList.toggle('d-none', !match);
                if (match) vis++;
            });
            const hdr = grp.querySelector('.m-cat-header');
            const empty = vis === 0 && q !== '';
            hdr?.classList.toggle('d-none', empty);
            grp.classList.toggle('d-none', empty);
        });
    });

    /* ── TOAST ──────────────────────────────────────────── */
    function showToast(success, msg) {
        const el  = document.getElementById('permissionToast');
        const ico = document.getElementById('toastIcon');
        const txt = document.getElementById('toastMessage');
        el.classList.toggle('toast-success', success);
        el.classList.toggle('toast-error',   !success);
        ico.className = success ? 'bi bi-check-circle toast-icon-ok' : 'bi bi-exclamation-triangle toast-icon-err';
        txt.textContent = msg;
        new bootstrap.Toast(el, { delay: 4000 }).show();
    }

    /* ── AJAX SUBMIT ────────────────────────────────────── */
    document.getElementById('permissionForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn  = document.getElementById('btnSavePermissions');
        const spin = btn.querySelector('.spinner-border');
        const icon = btn.querySelector('.icon-save');
        btn.disabled = true;
        spin.classList.remove('d-none');
        icon.classList.add('d-none');

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: new FormData(this)
        })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(d  => showToast(d.success, d.message || (d.success ? 'Hak akses berhasil disimpan.' : 'Gagal menyimpan hak akses.')))
        .catch(() => showToast(false, 'Terjadi kesalahan sistem.'))
        .finally(() => {
            btn.disabled = false;
            spin.classList.add('d-none');
            icon.classList.remove('d-none');
        });
    });

    /* ── INIT ───────────────────────────────────────────── */
    updateStates();
});
</script>
@endpush