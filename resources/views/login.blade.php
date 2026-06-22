<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SPK Penagihan PLN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --blue:        #0055A5;
        --blue-dark:   #003F7F;
        --blue-deeper: #002B58;
        --blue-light:  #E8F1FC;
        --yellow:      #F5A623;
        --yellow-dark: #D4891A;
        --bg:          #F0F4F9;
        --white:       #FFFFFF;
        --border:      #DDE5F0;
        --text:        #0D1B2E;
        --muted:       #7A8FA6;
    }

    html, body {
        height: 100%;
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: var(--bg);
        -webkit-font-smoothing: antialiased;
    }

    /* ── LAYOUT ── */
    .login-shell {
        min-height: 100vh;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    /* ── LEFT PANEL ── */
    .login-left {
        background: var(--blue-deeper);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 48px;
    }

    .login-left::before,
    .login-left::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        opacity: .06;
        background: #fff;
    }
    .login-left::before { width: 500px; height: 500px; right: -120px; top: -120px; }
    .login-left::after  { width: 320px; height: 320px; left: -80px; bottom: -80px; }

    .left-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
        background-size: 40px 40px;
    }

    .left-top { position: relative; z-index: 2; }

    .left-logo {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 60px;
    }
    .left-logo-badge {
        width: 52px; height: 52px; min-width: 52px;
        border-radius: 14px;
        background: #FFD54A;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(255,193,7,.35);
    }
    .left-logo-badge img { width: 34px; height: 34px; object-fit: contain; display: block; }
    .left-logo-name { font-size: 19px; font-weight: 700; color: #fff; line-height: 1.1; margin-bottom: 2px; }
    .left-logo-sub  { font-size: 13px; color: rgba(255,255,255,.55); font-weight: 500; text-transform: uppercase; letter-spacing: .8px; line-height: 1.3; }

    .left-headline {
        font-size: 34px; font-weight: 800; color: #fff;
        line-height: 1.2; letter-spacing: -.5px; margin-bottom: 16px;
    }
    .left-headline span { color: var(--yellow); }
    .left-desc { font-size: 16px; color: rgba(255,255,255,.5); line-height: 1.7; max-width: 320px; }

    .left-features { display: flex; flex-direction: column; gap: 10px; margin-top: 40px; position: relative; z-index: 2; }
    .feature-chip {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 14px;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 10px;
        font-size: 15px; color: rgba(255,255,255,.75); font-weight: 500;
        transition: background .15s;
    }
    .feature-chip:hover { background: rgba(255,255,255,.1); }
    .feature-chip-icon {
        width: 28px; height: 28px;
        background: rgba(245,166,35,.15);
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        color: var(--yellow); font-size: 15px; flex-shrink: 0;
    }

    .left-bottom { position: relative; z-index: 2; font-size: 13px; color: rgba(255,255,255,.25); font-weight: 500; }

    /* ── RIGHT PANEL ── */
    .login-right {
        background: var(--white);
        display: flex; align-items: center; justify-content: center;
        padding: 48px 40px;
        position: relative;
    }
    .login-right::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 120px; height: 120px;
        background: linear-gradient(135deg, var(--blue-light), transparent);
        border-radius: 0 0 0 100%;
    }

    .login-form-wrap { width: 100%; max-width: 380px; }

    .form-header { margin-bottom: 32px; }
    .form-header-eyebrow {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 10px;
        background: var(--blue-light); color: var(--blue);
        border-radius: 99px;
        font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .6px;
        margin-bottom: 14px;
    }
    .form-header h2 { font-size: 28px; font-weight: 800; color: var(--text); letter-spacing: -.4px; margin-bottom: 6px; }
    .form-header p  { font-size: 15px; color: var(--muted); line-height: 1.6; }

    /* Alert */
    .login-alert {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 12px 14px;
        background: #FEE8E8; border: 1px solid #F5AAAA; border-radius: 10px;
        font-size: 15px; color: #8B1A1A;
        margin-bottom: 20px;
        animation: shake .35s ease;
    }
    .login-alert i { margin-top: 1px; flex-shrink: 0; font-size: 17px; }
    @keyframes shake {
        0%,100% { transform: translateX(0); }
        20%      { transform: translateX(-5px); }
        60%      { transform: translateX(5px); }
    }

    /* Field group */
    .field-group { margin-bottom: 18px; }
    .field-label {
        display: block; font-size: 14px; font-weight: 600; color: #4A5A6A;
        margin-bottom: 6px; text-transform: uppercase; letter-spacing: .5px;
    }
    .field-wrap { position: relative; }
    .field-icon {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        color: var(--muted); font-size: 17px; pointer-events: none; transition: color .15s;
    }
    .field-input {
        width: 100%;
        padding: 10px 12px 10px 38px;
        border: 1.5px solid var(--border); border-radius: 10px;
        font-size: 15px; font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text); background: #FAFBFD;
        transition: border-color .15s, box-shadow .15s, background .15s;
        outline: none;
    }
    .field-input:focus {
        border-color: var(--blue); background: #fff;
        box-shadow: 0 0 0 3px rgba(0,85,165,.1);
    }
    .field-wrap:focus-within .field-icon { color: var(--blue); }
    .field-input.has-toggle { padding-right: 44px; }
    .field-icon-right {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        color: var(--muted); font-size: 17px; cursor: pointer; transition: color .15s;
        background: none; border: none; padding: 0; display: flex;
    }
    .field-icon-right:hover { color: var(--text); }

    /* Error hint */
    .field-error { font-size: 14px; color: #C0392B; margin-top: 5px; display: block; }

    /* Submit */
    .btn-login {
        width: 100%; padding: 12px;
        background: var(--blue); color: #fff;
        border: none; border-radius: 10px;
        font-size: 16px; font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;
        cursor: pointer; transition: background .15s, box-shadow .15s, transform .1s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        margin-top: 8px; position: relative; overflow: hidden;
    }
    .btn-login::before {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.1), transparent);
    }
    .btn-login:hover {
        background: var(--blue-dark);
        box-shadow: 0 6px 20px rgba(0,85,165,.35);
        transform: translateY(-1px);
    }
    .btn-login:active  { transform: translateY(0); }
    .btn-login.loading { pointer-events: none; opacity: .8; }
    .btn-login .spinner {
        display: none; width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.3); border-top-color: #fff;
        border-radius: 50%; animation: spin .6s linear infinite;
    }
    .btn-login.loading .spinner  { display: block; }
    .btn-login.loading .btn-text { display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Footer */
    .form-footer {
        margin-top: 32px; padding-top: 20px;
        border-top: 1px solid var(--border);
        text-align: center; font-size: 13px; color: var(--muted);
    }

    /* ── RESPONSIVE ── */
    @media (min-width: 1025px) and (max-width: 1199px) {
        .login-shell { grid-template-columns: 0.9fr 1.1fr; }
        .login-left  { padding: 40px; }
        .login-right { padding: 40px 36px; }
        .left-headline { font-size: 30px; }
        .form-header h2 { font-size: 26px; }
        .login-form-wrap { max-width: 350px; }
    }
    @media (min-width: 769px) and (max-width: 1024px) {
        .login-shell { grid-template-columns: 1fr; }
        .login-left  { display: none; }
        .login-right { padding: 36px 28px; min-height: 100vh; }
        .form-header h2 { font-size: 24px; }
        .login-form-wrap { max-width: 340px; }
    }
    @media (max-width: 768px) {
        .login-shell { grid-template-columns: 1fr; }
        .login-left  { display: none; }
        .login-right { padding: 28px 20px; min-height: 100vh; }
        .login-right::before { width: 100px; height: 100px; }
        .form-header { margin-bottom: 24px; }
        .form-header h2 { font-size: 22px; }
        .field-group  { margin-bottom: 16px; }
        .field-input  { padding: 9px 12px 9px 34px; font-size: 14px; border-radius: 8px; }
        .field-icon   { left: 10px; font-size: 16px; }
        .field-icon-right { right: 10px; }
        .btn-login    { padding: 10px; font-size: 15px; border-radius: 8px; min-height: 44px; }
        .field-input  { min-height: 44px; }
        .login-form-wrap { max-width: 100%; }
    }
    @media (max-width: 576px) {
        .login-right  { padding: 20px 16px; }
        .form-header  { margin-bottom: 20px; text-align: center; }
        .form-header-eyebrow { display: flex; justify-content: center; }
        .form-header h2 { font-size: 20px; margin-bottom: 4px; }
        .form-header p  { font-size: 13px; }
        .field-input  { font-size: 13px; padding: 8px 12px 8px 32px; }
        .btn-login    { padding: 10px; font-size: 14px; margin-top: 4px; }
        .field-group  { margin-bottom: 14px; }
        .form-footer  { font-size: 11px; margin-top: 20px; }
    }
    @media (max-width: 400px) {
        .login-right  { padding: 16px 12px; }
        .form-header  { margin-bottom: 16px; }
        .form-header h2 { font-size: 18px; }
        .field-group  { margin-bottom: 12px; }
        .field-label  { font-size: 12px; }
        .field-input  { font-size: 12px; padding: 7px 12px 7px 30px; }
        .btn-login    { padding: 9px; font-size: 13px; }
        .form-footer  { font-size: 10px; }
    }
    </style>
</head>
<body>

<div class="login-shell">

    {{-- ══════════════ LEFT PANEL ══════════════ --}}
    <div class="login-left">
        <div class="left-grid"></div>

        <div class="left-top">
            <div class="left-logo">
                <div class="left-logo-badge">
                    <img src="{{ asset('images/logo_pln.png') }}" alt="Logo PLN">
                </div>
                <div class="left-logo-text">
                    <div class="left-logo-name">PLN Smart Collection</div>
                    <div class="left-logo-sub">Monitoring &amp; Decision Support System</div>
                </div>
            </div>

            <div class="left-headline">
                Analisis Kinerja<br>
                <span>Petugas</span> &amp;<br>
                Prioritas Penagihan Pelanggan
            </div>
            <p class="left-desc">
                Platform terintegrasi untuk monitoring kinerja petugas,
                analisis prioritas pelanggan, dan pengelolaan
                penagihan PLN berbasis metode SAW.
            </p>

            <div class="left-features">
                <div class="feature-chip">
                    <div class="feature-chip-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    Monitoring kinerja petugas penagihan
                </div>
                <div class="feature-chip">
                    <div class="feature-chip-icon"><i class="bi bi-shield-check"></i></div>
                    Akses berbasis peran &amp; hak akses
                </div>
                <div class="feature-chip">
                    <div class="feature-chip-icon"><i class="bi bi-bar-chart-steps"></i></div>
                    Ranking SAW multi-kriteria
                </div>
                <div class="feature-chip">
                    <div class="feature-chip-icon"><i class="bi bi-receipt"></i></div>
                    Cetak invoice &amp; laporan
                </div>
            </div>
        </div>

        <div class="left-bottom">
            &copy; {{ date('Y') }} PLN ULP RIVAI &mdash; Hak cipta dilindungi
        </div>
    </div>

    {{-- ══════════════ RIGHT PANEL ══════════════ --}}
    <div class="login-right">
        <div class="login-form-wrap">

            <div class="form-header">
                <div class="form-header-eyebrow">
                    <i class="bi bi-lock"></i>
                    Autentikasi
                </div>
                <h2>Selamat Datang</h2>
                <p>Masuk dengan akun yang telah diberikan oleh administrator sistem.</p>
            </div>

            {{-- ── Alert error dari session ── --}}
            @if(session('error'))
            <div class="login-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            {{-- Form untuk proses login --}}
            <form method="POST" action="/login" id="login-form" autocomplete="off">
                @csrf

                {{-- ── USERNAME ── --}}
                <div class="field-group">
                    <label class="field-label" for="username">Username</label>
                    <div class="field-wrap">
                        <i class="bi bi-person field-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="field-input"
                            placeholder="Masukkan username"
                            value="{{ old('username') }}"
                            required
                            autocomplete="username"
                        >
                    </div>
                    @error('username')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ── PASSWORD ── --}}
                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <i class="bi bi-key field-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="field-input has-toggle"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="field-icon-right" id="toggle-pw" title="Tampilkan password">
                            <i class="bi bi-eye" id="pw-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ── SUBMIT ── --}}
                <button type="submit" class="btn-login" id="btn-login">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Masuk ke Sistem
                    </span>
                    <div class="spinner"></div>
                </button>

            </form>

            <div class="form-footer">
                Mengalami masalah login? Hubungi administrator sistem.<br>
                <strong style="color:#4A5A6A">PLN ULP RIVAI &mdash; PLN SMART COLLECTION {{ date('Y') }}</strong>
            </div>

        </div>
    </div>

</div>

<script>
// Fungsi untuk toggle visibilitas password
document.getElementById('toggle-pw').addEventListener('click', function () {
    const input = document.getElementById('password');
    const icon  = document.getElementById('pw-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
});

// Menampilkan animasi loading saat form disubmit
document.getElementById('login-form').addEventListener('submit', function () {
    document.getElementById('btn-login').classList.add('loading');
});
</script>

</body>
</html>
