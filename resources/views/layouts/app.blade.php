<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SPK Penagihan') — PLN</title>
    

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

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
        font-size: 16px;
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
       SIDEBAR OVERLAY (Mobile)
    ================================================================ */
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 20, 50, 0.5);
        z-index: 99;
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
        transition: opacity var(--transition);
    }
    .sidebar-overlay.show {
        display: block;
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
    .sidebar-brand-logo-img {
        width: 30px;
        height: 30px;
        object-fit: contain;
        border-radius: 6px;
        background: white;
        padding: 2px;
    }
    .sidebar-brand-text {
        line-height: 1.25;
    }
    .sidebar-brand-name {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        letter-spacing: .2px;
    }
    .sidebar-brand-sub {
        font-size: 12px;
        color: rgba(255,255,255,.4);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    /* Close button — mobile only */
    .sidebar-close-btn {
        display: none;
        margin-left: auto;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background: rgba(255,255,255,.1);
        border: none;
        color: rgba(255,255,255,.7);
        font-size: 18px;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: background var(--transition);
    }
    .sidebar-close-btn:hover {
        background: rgba(255,255,255,.2);
        color: #fff;
    }

    /* Nav section label */
    .sidebar-label {
        padding: 18px 20px 6px;
        font-size: 12px;
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
        font-size: 15px;
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
        font-size: 17px;
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
        font-size: 13px;
        font-weight: 700;
        color: var(--pln-blue-deeper);
        flex-shrink: 0;
    }
    .sidebar-user-info { flex: 1; min-width: 0; }
    .sidebar-user-name {
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-user-role {
        font-size: 12px;
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
        font-size: 15px;
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
        min-width: 0; /* prevent overflow */
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
        min-width: 0;
    }
    .topbar-page-title {
        font-size: 17px;
        font-weight: 600;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .topbar-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: var(--text-muted);
    }
    .topbar-breadcrumb span { color: var(--text-secondary); }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .topbar-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 99px;
        background: var(--pln-blue-light);
        color: var(--pln-blue);
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
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
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }
    .topbar-username {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Hamburger button */
    .sidebar-toggle-btn {
        display: none;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--text-secondary);
        font-size: 20px;
        cursor: pointer;
        transition: background var(--transition), color var(--transition);
        flex-shrink: 0;
    }
    .sidebar-toggle-btn:hover {
        background: var(--pln-blue-light);
        color: var(--pln-blue);
        border-color: var(--pln-blue);
    }

    /* ================================================================
       TOPBAR DROPDOWN
    ================================================================ */
    .topbar-dropdown .dropdown-menu {
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
        padding: 8px 0;
        min-width: 220px;
        margin-top: 12px !important;
        animation: fadeIn 0.2s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .topbar-dropdown .dropdown-item {
        padding: 8px 20px;
        font-size: 15px;
        font-weight: 500;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background var(--transition), color var(--transition);
    }
    .topbar-dropdown .dropdown-item i {
        font-size: 18px;
        color: var(--text-muted);
        transition: color var(--transition);
    }
    .topbar-dropdown .dropdown-item:hover {
        background: var(--pln-blue-light);
        color: var(--pln-blue);
    }
    .topbar-dropdown .dropdown-item:hover i { color: var(--pln-blue); }
    .topbar-dropdown .dropdown-item-danger { color: #DC2626 !important; }
    .topbar-dropdown .dropdown-item-danger i { color: #DC2626 !important; }
    .topbar-dropdown .dropdown-item-danger:hover {
        background: #FEF2F2 !important;
        color: #B91C1C !important;
    }
    .topbar-dropdown .dropdown-divider {
        border-color: var(--border);
        margin: 6px 0;
    }
    .topbar-dropdown .dropdown-user-info { padding: 8px 20px 8px; }
    .topbar-dropdown .dropdown-user-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }
    .topbar-dropdown .dropdown-user-role {
        font-size: 14px;
        color: var(--text-muted);
        text-transform: capitalize;
        margin-top: 4px;
    }

    /* ================================================================
       PAGE CONTENT
    ================================================================ */
    .page-content {
        flex: 1;
        padding: 28px;
        background: var(--bg-body);
        min-width: 0;
    }

    /* ================================================================
       REUSABLE COMPONENTS
    ================================================================ */

    /* Cards */
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border) !important;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow-sm);
        transition: box-shadow var(--transition);
    }
    .card:hover { box-shadow: var(--shadow-md); }
    .card-header {
        background: transparent !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 16px 20px !important;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 16px;
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
        height: 100%;
    }
    .stat-card:hover { box-shadow: var(--shadow-md); }
    .stat-card-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 14px;
    }
    .stat-card-icon.blue   { background: var(--pln-blue-light); color: var(--pln-blue); }
    .stat-card-icon.yellow { background: #FEF3DC; color: var(--pln-yellow-dark); }
    .stat-card-icon.green  { background: #E8F8F0; color: #1A7A4A; }
    .stat-card-icon.red    { background: #FEE8E8; color: #C0392B; }
    .stat-card-label  { font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
    .stat-card-value  { font-size: 26px; font-weight: 700; color: var(--text-primary); line-height: 1; margin-bottom: 6px; }
    .stat-card-sub    { font-size: 14px; color: var(--text-secondary); }
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
        font-size: 15px;
        font-weight: 600;
        font-family: var(--font-sans);
        cursor: pointer;
        transition: background var(--transition), box-shadow var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    .btn-pln:hover { background: var(--pln-blue-dark); color: #fff; box-shadow: 0 4px 12px rgba(0,85,165,.3); }
    .btn-pln:disabled { opacity: .5; cursor: not-allowed; }
    .btn-pln-outline {
        background: transparent;
        color: var(--pln-blue);
        border: 1px solid var(--pln-blue);
        border-radius: var(--radius-md);
        padding: 7px 17px;
        font-size: 15px;
        font-weight: 600;
        font-family: var(--font-sans);
        cursor: pointer;
        transition: background var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    .btn-pln-outline:hover { background: var(--pln-blue-light); }
    .btn-pln-ghost {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 7px 16px;
        font-size: 15px;
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
    .badge-pln     { background: var(--pln-blue-light); color: var(--pln-blue);  border-radius: 99px; padding: 3px 9px; font-size: 13px; font-weight: 600; }
    .badge-success { background: #E8F8F0; color: #1A7A4A; border-radius: 99px; padding: 3px 9px; font-size: 13px; font-weight: 600; }
    .badge-warning { background: #FEF3DC; color: #B7600A; border-radius: 99px; padding: 3px 9px; font-size: 13px; font-weight: 600; }
    .badge-danger  { background: #FEE8E8; color: #C0392B; border-radius: 99px; padding: 3px 9px; font-size: 13px; font-weight: 600; }
    .badge-neutral { background: #EEF1F5; color: #5A6A7E;  border-radius: 99px; padding: 3px 9px; font-size: 13px; font-weight: 600; }

    /* Tables */
    .pln-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
    }
    .pln-table thead tr { background: #F5F8FC; }
    .pln-table th {
        padding: 10px 14px;
        text-align: left;
        font-size: 13px;
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
    @media (max-width: 768px) {
        .pln-table-wrap {
            overflow-x: auto; /* horizontal scroll on small screens */
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Alerts */
    .pln-alert {
        padding: 12px 16px;
        border-radius: var(--radius-md);
        font-size: 15px;
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
        font-size: 15px;
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
        font-size: 14px;
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
        font-size: 22px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 4px;
        letter-spacing: -.3px;
    }
    .page-header p {
        font-size: 15px;
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
    .pln-loading-card p { font-size: 15px; color: var(--text-secondary); margin: 14px 0 0; font-weight: 500; }
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
    .pagination { gap: 4px; flex-wrap: wrap; }
    .page-link {
        border-radius: var(--radius-sm) !important;
        border-color: var(--border) !important;
        color: var(--text-secondary) !important;
        font-size: 14px !important;
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
        font-size: 15px !important;
        color: var(--text-primary) !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--pln-blue) !important;
        box-shadow: 0 0 0 3px rgba(0,85,165,.1) !important;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #C5D2E0; border-radius: 99px; }
    ::-webkit-scrollbar-thumb:hover { background: #9AAABB; }

    /* ================================================================
       RESPONSIVE — Laptop/Desktop (1201px – 1399px)
    ================================================================ */
    @media (max-width: 1399px) and (min-width: 1200px) {
        .page-content { padding: 24px; }
    }

    /* ================================================================
       RESPONSIVE — Tablet Landscape (1025px – 1199px)
    ================================================================ */
    @media (min-width: 1025px) and (max-width: 1199px) {
        :root {
            --sidebar-w: 220px;
            --topbar-h: 56px;
        }
        .page-content { padding: 20px; }
        .stat-card-value { font-size: 22px; }
        .topbar { padding: 0 20px; }
        .topbar-badge { display: inline-flex !important; }
        h1 { font-size: 22px !important; }
    }

    /* ================================================================
       RESPONSIVE — Tablet (769px – 1024px)
    ================================================================ */
    @media (min-width: 769px) and (max-width: 1024px) {
        :root {
            --sidebar-w: 210px;
            --topbar-h: 56px;
        }
        .page-content { padding: 18px; }
        .stat-card-value { font-size: 20px; }
        .topbar { padding: 0 16px; }
        .topbar-badge { display: none; } /* hide on tablet to save space */
        .topbar-username { max-width: 80px; }
        h1 { font-size: 20px !important; }
        h2 { font-size: 18px !important; }
        h3 { font-size: 17px !important; }
    }

    /* ================================================================
       RESPONSIVE — Mobile (≤ 768px)
    ================================================================ */
    @media (max-width: 768px) {
        /* Sidebar hidden off-screen */
        .sidebar {
            transform: translateX(-100%);
            width: 260px; /* slightly wider on mobile for touch */
        }
        .sidebar.open {
            transform: translateX(0);
            box-shadow: 4px 0 24px rgba(0,20,60,.25);
        }

        /* Show close button inside sidebar on mobile */
        .sidebar-close-btn {
            display: inline-flex;
        }

        /* Main fills full width */
        .main-wrap { margin-left: 0; }

        /* Topbar adjustments */
        .topbar { padding: 0 14px; gap: 8px; }
        .topbar-badge { display: none; }
        .topbar-breadcrumb { display: none; }
        .topbar-page-title { font-size: 16px; }

        /* Show hamburger */
        .sidebar-toggle-btn { display: inline-flex; }

        /* Page content */
        .page-content { padding: 14px; }

        /* Page header */
        .page-header { margin-bottom: 16px; }
        .page-header h1 { font-size: 19px; }

        /* Stat cards stack nicely — use col-6 in child views */
        .stat-card-value { font-size: 22px; }
        .stat-card { padding: 16px; }

        /* Tables scroll horizontally */
        .pln-table-wrap { border-radius: var(--radius-md); }

        /* Buttons */
        .btn-pln, .btn-pln-outline, .btn-pln-ghost {
            padding: 7px 13px;
            font-size: 14px;
        }

        /* Username hidden on very small screens */
        .topbar-username { display: none; }
        .topbar-user { padding: 4px 6px; gap: 0; }
    }

    /* ================================================================
       RESPONSIVE — Very small (≤ 400px)
    ================================================================ */
    @media (max-width: 400px) {
        .page-content { padding: 10px; }
        .stat-card-value { font-size: 18px; }
        .page-header h1 { font-size: 17px; }
        .stat-card { padding: 12px; }
        h1 { font-size: 18px !important; }
        h2 { font-size: 16px !important; }
    }

    /* ================================================================
       RESPONSIVE UTILITIES — Typography
    ================================================================ */
    @media (max-width: 768px) {
        h1 { font-size: 20px !important; }
        h2 { font-size: 18px !important; }
        h3 { font-size: 16px !important; }
        p { font-size: 15px !important; }
    }

    /* ================================================================
       RESPONSIVE UTILITIES — Spacing Adjustments
    ================================================================ */
    @media (max-width: 768px) {
        .row { --bs-gutter-x: 0.75rem; }
        .container-fluid { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
    }

    /* ================================================================
       RESPONSIVE UTILITIES — Tables
    ================================================================ */
    .table-responsive-wrapper {
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .table-responsive-wrapper table {
        margin-bottom: 0;
    }
    @media (max-width: 768px) {
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table-responsive-wrapper table {
            min-width: 100%;
            font-size: 14px;
        }
        .table-responsive-wrapper thead { display: table-header-group; }
        .table-responsive-wrapper th,
        .table-responsive-wrapper td {
            padding: 8px 10px;
            white-space: nowrap;
        }
    }

    /* ================================================================
       RESPONSIVE UTILITIES — Cards
    ================================================================ */
    @media (max-width: 992px) {
        .kpi-card { padding: 16px; }
    }
    @media (max-width: 576px) {
        .stat-card,
        .kpi-card,
        .card {
            padding: 12px;
        }
    }

    /* ================================================================
       RESPONSIVE UTILITIES — Charts
    ================================================================ */
    .chart-container {
        position: relative;
        width: 100%;
        height: auto;
    }
    @media (max-width: 768px) {
        .chart-container { min-height: 250px; }
        .chart-card { padding: 16px; }
    }
    @media (max-width: 576px) {
        .chart-container { min-height: 220px; }
        .chart-card { padding: 12px; }
    }

    /* ================================================================
       RESPONSIVE UTILITIES — Buttons & Actions
    ================================================================ */
    @media (max-width: 768px) {
        .dashboard-header-actions,
        .btn-group-actions {
            flex-wrap: wrap;
            gap: 8px;
        }
        .dashboard-header-actions .btn-action,
        .btn-group-actions .btn-pln {
            flex: 1 1 48%;
            min-width: 100px;
        }
    }
    @media (max-width: 576px) {
        .dashboard-header-actions .btn-action,
        .btn-group-actions .btn-pln {
            flex: 1 1 100%;
        }
    }

    /* ================================================================
       RESPONSIVE UTILITIES — Forms & Filters
    ================================================================ */
    .form-group-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    @media (max-width: 1024px) {
        .form-group-row {
            gap: 10px;
        }
    }
    @media (max-width: 768px) {
        .form-group-row {
            flex-direction: column;
            gap: 8px;
        }
        .form-group-row > * {
            flex: 1 1 100%;
        }
    }

    /* ================================================================
       RESPONSIVE UTILITIES — Modal Adjustments
    ================================================================ */
    @media (max-width: 576px) {
        .modal-dialog { margin: 8px; }
        .modal-content { border-radius: var(--radius-md); }
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

{{-- Overlay untuk sidebar pada tampilan mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-shell">

    {{-- Menampilkan navigasi sidebar --}}
    <aside class="sidebar" id="sidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo_pln.png') }}" alt="PLN" class="sidebar-brand-logo-img">
            <div class="sidebar-brand-text">
                <div class="sidebar-brand-name">PLN</div>
                <div class="sidebar-brand-sub">Smart Collection</div>
            </div>
            <!-- Close button shown on mobile -->
            <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Tutup menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav">

            @if(has_permission('dashboard:view'))
            <div class="sidebar-label">Utama</div>
            <a href="/dashboard"
               class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 nav-icon"></i>
                Dashboard
            </a>
            @endif

            @if(has_permission('roles:view') || has_permission('users:view') || has_permission('petugas:view') || has_permission('kriteria:view') || has_permission('pelanggan:view'))
            <div class="sidebar-label">Master Data</div>
            @endif

            @if(has_permission('roles:view'))
            <a href="/roles"
               class="nav-item {{ request()->is('roles*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock nav-icon"></i>
                Roles &amp; Akses
            </a>
            @endif

            @if(has_permission('users:view'))
            <a href="/datapengguna"
               class="nav-item {{ request()->is('datapengguna*') ? 'active' : '' }}">
                <i class="bi bi-people nav-icon"></i>
                Data Pengguna
            </a>
            @endif

            @if(has_permission('petugas:view'))
            <a href="/datapetugas"
               class="nav-item {{ request()->is('datapetugas*') ? 'active' : '' }}">
                <i class="bi bi-person-badge nav-icon"></i>
                Data Petugas
            </a>
            @endif

            @if(has_permission('kriteria:view'))
            <a href="/datakriteria"
               class="nav-item {{ request()->is('datakriteria*') ? 'active' : '' }}">
                <i class="bi bi-sliders nav-icon"></i>
                Data Kriteria
            </a>
            @endif

            @if(has_permission('pelanggan:view'))
            <a href="/pelanggan"
               class="nav-item {{ request()->is('pelanggan*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill nav-icon"></i>
                Data Pelanggan
            </a>
            @endif

            @if(has_permission('penilaian:view') || has_permission('penagihan:view') || has_permission('invoice:view') || has_permission('ranking:view'))
            <div class="sidebar-label">Operasional</div>
            @endif

            @if(has_permission('penagihan:view'))
            <a href="/penagihan"
               class="nav-item {{ request()->is('penagihan*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square nav-icon"></i>
                Input Penagihan
            </a>
            @endif

            @if(has_permission('invoice:view'))
            <a href="{{ route('invoice.index') }}"
               class="nav-item {{ request()->is('invoices*') ? 'active' : '' }}">
                <i class="bi bi-receipt nav-icon"></i>
                Cetak Invoice
            </a>
            @endif

            @if(has_permission('penilaian:view') || has_permission('ranking:view'))
            <div class="sidebar-label">SPK &amp; Analitik</div>
            @endif

            @if(has_permission('penilaian:view'))
            <a href="/penilaian"
               class="nav-item {{ request()->is('penilaian*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data nav-icon"></i>
                Data Penilaian
            </a>
            @endif

            @if(has_permission('ranking:view'))
            <a href="/ranking"
               class="nav-item {{ request()->is('ranking*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-steps nav-icon"></i>
                Hasil Ranking
            </a>
            @endif

            @if(has_permission('monitoring:view'))
            <a href="{{ route('monitoring.index') }}"
               class="nav-item {{ request()->is('monitoringkinerja*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow nav-icon"></i>
                Monitoring Kinerja
            </a>
            @endif

        </div>
    </aside>

    {{-- Wrapper utama untuk konten halaman --}}
    <div class="main-wrap">

        <!-- TOP BAR -->
        <header class="topbar">
            <div class="topbar-left">

                <!-- Hamburger — visible on mobile & tablet -->
                <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Buka menu">
                    <i class="bi bi-list"></i>
                </button>

                <div>
                   <div class="topbar-page-title">
    @yield('page_title', View::yieldContent('title', 'Dashboard'))
</div>

<div class="topbar-breadcrumb">
    <i class="bi bi-house" style="font-size: 13px"></i>
    <span>/</span>
    <span>@yield('page_title', View::yieldContent('title', 'Dashboard'))</span>
</div>
                </div>
            </div>

            <div class="topbar-right">
                <span class="topbar-badge">
                    <i class="bi bi-building" style="font-size: 13px"></i>
                    PLN ULP RIVAI
                </span>

                <div class="dropdown topbar-dropdown">
                    <div class="topbar-user" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <div class="topbar-avatar">{{ $userInitial }}</div>
                        <span class="topbar-username">{{ $userName }}</span>
                        <i class="bi bi-chevron-down" style="font-size: 12px;color:var(--text-muted);margin-left:4px"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-user-info">
                            <div class="dropdown-user-name">{{ $userName }}</div>
                            <div class="dropdown-user-role">{{ $role }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item dropdown-item-danger" href="/logout">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Area konten utama halaman --}}
        <main class="page-content">
            @yield('content')
        </main>

    </div>
</div>

{{-- Memuat script Bootstrap --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Script untuk mengatur interaksi sidebar mobile --}}
<script>
(function () {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const closeBtn  = document.getElementById('sidebarCloseBtn');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden'; // prevent scroll behind overlay
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    // Hamburger button
    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (sidebar.classList.contains('open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    // Close button inside sidebar
    closeBtn.addEventListener('click', closeSidebar);

    // Clicking overlay closes sidebar
    overlay.addEventListener('click', closeSidebar);

    // Close sidebar when a nav link is clicked (mobile navigation)
    document.querySelectorAll('.nav-item').forEach(function (item) {
        item.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });

    // On resize to desktop, reset mobile state
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
})();
</script>

@stack('scripts')

</body>
</html>
