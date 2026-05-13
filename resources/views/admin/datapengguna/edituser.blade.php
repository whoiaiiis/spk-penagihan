@extends('layouts.app')

@section('title', 'Edit User')

@push('styles')
<style>
    :root{
        --c-primary:#005baa;
        --c-primary-dk:#004080;

        --c-success:#0F6E56;
        --c-success-dk:#085041;

        --c-border:#E2E8F0;
        --c-surface:#F8FAFC;

        --c-muted:#64748B;
        --c-text:#1E293B;

        --radius:16px;
        --radius-sm:10px;

        --shadow:
            0 1px 3px rgba(0,0,0,.06),
            0 10px 28px rgba(0,0,0,.06);
    }

    /* ── PAGE ───────────────────────────────── */
    .user-edit-page{
        width:100%;
    }

    .user-edit-container{
        width:100%;
        max-width:1200px;

        margin:auto;

        padding:24px;
    }

    /* ── CARD ───────────────────────────────── */
    .form-card{
        background:#fff;

        border:1px solid var(--c-border);
        border-radius:var(--radius);

        overflow:hidden;

        box-shadow:var(--shadow);
    }

    /* ── HEADER ─────────────────────────────── */
    .form-card-hdr{
        padding:24px 28px;

        border-bottom:1px solid var(--c-border);

        display:flex;
        align-items:center;
        gap:16px;

        background:
            linear-gradient(
                to right,
                rgba(15,110,86,.05),
                rgba(15,110,86,0)
            );
    }

    .form-icon{
        width:52px;
        height:52px;

        border-radius:14px;

        background:#EAF3DE;
        color:#0F6E56;

        display:flex;
        align-items:center;
        justify-content:center;

        flex-shrink:0;
    }

    .form-icon svg{
        width:22px;
        height:22px;
    }

    .form-card-hdr h2{
        font-size:1.2rem;
        font-weight:700;

        margin:0 0 2px;

        color:var(--c-text);
    }

    .form-card-hdr p{
        margin:0;

        color:var(--c-muted);

        font-size:.875rem;
    }

    /* ── BODY ───────────────────────────────── */
    .form-body{
        padding:28px;
    }

    /* ── FORM GROUP ─────────────────────────── */
    .fg{
        margin-bottom:18px;
    }

    .fg label{
        display:block;

        margin-bottom:7px;

        font-size:.75rem;
        font-weight:700;

        text-transform:uppercase;
        letter-spacing:.05em;

        color:var(--c-muted);
    }

    .fg input,
    .fg select{
        width:100%;
        height:46px;

        padding:0 14px;

        border:1px solid var(--c-border);
        border-radius:var(--radius-sm);

        background:#F8FAFC;

        color:var(--c-text);
        font-size:.92rem;

        outline:none;

        transition:.15s;
    }

    .fg input:focus,
    .fg select:focus{
        border-color:var(--c-primary);

        background:#fff;

        box-shadow:
            0 0 0 3px rgba(0,91,170,.08);
    }

    .fg-hint{
        margin-top:6px;

        font-size:.78rem;

        color:#94A3B8;
    }

    /* ── GRID ───────────────────────────────── */
    .fg-row{
        display:grid;
        grid-template-columns:1fr 1fr;

        gap:18px;
    }

    /* ── PASSWORD ───────────────────────────── */
    .pwd-wrap{
        position:relative;
    }

    .pwd-wrap input{
        padding-right:46px;
    }

    .pwd-toggle{
        position:absolute;

        right:14px;
        top:50%;

        transform:translateY(-50%);

        border:none;
        background:none;

        cursor:pointer;

        color:var(--c-muted);

        display:flex;
        align-items:center;
        justify-content:center;
    }

    .pwd-toggle svg{
        width:18px;
        height:18px;
    }

    /* ── ALERT ──────────────────────────────── */
    .alert-custom{
        padding:14px 16px;

        border-radius:12px;

        background:#FEF2F2;
        border:1px solid #FECACA;

        color:#991B1B;

        margin-bottom:20px;

        font-size:.875rem;
    }

    .alert-custom div+div{
        margin-top:4px;
    }

    /* ── FOOTER ─────────────────────────────── */
    .form-footer{
        padding:18px 28px;

        border-top:1px solid var(--c-border);

        background:var(--c-surface);

        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:14px;

        flex-wrap:wrap;
    }

    /* ── BUTTON ─────────────────────────────── */
    .btn-back,
    .btn-update{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;

        height:44px;

        padding:0 18px;

        border-radius:var(--radius-sm);

        font-size:.9rem;
        font-weight:600;

        text-decoration:none;

        transition:.15s;
    }

    .btn-back{
        background:#fff;

        border:1px solid var(--c-border);

        color:var(--c-muted);
    }

    .btn-back:hover{
        background:var(--c-surface);

        color:var(--c-text);
    }

    .btn-update{
        border:none;

        background:var(--c-success);

        color:#fff;

        cursor:pointer;
    }

    .btn-update:hover{
        background:var(--c-success-dk);
    }

    .btn-back svg,
    .btn-update svg{
        width:16px;
        height:16px;
    }

    /* ── RESPONSIVE ─────────────────────────── */
    @media(max-width:768px){

        .user-edit-container{
            padding:16px;
        }

        .form-card-hdr{
            padding:20px;
        }

        .form-body{
            padding:20px;
        }

        .form-footer{
            padding:20px;

            flex-direction:column;
            align-items:stretch;
        }

        .btn-back,
        .btn-update{
            width:100%;
        }

        .fg-row{
            grid-template-columns:1fr;
            gap:0;
        }
    }

    @media(max-width:480px){

        .form-card-hdr{
            flex-direction:column;
            align-items:flex-start;
        }

        .form-icon{
            width:46px;
            height:46px;
        }

        .form-icon svg{
            width:20px;
            height:20px;
        }

        .form-card-hdr h2{
            font-size:1.05rem;
        }

        .fg input,
        .fg select{
            height:44px;
        }
    }
