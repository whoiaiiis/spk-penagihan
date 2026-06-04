<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Services\PelangganImportService;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\PenagihanController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\SupervisorDashboardController;

/* ================= ROOT ================= */
Route::get('/', function () {
    return redirect('/login');
});

/* ================= LOGIN ================= */
/* ================= LOGIN ================= */

Route::get('/login', function () {

    if (Session::has('user')) {
        return redirect('/dashboard');
    }

    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {

    $request->validate([
        'username' => 'required',
        'password' => 'required'
    ]);

    $user = DB::table('users')
        ->where('username', $request->username)
        ->first();

    if ($user && Hash::check($request->password, $user->password)) {

        if ($user->status != 'aktif') {
            return back()->with('error', 'Akun tidak aktif');
        }

        Session::put('user', $user);
        Session::put('role', $user->role);

        // Ambil data permission berdasarkan role_id milik user
        $roleData = DB::table('roles')->where('id', $user->role_id)->first();
        $permissions = [];
        if ($roleData) {
            $permissions = DB::table('role_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role_id', $roleData->id)
                ->pluck('permissions.name')
                ->toArray();
        }
        Session::put('permissions', $permissions);

        return redirect('/dashboard');
    }

    return back()->with('error', 'Username atau password salah');
});

/* ================= DASHBOARD ================= */

    Route::get('/dashboard', function () {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    $role = Session::get('role');

    if ($role == 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($role == 'supervisor') {
        return redirect()->route('supervisor.dashboard');
    }

    if ($role == 'staff') {
        return redirect()->route('staff.dashboard');
    }

    return redirect('/login');
});

/* ================= MENU ADMIN ================= */

// ADMIN DASHBOARD
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->name('admin.dashboard')
    ->middleware('permission:dashboard:view');

// SUPERVISOR DASHBOARD
Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])
    ->name('supervisor.dashboard')
    ->middleware('permission:dashboard:view');

// STAFF DASHBOARD
Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])
    ->name('staff.dashboard')
    ->middleware('permission:dashboard:view');

// ROLES
Route::get('/roles', function () {

    $roles       = DB::table('roles')->get();
    $permissions = DB::table('permissions')->get();

    $raw = DB::table('role_permissions')->get();

    $rolePermissions = [];

    foreach ($raw as $rp) {
        $rolePermissions[$rp->role_id][] = $rp->permission_id;
    }

    $role = Session::get('role');

    return view('admin.roles', compact(
        'roles',
        'permissions',
        'rolePermissions',
        'role'
    ));

})->middleware('permission:roles.view');

// SIMPAN PERMISSION
Route::post('/roles/save', function (Request $request) {
    try {
        DB::beginTransaction();
        DB::table('role_permissions')->truncate();

        if ($request->permissions) {
            foreach ($request->permissions as $role_id => $perms) {
                foreach (array_unique($perms) as $perm_id) {
                    DB::table('role_permissions')->insert([
                        'role_id'       => $role_id,
                        'permission_id' => $perm_id
                    ]);
                }
            }
        }
        DB::commit();

        // Update current session permissions for immediate sidebar reflection
        $loggedInUser = Session::get('user');
        if ($loggedInUser) {
            $roleData = DB::table('roles')->where('id', $loggedInUser->role_id)->first();
            if ($roleData) {
                $newPermissions = DB::table('role_permissions')
                    ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                    ->where('role_permissions.role_id', $roleData->id)
                    ->pluck('permissions.name')
                    ->toArray();
                Session::put('permissions', $newPermissions);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Hak akses berhasil disimpan.'
            ]);
        }

        return back()->with('success', 'Berhasil disimpan');
    } catch (\Exception $e) {
        DB::rollBack();
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
        return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
})->middleware('permission:roles:edit');

/* ================= DATA PENGGUNA ================= */
Route::middleware('permission:users:view')->group(function () {
    Route::get('/datapengguna', function () {
        $users = DB::table('users')->get();
        $role  = Session::get('role');
        return view('admin.datapengguna.datapengguna', compact('users', 'role'));
    });
});

Route::middleware('permission:users:create')->group(function () {
    Route::get('/datapengguna/create', function () {
        $role = Session::get('role');
        return view('admin.datapengguna.create', compact('role'));
    });

    Route::post('/datapengguna/store', function (Request $request) {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required',
            'status'   => 'required'
        ]);

        DB::table('users')->insert([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'status'   => $request->status
        ]);

        return redirect('/datapengguna')->with('success', 'User berhasil ditambahkan');
    });
});

