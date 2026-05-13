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

    /* Decorative circles */
    .login-left::before,
    .login-left::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        opacity: .06;
        background: #fff;
    }
    .login-left::before {
        width: 500px; height: 500px;
        right: -120px; top: -120px;
    }
    .login-left::after {
        width: 320px; height: 320px;
        left: -80px; bottom: -80px;
    }

    .left-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
        background-size: 40px 40px;
    }

    .left-top {
        position: relative;
        z-index: 2;
    }
    .left-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 60px;
    }
    .left-logo-badge {
        width: 42px; height: 42px;
        background: var(--yellow);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
        color: var(--blue-deeper);
        box-shadow: 0 4px 16px rgba(245,166,35,.5);
        flex-shrink: 0;
    }
    .left-logo-text {
        line-height: 1.2;
    }
    .left-logo-name {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
    }
    .left-logo-sub {
        font-size: 11px;
        color: rgba(255,255,255,.4);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .7px;
    }

    .left-headline {
        font-size: 32px;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
        letter-spacing: -.5px;
        margin-bottom: 16px;
    }
    .left-headline span {
        color: var(--yellow);
    }
    .left-desc {
        font-size: 14px;
        color: rgba(255,255,255,.5);
        line-height: 1.7;
        max-width: 320px;
    }

    /* Feature chips */
    .left-features {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 40px;
        position: relative;
        z-index: 2;
    }
    .feature-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 10px;
        font-size: 13px;
        color: rgba(255,255,255,.75);
        font-weight: 500;
        transition: background .15s;
    }
    .feature-chip:hover { background: rgba(255,255,255,.1); }
    .feature-chip-icon {
        width: 28px; height: 28px;
        background: rgba(245,166,35,.15);
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--yellow);
        font-size: 13px;
        flex-shrink: 0;
    }

    .left-bottom {
        position: relative;
        z-index: 2;
        font-size: 11px;
        color: rgba(255,255,255,.25);
        font-weight: 500;
    }

    /* ── RIGHT PANEL ── */
    .login-right {
        background: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 40px;
        position: relative;
    }

    /* Top-right corner accent */
    .login-right::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 120px; height: 120px;
        background: linear-gradient(135deg, var(--blue-light), transparent);
        border-radius: 0 0 0 100%;
    }

    .login-form-wrap {
        width: 100%;
        max-width: 380px;
    }

    .form-header {
        margin-bottom: 32px;
    }
    .form-header-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: var(--blue-light);
        color: var(--blue);
        border-radius: 99px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 14px;
    }
    .form-header h2 {
        font-size: 26px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -.4px;
        margin-bottom: 6px;
    }
    .form-header p {
        font-size: 13px;
        color: var(--muted);
        line-height: 1.6;
    }

    /* Alert */
    .login-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        background: #FEE8E8;
        border: 1px solid #F5AAAA;
        border-radius: 10px;
        font-size: 13px;
        color: #8B1A1A;
        margin-bottom: 20px;
        animation: shake .35s ease;
    }
    .login-alert i { margin-top: 1px; flex-shrink: 0; font-size: 15px; }
    @keyframes shake {
        0%,100% { transform: translateX(0); }
        20%      { transform: translateX(-5px); }
        60%      { transform: translateX(5px); }
    }

    /* Field group */
    .field-group {
        margin-bottom: 18px;
    }
    .field-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #4A5A6A;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .field-wrap {
        position: relative;
    }
    .field-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
        font-size: 15px;
        pointer-events: none;
        transition: color .15s;
    }
    .field-input {
        width: 100%;
        padding: 10px 12px 10px 38px;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-size: 13px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text);
        background: #FAFBFD;
        transition: border-color .15s, box-shadow .15s, background .15s;
        outline: none;
    }
    .field-input:focus {
        border-color: var(--blue);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(0,85,165,.1);
    }
    .field-input:focus + .field-icon-right,
    .field-wrap:focus-within .field-icon { color: var(--blue); }

    .field-input.has-toggle { padding-right: 44px; }
    .field-icon-right {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
        font-size: 15px;
        cursor: pointer;
        transition: color .15s;
        background: none;
        border: none;
        padding: 0;
        display: flex;
    }
    .field-icon-right:hover { color: var(--text); }

    /* Submit */
    .btn-login {
        width: 100%;
        padding: 12px;
        background: var(--blue);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        font-family: 'Plus Jakarta Sans', sans-serif;
        cursor: pointer;
        transition: background .15s, box-shadow .15s, transform .1s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 8px;
        position: relative;
        overflow: hidden;
    }
    .btn-login::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.1), transparent);
    }
    .btn-login:hover {
        background: var(--blue-dark);
        box-shadow: 0 6px 20px rgba(0,85,165,.35);
        transform: translateY(-1px);
    }
    .btn-login:active { transform: translateY(0); }
    .btn-login.loading { pointer-events: none; opacity: .8; }
    .btn-login .spinner {
        display: none;
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .6s linear infinite;
    }
    .btn-login.loading .spinner { display: block; }
    .btn-login.loading .btn-text { display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Back link */
    .back-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 24px;
        font-size: 13px;
        color: var(--muted);
        text-decoration: none;
        font-weight: 500;
        transition: color .15s;
    }
    .back-link:hover { color: var(--blue); }
    .back-link i { font-size: 12px; }

    /* Footer */
    .form-footer {
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
        text-align: center;
        font-size: 11px;
        color: var(--muted);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .login-shell { grid-template-columns: 1fr; }
        .login-left  { display: none; }
        .login-right { padding: 32px 24px; }
    }
    </style>
