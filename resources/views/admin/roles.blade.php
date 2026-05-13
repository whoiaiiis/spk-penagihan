@extends('layouts.app')
@section('title', 'Manajemen Role & Hak Akses')

@push('styles')
<style>
    :root {
        --c-border:   #E2E8F0;
        --c-surface:  #F8FAFC;
        --c-group:    #F1F5F9;
        --c-primary:  #005baa;
        --c-primary-dk: #004080;
        --c-text:     #1E293B;
        --c-muted:    #64748B;
        --c-light:    #94A3B8;
        --radius:     12px;
        --radius-sm:  8px;
        --shadow:     0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
    }

    .page-wrap { max-width: 860px; padding: 24px 20px 48px; }

    .page-header { margin-bottom: 20px; }
    .page-header h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 2px; letter-spacing: -.02em; }
    .page-header p  { font-size: .875rem; color: var(--c-muted); margin: 0; }

    .table-card {
        background: #fff; border: 1px solid var(--c-border);
        border-radius: var(--radius); box-shadow: var(--shadow);
        overflow: hidden;
    }

    .perm-table {
        width: 100%; border-collapse: collapse; table-layout: fixed;
    }

    /* HEADER */
    .perm-table thead tr {
        background: var(--c-surface);
        border-bottom: 2px solid var(--c-border);
    }
    .perm-table thead th {
        padding: 12px 14px;
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em;
        color: var(--c-muted);
    }
    .perm-table thead th.col-menu { text-align: left; }
    .perm-table thead th.col-role { text-align: center; }

    .role-name-label {
        display: block; font-size: .75rem; font-weight: 700;
        color: var(--c-text); text-transform: uppercase;
        letter-spacing: .05em; margin-bottom: 5px;
    }
    .col-all-wrap {
        display: flex; align-items: center;
        justify-content: center; gap: 5px;
    }
    .col-all-wrap label {
        font-size: .7rem; color: var(--c-light);
        cursor: pointer; user-select: none;
    }

    /* GROUP ROW */
    .group-row td {
        background: var(--c-group);
        padding: 6px 16px;
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .08em;
        color: #475569;
        border-top: 1px solid var(--c-border);
        border-bottom: 1px solid var(--c-border);
    }

    /* PERMISSION ROW */
    .perm-row { border-bottom: 1px solid #F1F5F9; transition: background .1s; }
    .perm-row:hover { background: #F8FAFC; }
    .perm-row:last-child { border-bottom: none; }
    .perm-row td { padding: 10px 14px; font-size: .875rem; color: #334155; }
    .perm-row td.check-td { text-align: center; }

    input[type="checkbox"] {
        width: 16px; height: 16px;
        cursor: pointer; accent-color: var(--c-primary);
    }

    /* FOOTER */
    .table-footer {
        padding: 12px 18px;
        border-top: 1px solid var(--c-border);
        display: flex; align-items: center; justify-content: space-between;
        background: var(--c-surface);
    }
    .perm-counter { font-size: .8125rem; color: var(--c-muted); }

    .btn-save {
        background: var(--c-primary); color: #fff;
        border: none; border-radius: var(--radius-sm);
        padding: 8px 22px; font-size: .875rem; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
        transition: background .15s;
    }
    .btn-save:hover { background: var(--c-primary-dk); }
    .btn-save svg { width: 15px; height: 15px; }
</style>
@endpush

@section('content')
<div class="px-4 py-4">

    <div class="page-header">
        <h1>Manajemen Role &amp; Hak Akses</h1>
        <p>Centang permission yang boleh diakses oleh masing-masing role.</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small mb-3" role="alert">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small mb-3" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="/roles/save">
        @csrf

        <div class="table-card">
            <div class="table-responsive">
            <table class="perm-table">

                {{-- ── HEADER ── --}}
                <colgroup>
                    <col style="width: 240px">
                    @foreach($roles as $role)
                    <col>
                    @endforeach
                </colgroup>

                <thead>
                    <tr>
                        <th class="col-menu ps-4">Menu / Aksi</th>
                        @foreach($roles as $role)
                        <th class="col-role">
                            <span class="role-name-label">{{ $role->name }}</span>
                            <div class="col-all-wrap">
                                <input type="checkbox"
                                       id="ca_{{ $role->id }}"
                                       class="col-all"
                                       data-role="{{ $role->id }}">
                                <label for="ca_{{ $role->id }}">semua</label>
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>

                {{-- ── BODY ──
                     $permissions = flat collection dari DB::table('permissions')->get()
                     Satu grup "Master Data" karena tabel permissions belum ada kolom group.
                     Tambah grup baru cukup copy baris <tr class="group-row"> di sini.
                --}}
                <tbody>
                    <tr class="group-row">
                        <td colspan="{{ count($roles) + 1 }}">Hak Akses</td>
                    </tr>

                    @foreach($permissions as $perm)
                    <tr class="perm-row">
                        <td class="ps-4">
                            {{ ucfirst(str_replace('_', ' ', $perm->name)) }}
                        </td>

                        @foreach($roles as $role)
                        <td class="check-td">
                            <input type="checkbox"
                                   class="row-cb"
                                   name="permissions[{{ $role->id }}][]"
                                   value="{{ $perm->id }}"
                                   data-role="{{ $role->id }}"
                                   @if(isset($rolePermissions[$role->id]) && in_array($perm->id, $rolePermissions[$role->id])) checked @endif>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>

            </table>
            </div>

            {{-- ── FOOTER ── --}}
            <div class="table-footer">
                <span class="perm-counter" id="perm-counter">— permission dipilih</span>
                <button type="submit" class="btn-save">
                    <svg fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M17 21v-8H7v8M7 3v5h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function syncColMaster(roleId) {
    const all = document.querySelectorAll(`.row-cb[data-role="${roleId}"]`);
    const chk = document.querySelectorAll(`.row-cb[data-role="${roleId}"]:checked`);
    const master = document.querySelector(`.col-all[data-role="${roleId}"]`);
    if (!master) return;
    master.checked       = all.length > 0 && chk.length === all.length;
    master.indeterminate = chk.length > 0 && chk.length < all.length;
}

function updateCounter() {
    const n = document.querySelectorAll('.row-cb:checked').length;
    document.getElementById('perm-counter').textContent = n + ' permission dipilih';
    document.querySelectorAll('.col-all').forEach(m => syncColMaster(m.dataset.role));
}

document.querySelectorAll('.col-all').forEach(master => {
    syncColMaster(master.dataset.role);
    master.addEventListener('change', function () {
        document.querySelectorAll(`.row-cb[data-role="${this.dataset.role}"]`)
            .forEach(cb => cb.checked = this.checked);
        updateCounter();
    });
});

document.querySelectorAll('.row-cb').forEach(cb => {
    cb.addEventListener('change', updateCounter);
});

updateCounter();
</script>
@endpush