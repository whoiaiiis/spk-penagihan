<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SPK Penagihan') — PLN</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    /* ================================================================
       CSS VARIABLES — PLN Brand System
    ================================================================ */
    :root {
        --pln-blue:        #0055A5;
        --pln-blue-dark:   #003F7F;
        --pln-blue-deeper: #002B58;
        --pln-blue-light:  #E8F1FC;
        --pln-yellow:      #F5A623;
        --pln-yellow-dark: #D4891A;

        --sidebar-w:       240px;
        --topbar-h:        60px;

        --bg-body:         #F0F4F9;
        --bg-card:         #FFFFFF;
        --text-primary:    #0D1B2E;
        --text-secondary:  #5A6A7E;
        --text-muted:      #9AAABB;
        --border:          #E2E9F1;

        --radius-sm:  6px;
        --radius-md:  10px;
        --radius-lg:  14px;
        --radius-xl:  20px;

        --shadow-sm:  0 1px 3px rgba(0,55,130,.06), 0 1px 2px rgba(0,55,130,.04);
        --shadow-md:  0 4px 12px rgba(0,55,130,.08), 0 2px 4px rgba(0,55,130,.05);
        --shadow-lg:  0 12px 32px rgba(0,55,130,.12), 0 4px 8px rgba(0,55,130,.06);

        --font-sans: 'Plus Jakarta Sans', sans-serif;
        --font-mono: 'DM Mono', monospace;

        --transition: 0.18s cubic-bezier(.4,0,.2,1);
    }

    /* ================================================================
       RESET & BASE
    ================================================================ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        height: 100%;
        font-family: var(--font-sans);
        font-size: 14px;
        color: var(--text-primary);
        background: var(--bg-body);
        -webkit-font-smoothing: antialiased;
    }

    /* ================================================================
       LAYOUT SHELL
    ================================================================ */
    .app-shell {
        display: flex;
        min-height: 100vh;
    }

    /* ================================================================
       SIDEBAR
    ================================================================ */
    .sidebar {
        width: var(--sidebar-w);
        min-height: 100vh;
        position: fixed;
        top: 0; left: 0;
        display: flex;
        flex-direction: column;
        background: var(--pln-blue-deeper);
        z-index: 100;
        transition: transform var(--transition);
    }

    /* Decorative top accent bar */
    .sidebar::before {
        content: '';
        display: block;
        height: 3px;
        background: linear-gradient(90deg, var(--pln-yellow), #FFD166, var(--pln-yellow));
        flex-shrink: 0;
    }

    /* Brand / Logo */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid rgba(255,255,255,.08);
        flex-shrink: 0;
    }
    .sidebar-brand-logo {
        width: 36px;
        height: 36px;
        background: var(--pln-yellow);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: var(--pln-blue-deeper);
        letter-spacing: -.5px;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(245,166,35,.4);
    }
    .sidebar-brand-text {
        line-height: 1.25;
    }
    .sidebar-brand-name {
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        letter-spacing: .2px;
    }
    .sidebar-brand-sub {
        font-size: 10px;
        color: rgba(255,255,255,.4);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    /* Nav section label */
    .sidebar-label {
        padding: 18px 20px 6px;
        font-size: 10px;
        font-weight: 600;
        color: rgba(255,255,255,.3);
        text-transform: uppercase;
        letter-spacing: .8px;
    }

    /* Nav links */
    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 4px 12px 12px;
        scrollbar-width: none;
    }
    .sidebar-nav::-webkit-scrollbar { display: none; }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 10px;
        border-radius: var(--radius-md);
        color: rgba(255,255,255,.65);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: background var(--transition), color var(--transition);
        cursor: pointer;
        margin-bottom: 1px;
        position: relative;
    }
    .nav-item:hover {
        background: rgba(255,255,255,.08);
        color: rgba(255,255,255,.95);
        text-decoration: none;
    }
    .nav-item.active {
        background: rgba(255,255,255,.13);
        color: #fff;
        font-weight: 600;
    }
    .nav-item.active::before {
        content: '';
        position: absolute;
        left: 0; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 18px;
        background: var(--pln-yellow);
        border-radius: 0 3px 3px 0;
    }
    .nav-item .nav-icon {
        width: 18px;
        text-align: center;
        font-size: 15px;
        flex-shrink: 0;
        opacity: .8;
    }
    .nav-item.active .nav-icon { opacity: 1; }

    /* Sidebar footer */
    .sidebar-footer {
        padding: 12px;
        border-top: 1px solid rgba(255,255,255,.08);
        flex-shrink: 0;
    }
    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 10px;
        border-radius: var(--radius-md);
        background: rgba(255,255,255,.06);
    }
    .sidebar-avatar {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--pln-yellow), #FFD166);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: var(--pln-blue-deeper);
        flex-shrink: 0;
    }
    .sidebar-user-info { flex: 1; min-width: 0; }
    .sidebar-user-name {
        font-size: 12px;
        font-weight: 600;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-user-role {
        font-size: 10px;
        color: rgba(255,255,255,.4);
        text-transform: capitalize;
    }
    .sidebar-logout {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        background: rgba(255,255,255,.07);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.5);
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        transition: background var(--transition), color var(--transition);
        flex-shrink: 0;
    }
    .sidebar-logout:hover {
        background: rgba(220,60,60,.25);
        color: #FF8A8A;
    }

    /* ================================================================
       MAIN CONTENT AREA
    ================================================================ */
    .main-wrap {
        margin-left: var(--sidebar-w);
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* ================================================================
       TOP BAR
    ================================================================ */
    .topbar {
        height: var(--topbar-h);
        background: var(--bg-card);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 28px;
        position: sticky;
        top: 0;
        z-index: 50;
        box-shadow: var(--shadow-sm);
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .topbar-page-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .topbar-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text-muted);
    }
    .topbar-breadcrumb span { color: var(--text-secondary); }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .topbar-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 99px;
        background: var(--pln-blue-light);
        color: var(--pln-blue);
        font-size: 11px;
        font-weight: 600;
    }
    .topbar-notif {
        width: 34px;
        height: 34px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        background: var(--bg-card);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        font-size: 15px;
        cursor: pointer;
        transition: background var(--transition), border-color var(--transition);
        position: relative;
    }
    .topbar-notif:hover {
        background: var(--pln-blue-light);
        border-color: var(--pln-blue);
        color: var(--pln-blue);
    }
    .topbar-divider {
        width: 1px;
        height: 22px;
        background: var(--border);
    }
    .topbar-user {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 10px 4px 4px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        background: var(--bg-card);
        cursor: pointer;
        transition: background var(--transition);
    }
    .topbar-user:hover { background: var(--pln-blue-light); }
    .topbar-avatar {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        background: linear-gradient(135deg, var(--pln-blue), var(--pln-blue-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
    }
    .topbar-username {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* ================================================================
       PAGE CONTENT
    ================================================================ */
    .page-content {
        flex: 1;
        padding: 28px;
        background: var(--bg-body);
    }

    /* ================================================================
       REUSABLE COMPONENTS (available to all child views)
    ================================================================ */

    /* Cards */
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border) !important;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow-sm);
        transition: box-shadow var(--transition), transform var(--transition);
    }
    .card:hover {
        box-shadow: var(--shadow-md);
        transform: none; /* override Bootstrap hover scale */
    }
    .card-header {
        background: transparent !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 16px 20px !important;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 14px;
    }
    .card-body { padding: 20px !important; }

    /* Stat Cards */
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
        transition: box-shadow var(--transition);
    }
    .stat-card:hover { box-shadow: var(--shadow-md); }
    .stat-card-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 14px;
    }
    .stat-card-icon.blue   { background: var(--pln-blue-light); color: var(--pln-blue); }
    .stat-card-icon.yellow { background: #FEF3DC; color: var(--pln-yellow-dark); }
    .stat-card-icon.green  { background: #E8F8F0; color: #1A7A4A; }
    .stat-card-icon.red    { background: #FEE8E8; color: #C0392B; }
    .stat-card-label  { font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
    .stat-card-value  { font-size: 24px; font-weight: 700; color: var(--text-primary); line-height: 1; margin-bottom: 6px; }
    .stat-card-sub    { font-size: 12px; color: var(--text-secondary); }
    .stat-card-accent {
        position: absolute;
        right: -10px; top: -10px;
        width: 80px; height: 80px;
        border-radius: 50%;
        opacity: .05;
        background: var(--pln-blue);
    }

    /* Buttons */
    .btn-pln {
        background: var(--pln-blue);
        color: #fff;
        border: none;
        border-radius: var(--radius-md);
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        font-family: var(--font-sans);
        cursor: pointer;
        transition: background var(--transition), box-shadow var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-pln:hover { background: var(--pln-blue-dark); color: #fff; box-shadow: 0 4px 12px rgba(0,85,165,.3); }
    .btn-pln:disabled { opacity: .5; cursor: not-allowed; }
    .btn-pln-outline {
        background: transparent;
        color: var(--pln-blue);
        border: 1px solid var(--pln-blue);
        border-radius: var(--radius-md);
        padding: 7px 17px;
        font-size: 13px;
        font-weight: 600;
        font-family: var(--font-sans);
        cursor: pointer;
        transition: background var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-pln-outline:hover { background: var(--pln-blue-light); }
    .btn-pln-ghost {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 7px 16px;
        font-size: 13px;
        font-weight: 500;
        font-family: var(--font-sans);
        cursor: pointer;
        transition: background var(--transition), border-color var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-pln-ghost:hover { background: var(--bg-body); border-color: #C5D2E0; color: var(--text-primary); }

    /* Badges */
    .badge-pln       { background: var(--pln-blue-light);  color: var(--pln-blue);    border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; }
    .badge-success   { background: #E8F8F0; color: #1A7A4A; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; }
    .badge-warning   { background: #FEF3DC; color: #B7600A; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; }
    .badge-danger    { background: #FEE8E8; color: #C0392B; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; }
    .badge-neutral   { background: #EEF1F5; color: #5A6A7E; border-radius: 99px; padding: 3px 9px; font-size: 11px; font-weight: 600; }

    /* Tables */
    .pln-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .pln-table thead tr { background: #F5F8FC; }
    .pln-table th {
        padding: 10px 14px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .5px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .pln-table td {
        padding: 11px 14px;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .pln-table tr:last-child td { border-bottom: none; }
    .pln-table tr:hover td { background: #F8FAFE; }
    .pln-table-wrap {
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    /* Alerts */
    .pln-alert {
        padding: 12px 16px;
        border-radius: var(--radius-md);
        font-size: 13px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid transparent;
    }
    .pln-alert-success { background: #E8F8F0; color: #1A5E37; border-color: #A8DFC0; }
    .pln-alert-danger  { background: #FEE8E8; color: #8B1A1A; border-color: #F5AAAA; }
    .pln-alert-warning { background: #FEF3DC; color: #7A4A00; border-color: #F5CF88; }
    .pln-alert-info    { background: var(--pln-blue-light); color: #003E7A; border-color: #A8C8F5; }

    /* Form inputs */
    .pln-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 13px;
        font-family: var(--font-sans);
        color: var(--text-primary);
        background: var(--bg-card);
        transition: border-color var(--transition), box-shadow var(--transition);
        outline: none;
    }
    .pln-input:focus {
        border-color: var(--pln-blue);
        box-shadow: 0 0 0 3px rgba(0,85,165,.1);
    }
    .pln-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    /* Page header */
    .page-header {
        margin-bottom: 24px;
    }
    .page-header h1 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 4px;
        letter-spacing: -.3px;
    }
    .page-header p {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0;
    }

    /* Loading overlay */
    .pln-loading {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(240,244,249,.85);
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }
    .pln-loading.show { display: flex; }
    .pln-loading-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 32px 48px;
        text-align: center;
        box-shadow: var(--shadow-lg);
    }
    .pln-loading-card p { font-size: 13px; color: var(--text-secondary); margin: 14px 0 0; font-weight: 500; }
    .pln-spinner {
        width: 36px; height: 36px;
        border: 3px solid var(--border);
        border-top-color: var(--pln-blue);
        border-radius: 50%;
        animation: pln-spin .7s linear infinite;
        margin: 0 auto;
    }
    @keyframes pln-spin { to { transform: rotate(360deg); } }

    /* Pagination override */
    .pagination { gap: 4px; }
    .page-link {
        border-radius: var(--radius-sm) !important;
        border-color: var(--border) !important;
        color: var(--text-secondary) !important;
        font-size: 12px !important;
        padding: 5px 10px !important;
        font-family: var(--font-sans);
    }
    .page-link:hover { background: var(--pln-blue-light) !important; color: var(--pln-blue) !important; }
    .page-item.active .page-link { background: var(--pln-blue) !important; border-color: var(--pln-blue) !important; color: #fff !important; }

    /* Bootstrap overrides */
    .btn { font-family: var(--font-sans) !important; }
    .form-control, .form-select {
        border-color: var(--border) !important;
        border-radius: var(--radius-md) !important;
        font-family: var(--font-sans) !important;
        font-size: 13px !important;
        color: var(--text-primary) !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--pln-blue) !important;
        box-shadow: 0 0 0 3px rgba(0,85,165,.1) !important;
    }

    /* Active nav detection */
    .nav-item[href="{{ request()->url() }}"],
    .nav-item.active { /* set via JS */ }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #C5D2E0; border-radius: 99px; }
    ::-webkit-scrollbar-thumb:hover { background: #9AAABB; }

    /* Mobile */
    @media (max-width: 768px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .main-wrap { margin-left: 0; }
        .page-content { padding: 16px; }
        .topbar { padding: 0 16px; }
    }
    </style>

    @stack('styles')
</head>

<body>

@php
    $permissions = Session::get('permissions') ?? [];
    $role        = Session::get('role') ?? '';
    $user        = Session::get('user');
    $userName    = $user->name ?? 'User';
    $userInitial = strtoupper(substr($userName, 0, 1));
    $currentUrl  = request()->url();
@endphp

<div class="app-shell">

    <!-- ============================================================
         SIDEBAR
    ============================================================ -->
    <aside class="sidebar" id="sidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="sidebar-brand-logo">PLN</div>
            <div class="sidebar-brand-text">
                <div class="sidebar-brand-name">SPK Penagihan</div>
                <div class="sidebar-brand-sub">Sistem Pendukung Keputusan</div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav">

            @if(in_array('dashboard', $permissions))
            <div class="sidebar-label">Utama</div>
            <a href="/dashboard"
               class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 nav-icon"></i>
                Dashboard
            </a>
            @endif

            @if(in_array('roles', $permissions) || in_array('data_pengguna', $permissions) || in_array('data_petugas', $permissions) || in_array('data_kriteria', $permissions))
            <div class="sidebar-label">Master Data</div>
            @endif

            @if(in_array('roles', $permissions))
            <a href="/roles"
               class="nav-item {{ request()->is('roles*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock nav-icon"></i>
                Roles &amp; Akses
            </a>
            @endif

            @if(in_array('data_pengguna', $permissions))
            <a href="/datapengguna"
               class="nav-item {{ request()->is('datapengguna*') ? 'active' : '' }}">
                <i class="bi bi-people nav-icon"></i>
                Data Pengguna
            </a>
            @endif

            @if(in_array('data_petugas', $permissions))
            <a href="/datapetugas"
               class="nav-item {{ request()->is('datapetugas*') ? 'active' : '' }}">
                <i class="bi bi-person-badge nav-icon"></i>
                Data Petugas
            </a>
            @endif

            @if(in_array('data_kriteria', $permissions))
            <a href="/datakriteria"
               class="nav-item {{ request()->is('datakriteria*') ? 'active' : '' }}">
                <i class="bi bi-sliders nav-icon"></i>
                Data Kriteria
            </a>
            @endif

            @if(in_array('data_pelanggan', $permissions))
            <a href="/pelanggan"
               class="nav-item {{ request()->is('pelanggan*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill nav-icon"></i>
                Data Pelanggan
            </a>
            @endif

            @if(in_array('data_penilaian', $permissions) || in_array('input_penagihan', $permissions) || in_array('cetak_invoice', $permissions) || in_array('hasil_ranking', $permissions))
            <div class="sidebar-label">Operasional</div>
            @endif

            @if(in_array('input_penagihan', $permissions))
            <a href="/penagihan"
               class="nav-item {{ request()->is('penagihan*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square nav-icon"></i>
                Input Penagihan
            </a>
            @endif

            @if(in_array('cetak_invoice', $permissions))
            <a href="/invoice"
               class="nav-item {{ request()->is('invoice*') ? 'active' : '' }}">
                <i class="bi bi-receipt nav-icon"></i>
                Cetak Invoice
            </a>
            @endif

            @if(in_array('data_penilaian', $permissions) || in_array('hasil_ranking', $permissions))
            <div class="sidebar-label">SPK &amp; Analitik</div>
            @endif

            @if(in_array('data_penilaian', $permissions))
            <a href="/penilaian"
               class="nav-item {{ request()->is('penilaian*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data nav-icon"></i>
                Data Penilaian
            </a>
            @endif

            @if(in_array('hasil_ranking', $permissions))
            <a href="/ranking"
               class="nav-item {{ request()->is('ranking*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-steps nav-icon"></i>
                Hasil Ranking
            </a>
            @endif

            @if(in_array('monitoring', $permissions))
            <a href="/monitoring"
               class="nav-item {{ request()->is('monitoring*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow nav-icon"></i>
                Monitoring Kinerja
            </a>
            @endif

        </div>

        <!-- Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">{{ $userInitial }}</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ $userName }}</div>
                    <div class="sidebar-user-role">{{ $role }}</div>
                </div>
                <a href="/logout" class="sidebar-logout" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

    </aside>

    <!-- ============================================================
         MAIN
    ============================================================ -->
    <div class="main-wrap">

        <!-- TOP BAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn-pln-ghost d-md-none" style="padding:6px 10px"
                        onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i class="bi bi-list" style="font-size:18px"></i>
                </button>
                <div>
                    <div class="topbar-page-title">@yield('page_title', 'Dashboard')</div>
                    <div class="topbar-breadcrumb">
                        <i class="bi bi-house" style="font-size:11px"></i>
                        <span>/</span>
                        <span>@yield('page_title', 'Dashboard')</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-badge">
                    <i class="bi bi-building" style="font-size:11px"></i>
                    PLN UP3
                </span>
                <div class="topbar-divider"></div>
                <div class="topbar-notif" title="Notifikasi">
                    <i class="bi bi-bell"></i>
                </div>
                <div class="topbar-user">
                    <div class="topbar-avatar">{{ $userInitial }}</div>
                    <span class="topbar-username">{{ $userName }}</span>
                    <i class="bi bi-chevron-down" style="font-size:10px;color:var(--text-muted)"></i>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="page-content">
            @yield('content')
        </main>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Close sidebar on outside click (mobile)
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth <= 768 &&
        sidebar.classList.contains('open') &&
        !sidebar.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});
</script>

@stack('scripts')

</body>
</html>