</style>
@endpush

@section('content')

<div class="user-edit-page">

    <div class="user-edit-container">

        <div class="form-card">

            <div class="form-card-hdr">

                <div class="form-icon">
                    <svg fill="none" viewBox="0 0 24 24">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"/>

                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </div>

                <div>
                    <h2>Edit User</h2>
                    <p>
                        Ubah data pengguna:
                        <strong>{{ $user->name }}</strong>
                    </p>
                </div>

            </div>

            <form method="POST"
                  action="/datapengguna/update/{{ $user->id }}">

                @csrf

                <div class="form-body">

                    @if($errors->any())
                    <div class="alert-custom">
                        @foreach($errors->all() as $e)
                            <div>{{ $e }}</div>
                        @endforeach
                    </div>
                    @endif

                    <div class="fg">
                        <label>Nama Lengkap</label>

                        <input type="text"
                               name="name"
                               value="{{ old('name', $user->name) }}"
                               required>
                    </div>

                    <div class="fg">
                        <label>Email</label>

                        <input type="email"
                               name="email"
                               value="{{ old('email', $user->email) }}"
                               required>
                    </div>

                    <div class="fg">

                        <label>Password Baru</label>

                        <div class="pwd-wrap">

                            <input type="password"
                                   name="password"
                                   id="pwd-edit"
                                   placeholder="Kosongkan jika tidak diubah">

                            <button type="button"
                                    class="pwd-toggle"
                                    onclick="togglePwd('pwd-edit')">

                                <svg fill="none" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                          stroke="currentColor"
                                          stroke-width="1.8"/>

                                    <circle cx="12"
                                            cy="12"
                                            r="3"
                                            stroke="currentColor"
                                            stroke-width="1.8"/>
                                </svg>

                            </button>

                        </div>

                        <div class="fg-hint">
                            Biarkan kosong jika tidak ingin mengganti password
                        </div>

                    </div>

                    <div class="fg-row">

                        <div class="fg">

                            <label>Role</label>

                            <select name="role">

                                <option value="admin"
                                    @selected(old('role',$user->role)=='admin')>
                                    Admin
                                </option>

                                <option value="staff"
                                    @selected(old('role',$user->role)=='staff')>
                                    Staff
                                </option>

                                <option value="supervisor"
                                    @selected(old('role',$user->role)=='supervisor')>
                                    Supervisor
                                </option>

                            </select>

                        </div>

                        <div class="fg">

                            <label>Status</label>

                            <select name="status">

                                <option value="aktif"
                                    @selected(old('status',$user->status)=='aktif')>
                                    Aktif
                                </option>

                                <option value="nonaktif"
                                    @selected(old('status',$user->status)=='nonaktif')>
                                    Nonaktif
                                </option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="form-footer">

                    <a href="/datapengguna"
                       class="btn-back">

                        <svg fill="none" viewBox="0 0 24 24">
                            <path d="M19 12H5M12 19l-7-7 7-7"
                                  stroke="currentColor"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>

                        Kembali
                    </a>

                    <button type="submit"
                            class="btn-update">

                        <svg fill="none" viewBox="0 0 24 24">
                            <path d="M20 6L9 17l-5-5"
                                  stroke="currentColor"
                                  stroke-width="2.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>

                        Update
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    function togglePwd(id)
    {
        const input = document.getElementById(id);

        input.type =
            input.type === 'password'
            ? 'text'
            : 'password';
    }
</script>
@endpush