Route::middleware('permission:users:edit')->group(function () {
    Route::get('/datapengguna/edit/{id}', function ($id) {
        $user = DB::table('users')->where('id', $id)->first();
        $role = Session::get('role');
        return view('admin.datapengguna.edituser', compact('user', 'role'));
    });

    Route::post('/datapengguna/update/{id}', function (Request $request, $id) {
        $request->validate([
            'name'   => 'required',
            'email'  => 'required|email|unique:users,email,' . $id,
            'role'   => 'required',
            'status' => 'required'
        ]);

        $data = [
            'name'   => $request->name,
            'email'  => $request->email,
            'role'   => $request->role,
            'status' => $request->status
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($data);

        return redirect('/datapengguna')->with('success', 'User berhasil diupdate');
    });
});

Route::post('/datapengguna/delete/{id}', function ($id) {
    DB::table('users')->where('id', $id)->delete();
    return back()->with('success', 'User berhasil dihapus');
})->middleware('permission:users:delete');

/* ================= DATA PETUGAS ================= */
Route::middleware('permission:petugas:view')->group(function () {
    Route::get('/datapetugas', [App\Http\Controllers\PetugasController::class, 'index']);
});

Route::middleware('permission:petugas:create')->group(function () {
    Route::get('/datapetugas/create', [App\Http\Controllers\PetugasController::class, 'create']);
    Route::post('/datapetugas/store', [App\Http\Controllers\PetugasController::class, 'store']);
});

Route::middleware('permission:petugas:edit')->group(function () {
    Route::get('/datapetugas/edit/{id}', [App\Http\Controllers\PetugasController::class, 'edit']);
    Route::post('/datapetugas/update/{id}', [App\Http\Controllers\PetugasController::class, 'update']);
});

Route::post('/datapetugas/delete/{id}', [App\Http\Controllers\PetugasController::class, 'destroy'])
    ->middleware('permission:petugas:delete');

/* ================= DATA KRITERIA ================= */
Route::middleware('permission:kriteria:view')->group(function () {
    Route::get('/datakriteria', function () {
        $kriteria = DB::table('kriteria')->get();
        return view('admin.datakriteria.datakriteria', compact('kriteria'));
    });
});

Route::middleware('permission:kriteria:create')->group(function () {
    Route::get('/datakriteria/create', function () {
        return view('admin.datakriteria.create');
    });

    Route::post('/datakriteria/store', function (Request $request) {
        $request->validate([
            'nama'       => 'required',
            'tipe'       => 'required',
            'bobot'      => 'required|numeric',
            'keterangan' => 'required'
        ]);

        DB::table('kriteria')->insert([
            'nama'       => $request->nama,
            'tipe'       => $request->tipe,
            'bobot'      => $request->bobot,
            'keterangan' => $request->keterangan
        ]);

        return redirect('/datakriteria')->with('success', 'Kriteria berhasil ditambahkan');
    });
});

Route::middleware('permission:kriteria:edit')->group(function () {
    Route::get('/datakriteria/edit/{id}', function ($id) {
        $kriteria = DB::table('kriteria')->where('id', $id)->first();
        return view('admin.datakriteria.edit', compact('kriteria'));
    });

    Route::post('/datakriteria/update/{id}', function (Request $request, $id) {
        $request->validate([
            'nama'       => 'required',
            'tipe'       => 'required',
            'bobot'      => 'required|numeric',
            'keterangan' => 'required'
        ]);

        DB::table('kriteria')->where('id', $id)->update([
            'nama'       => $request->nama,
            'tipe'       => $request->tipe,
            'bobot'      => $request->bobot,
            'keterangan' => $request->keterangan
        ]);

        return redirect('/datakriteria')->with('success', 'Kriteria berhasil diupdate');
    });
});

Route::post('/datakriteria/delete/{id}', function ($id) {
    DB::table('kriteria')->where('id', $id)->delete();
    return back()->with('success', 'Kriteria berhasil dihapus');
})->middleware('permission:kriteria:delete');

/* ================================================================
   DATA PELANGGAN — Master Data & Import Management
   ================================================================ */

Route::prefix('pelanggan')->group(function () {

    // Halaman utama (master data, import, error logs)
    Route::get('/', [PelangganController::class, 'index'])->name('pelanggan.index')->middleware('permission:pelanggan:view');

    // Upload CSV
    Route::post('/upload', [PelangganController::class, 'upload'])->name('pelanggan.upload')->middleware('permission:pelanggan:create');

    // Simpan ke database (UPSERT)
    Route::post('/save', [PelangganController::class, 'save'])->name('pelanggan.save')->middleware('permission:pelanggan:create');

    // Reset preview import
    Route::post('/reset', [PelangganController::class, 'reset'])->name('pelanggan.reset')->middleware('permission:pelanggan:create');

    // Detail pelanggan (JSON)
    Route::get('/show/{id}', [PelangganController::class, 'show'])->name('pelanggan.show')->middleware('permission:pelanggan:view');

    // Edit pelanggan form (JSON)
    Route::get('/edit/{id}', [PelangganController::class, 'edit'])->name('pelanggan.edit')->middleware('permission:pelanggan:edit');

    // Update pelanggan
    Route::put('/update/{id}', [PelangganController::class, 'update'])->name('pelanggan.update')->middleware('permission:pelanggan:edit');

    // Hapus pelanggan
    Route::delete('/destroy/{id}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy')->middleware('permission:pelanggan:delete');

    // Export ke CSV
    Route::get('/export', [PelangganController::class, 'export'])->name('pelanggan.export')->middleware('permission:pelanggan:export');
});


/*
|--------------------------------------------------------------------------
| INPUT PENAGIHAN
|--------------------------------------------------------------------------
*/

Route::prefix('penagihan')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HALAMAN INPUT PENAGIHAN
    |--------------------------------------------------------------------------
    */

    Route::get('/', [PenagihanController::class, 'index'])
        ->name('penagihan.index')->middleware('permission:penagihan:view');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD CSV
    |--------------------------------------------------------------------------
    */

    Route::post('/upload', [PenagihanController::class, 'upload'])
        ->name('penagihan.upload')->middleware('permission:penagihan:create');

    /*
    |--------------------------------------------------------------------------
    | SIMPAN KE DATABASE
    |--------------------------------------------------------------------------
    */

    Route::post('/save', [PenagihanController::class, 'save'])
        ->name('penagihan.save')->middleware('permission:penagihan:create');

    /*
    |--------------------------------------------------------------------------
    | RESET IMPORT
    |--------------------------------------------------------------------------
    */

    Route::post('/reset', [PenagihanController::class, 'reset'])
        ->name('penagihan.reset')->middleware('permission:penagihan:create');
});