</head>
<body>

<div class="login-shell">

    <!-- ══════════════ LEFT PANEL ══════════════ -->
    <div class="login-left">
        <div class="left-grid"></div>

        <div class="left-top">
            <div class="left-logo">
                <div class="left-logo-badge">PLN</div>
                <div class="left-logo-text">
                    <div class="left-logo-name">SPK Penagihan</div>
                    <div class="left-logo-sub">Sistem Pendukung Keputusan</div>
                </div>
            </div>

            <div class="left-headline">
                Kelola Penagihan<br>
                Lebih <span>Cerdas</span> &<br>
                Terukur
            </div>
            <p class="left-desc">
                Platform terintegrasi untuk manajemen penagihan pelanggan PLN
                berbasis metode SAW (Simple Additive Weighting).
            </p>

            <div class="left-features">
                <div class="feature-chip">
                    <div class="feature-chip-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    Analisis prioritas pelanggan otomatis
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
            &copy; {{ date('Y') }} PLN UP3 &mdash; Hak cipta dilindungi
        </div>
    </div>

    <!-- ══════════════ RIGHT PANEL ══════════════ -->
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

            @if(session('error'))
                <div class="login-alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="/login" id="login-form" autocomplete="off">
                @csrf

                <!-- Email -->
                <div class="field-group">
                    <label class="field-label" for="email">Email</label>
                    <div class="field-wrap">
                        <i class="bi bi-envelope field-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="field-input"
                            placeholder="nama@pln.co.id"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                        >
                    </div>
                    @error('email')
                        <span style="font-size:12px;color:#C0392B;margin-top:4px;display:block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
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
                        <span style="font-size:12px;color:#C0392B;margin-top:4px;display:block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login" id="btn-login">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Masuk ke Sistem
                    </span>
                    <div class="spinner"></div>
                </button>

            </form>

            <a href="/landing" class="back-link">
                <i class="bi bi-arrow-left"></i>
                Kembali ke halaman utama
            </a>

            <div class="form-footer">
                Mengalami masalah login? Hubungi administrator sistem.<br>
                <strong style="color:#4A5A6A">PLN UP3 &mdash; Sistem Penagihan {{ date('Y') }}</strong>
            </div>

        </div>
    </div>

</div>

<script>
// Toggle password visibility
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

// Loading state on submit
document.getElementById('login-form').addEventListener('submit', function () {
    document.getElementById('btn-login').classList.add('loading');
});
</script>

</body>
</html>