/* ================================================================
   PROSES PENILAIAN 
   ================================================================ */
Route::prefix('penilaian')->name('datapenilaian.')->group(function () {
    Route::get('/',          [PenilaianController::class, 'index'])         ->name('index')->middleware('permission:penilaian:view');
    Route::post('/generate', [PenilaianController::class, 'generate'])      ->name('generate')->middleware('permission:penilaian:create');
    Route::post('/kriteria', [PenilaianController::class, 'updateKriteria'])->name('kriteria')->middleware('permission:penilaian:edit');
});

/* ================================================================
   PROSES RANKING 
   ================================================================ */
Route::prefix('ranking')->name('hasilranking.')->group(function () {
    Route::get('/',          [RankingController::class, 'index'])   ->name('index')->middleware('permission:ranking:view');
    Route::post('/generate', [RankingController::class, 'generate'])->name('generate')->middleware('permission:ranking:create');
    Route::get('/export-csv', [RankingController::class, 'exportCsv'])->name('exportCsv')->middleware('permission:ranking:export');
    Route::get('/export-pdf', [RankingController::class, 'exportPdf'])->name('exportPdf')->middleware('permission:ranking:export');
});

/* ================================================================
   MONITORING KINERJA — Supervisor
   URL: /monitoringkinerja
================================================================ */

Route::get('/monitoringkinerja', [MonitoringController::class, 'index'])
    ->name('monitoring.index')->middleware('permission:monitoring:view');

Route::get('/monitoringkinerja/export-pdf', [MonitoringController::class, 'exportPdf'])
    ->name('monitoring.exportPdf')->middleware('permission:monitoring:export');

Route::get('/monitoringkinerja/detail/{id}', [MonitoringController::class, 'detail'])
    ->name('monitoring.detail')->middleware('permission:monitoring:view');
 

/* ================= INVOICE (SPK RANKING) ================= */

// Index - Daftar Invoice
Route::get('/invoices', [App\Http\Controllers\InvoiceController::class, 'index'])
    ->name('invoice.index')->middleware('permission:invoice:view');

// Generate - Buat Invoice dari Ranking
Route::post('/invoices/generate', [App\Http\Controllers\InvoiceController::class, 'generate'])
    ->name('invoice.generate')->middleware('permission:invoice:create');

// Print - Preview Cetak Invoice (dengan filter & bulk print)
Route::get('/invoices/print', [App\Http\Controllers\InvoiceController::class, 'print'])
    ->name('invoice.print')->middleware('permission:invoice:view');

// Export PDF - Export Invoice ke PDF
Route::get('/invoices/export-pdf', [App\Http\Controllers\InvoiceController::class, 'exportPdf'])
    ->name('invoice.exportPdf')->middleware('permission:invoice:export');

// Delete - Hapus Invoice
Route::delete('/invoices/{id}', [App\Http\Controllers\InvoiceController::class, 'destroy'])
    ->name('invoice.destroy')->middleware('permission:invoice:delete');

/* ================= LOGOUT ================= */
Route::get('/logout', function () {
    Session::flush();
    return redirect('/login');
});