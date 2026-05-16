<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Services\PelangganImportService;
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
Route::get('/login', function () {

    if (Session::has('user')) {
        return redirect('/dashboard');
    }

    return view('login');
});

/* ================= PROSES LOGIN ================= */
Route::post('/login', function (Request $request) {

    $request->validate([
        'email'    => 'required',
        'password' => 'required'
    ]);

    $user = DB::table('users')->where('email', $request->email)->first();

    if ($user && Hash::check($request->password, $user->password)) {

        if ($user->status != 'aktif') {
            return back()->with('error', 'Akun tidak aktif');
        }

        $roleData = DB::table('roles')->where('name', $user->role)->first();

        $permissions = DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleData->id)
            ->pluck('permissions.name')
            ->toArray();

        Session::put('user', $user);
        Session::put('role', $user->role);
        Session::put('permissions', $permissions);

        return redirect('/dashboard');
    }

    return back()->with('error', 'Email atau password salah');
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
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

// SUPERVISOR DASHBOARD
Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])->name('supervisor.dashboard');

// STAFF DASHBOARD
Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');

// ROLES
Route::get('/roles', function () {

    if (!Session::has('user') || Session::get('role') != 'admin') {
        return redirect('/dashboard');
    }

    $roles       = DB::table('roles')->get();
    $permissions = DB::table('permissions')->get();

    $raw = DB::table('role_permissions')->get();

    $rolePermissions = [];
    foreach ($raw as $rp) {
        $rolePermissions[$rp->role_id][] = $rp->permission_id;
    }

    $role = Session::get('role');

    return view('admin.roles', compact('roles', 'permissions', 'rolePermissions', 'role'));
});

// SIMPAN PERMISSION
Route::post('/roles/save', function (Request $request) {

    DB::table('role_permissions')->truncate();

    if ($request->permissions) {
        foreach ($request->permissions as $role_id => $perms) {
            foreach ($perms as $perm_id) {
                DB::table('role_permissions')->insert([
                    'role_id'       => $role_id,
                    'permission_id' => $perm_id
                ]);
            }
        }
    }

    return back()->with('success', 'Berhasil disimpan');
});

/* ================= DATA PENGGUNA ================= */
Route::get('/datapengguna', function () {

    if (!Session::has('user') || Session::get('role') != 'admin') {
        return redirect('/dashboard');
    }

    $users = DB::table('users')->get();
    $role  = Session::get('role');

    return view('admin.datapengguna.datapengguna', compact('users', 'role'));
});

Route::get('/datapengguna/create', function () {

    if (!Session::has('user') || Session::get('role') != 'admin') {
        return redirect('/dashboard');
    }

    $role = Session::get('role');

    return view('admin.datapengguna.create', compact('role'));
});

Route::post('/datapengguna/store', function (Request $request) {

    if (!Session::has('user') || Session::get('role') != 'admin') {
        return redirect('/dashboard');
    }

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

Route::get('/datapengguna/edit/{id}', function ($id) {

    if (!Session::has('user') || Session::get('role') != 'admin') {
        return redirect('/dashboard');
    }

    $user = DB::table('users')->where('id', $id)->first();
    $role = Session::get('role');

    return view('admin.datapengguna.edituser', compact('user', 'role'));
});

Route::post('/datapengguna/update/{id}', function (Request $request, $id) {

    if (!Session::has('user') || Session::get('role') != 'admin') {
        return redirect('/dashboard');
    }

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

Route::post('/datapengguna/delete/{id}', function ($id) {

    if (!Session::has('user') || Session::get('role') != 'admin') {
        return redirect('/dashboard');
    }

    DB::table('users')->where('id', $id)->delete();

    return back()->with('success', 'User berhasil dihapus');
});

/* ================= DATA PETUGAS ================= */
Route::get('/datapetugas', function (Request $request) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    $query = DB::table('petugas');

    if (!empty($request->search)) {
        $query->where('nama', 'like', '%' . $request->search . '%');
    }

    if (!empty($request->wilayah)) {
        $query->where('wilayah', $request->wilayah);
    }

    if (!empty($request->status)) {
        $query->where('status', $request->status);
    }

    $petugas     = $query->paginate(5)->withQueryString();
    $wilayahList = DB::table('petugas')->select('wilayah')->distinct()->get();

    return view('admin.datapetugas.datapetugas', compact('petugas', 'wilayahList'));
});

Route::get('/datapetugas/create', function () {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    return view('admin.datapetugas.create');
});

Route::post('/datapetugas/store', function (Request $request) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    $request->validate([
        'kode_petugas' => 'required',
        'nama'         => 'required',
        'email'        => 'required|email',
        'wilayah'      => 'required',
        'status'       => 'required'
    ]);

    DB::table('petugas')->insert([
        'kode_petugas' => $request->kode_petugas,
        'nama'         => $request->nama,
        'email'        => $request->email,
        'wilayah'      => $request->wilayah,
        'status'       => $request->status
    ]);

    return redirect('/datapetugas')->with('success', 'Petugas berhasil ditambahkan');
});

Route::get('/datapetugas/edit/{id}', function ($id) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    $petugas = DB::table('petugas')->where('id', $id)->first();

    if (!$petugas) {
        return redirect('/datapetugas')->with('error', 'Data tidak ditemukan');
    }

    return view('admin.datapetugas.edit', compact('petugas'));
});

Route::post('/datapetugas/update/{id}', function (Request $request, $id) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    $request->validate([
        'kode_petugas' => 'required',
        'nama'         => 'required',
        'email'        => 'required|email',
        'wilayah'      => 'required',
        'status'       => 'required'
    ]);

    DB::table('petugas')->where('id', $id)->update([
        'kode_petugas' => $request->kode_petugas,
        'nama'         => $request->nama,
        'email'        => $request->email,
        'wilayah'      => $request->wilayah,
        'status'       => $request->status
    ]);

    return redirect('/datapetugas')->with('success', 'Petugas berhasil diupdate');
});

Route::post('/datapetugas/delete/{id}', function ($id) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    DB::table('petugas')->where('id', $id)->delete();

    return back()->with('success', 'Petugas berhasil dihapus');
});

/* ================= DATA KRITERIA ================= */
Route::get('/datakriteria', function () {

    $kriteria = DB::table('kriteria')->get();

    return view('admin.datakriteria.datakriteria', compact('kriteria'));
});

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

Route::post('/datakriteria/delete/{id}', function ($id) {

    DB::table('kriteria')->where('id', $id)->delete();

    return back()->with('success', 'Kriteria berhasil dihapus');
});

/* ================================================================
   DATA PELANGGAN — Import CSV (FINAL FIX)
   ================================================================ */

// 🔹 HALAMAN UTAMA
Route::get('/pelanggan', function () {

    if (!Session::has('user')) {
        return redirect('/login');
    }

   return view('staff.datapelanggan.datapelanggan', [
    'preview'   => session('import_preview', []),
    'errorList' => session('import_errors', []), 
    'stats'     => session('import_stats', [
        'total' => 0,
        'valid' => 0,
        'error_rows' => 0,
    ]),
    'fatal'     => session('import_fatal', null),
]);
});


// 🔹 UPLOAD CSV
Route::post('/datapelanggan/upload', function (Request $request, PelangganImportService $service) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    // VALIDASI
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt|max:5120',
    ], [
        'csv_file.required' => 'Pilih file CSV terlebih dahulu.',
        'csv_file.mimes'    => 'File harus berformat CSV.',
        'csv_file.max'      => 'Ukuran file maksimal 5 MB.',
    ]);

    try {

        $file = $request->file('csv_file');

        if (!$file->isValid()) {
            return back()->with('import_fatal', 'File upload gagal / corrupt');
        }

        $path = $file->getRealPath();

        if (!file_exists($path)) {
            return back()->with('import_fatal', 'File tidak ditemukan di server');
        }

        // 🔥 PROSES FILE
        $result = $service->processFile($path);

        // 🔥 VALIDASI RESULT
        if (!$result || !isset($result['preview'])) {
            return back()->with('import_fatal', 'Gagal membaca isi file CSV');
        }

        // 🔥 SIMPAN SESSION
        Session::put('import_preview', $result['preview'] ?? []);
        Session::put('import_valid',   $result['valid'] ?? []);
        Session::put('import_errors',  $result['errors'] ?? []);
        Session::put('import_stats',   $result['stats'] ?? [
            'total' => 0,
            'valid' => 0,
            'error' => 0,
            'error_rows' => 0
        ]);
        Session::put('import_fatal',   $result['fatal'] ?? null);

        return redirect('/pelanggan')
            ->with('upload_success', true)
            ->with('active_tab', empty($result['preview']) ? 'import' : 'preview');

    } catch (\Exception $e) {

        return back()->with('import_fatal', 'Terjadi error saat membaca file: ' . $e->getMessage());
    }

})->name('datapelanggan.upload');


// 🔹 SIMPAN KE DATABASE
Route::post('/datapelanggan/save', function (PelangganImportService $service) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    $validRows = Session::get('import_valid', []);

    if (empty($validRows)) {
        return redirect('/pelanggan')
            ->with('save_error', 'Tidak ada data valid yang bisa disimpan.')
            ->with('active_tab', 'preview');
    }

    try {

        $count = $service->saveToDatabase($validRows);

        // 🔥 HAPUS SESSION
        Session::forget([
            'import_preview',
            'import_valid',
            'import_errors',
            'import_stats',
            'import_fatal'
        ]);

        return redirect('/pelanggan')
            ->with('save_success', "{$count} data berhasil disimpan")
            ->with('active_tab', 'import');

    } catch (\Exception $e) {

        return redirect('/pelanggan')
            ->with('save_error', 'Gagal simpan: ' . $e->getMessage())
            ->with('active_tab', 'preview');
    }

})->name('datapelanggan.save');


// 🔹 RESET
Route::post('/datapelanggan/reset', function () {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    Session::forget([
        'import_preview',
        'import_valid',
        'import_errors',
        'import_stats',
        'import_fatal'
    ]);

    return redirect('/pelanggan')->with('active_tab', 'import');

})->name('datapelanggan.reset');


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
        ->name('penagihan.index');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD CSV
    |--------------------------------------------------------------------------
    */

    Route::post('/upload', [PenagihanController::class, 'upload'])
        ->name('penagihan.upload');

    /*
    |--------------------------------------------------------------------------
    | SIMPAN KE DATABASE
    |--------------------------------------------------------------------------
    */

    Route::post('/save', [PenagihanController::class, 'save'])
        ->name('penagihan.save');

    /*
    |--------------------------------------------------------------------------
    | RESET IMPORT
    |--------------------------------------------------------------------------
    */

    Route::post('/reset', [PenagihanController::class, 'reset'])
        ->name('penagihan.reset');
});

/* ================================================================
   PROSES PENILAIAN 
   ================================================================ */
Route::prefix('penilaian')->name('datapenilaian.')->group(function () {
    Route::get('/',          [PenilaianController::class, 'index'])         ->name('index');
    Route::post('/generate', [PenilaianController::class, 'generate'])      ->name('generate');
    Route::post('/kriteria', [PenilaianController::class, 'updateKriteria'])->name('kriteria');
});

/* ================================================================
   PROSES RANKING 
   ================================================================ */
Route::prefix('ranking')->name('hasilranking.')->group(function () {
    Route::get('/',          [RankingController::class, 'index'])   ->name('index');
    Route::post('/generate', [RankingController::class, 'generate'])->name('generate');
});

/* ================================================================
   MONITORING KINERJA — Supervisor
   URL: /monitoringkinerja
================================================================ */

Route::get('/monitoringkinerja', [MonitoringController::class, 'index'])
    ->name('monitoring.index');

Route::get('/monitoringkinerja/detail/{id}', [MonitoringController::class, 'detail'])
    ->name('monitoring.detail');
 

/* ================= INVOICE (SPK RANKING) ================= */

// Index - Daftar Invoice
Route::get('/invoices', [App\Http\Controllers\InvoiceController::class, 'index'])
    ->name('invoice.index');

// Generate - Buat Invoice dari Ranking
Route::post('/invoices/generate', [App\Http\Controllers\InvoiceController::class, 'generate'])
    ->name('invoice.generate');

// Print - Preview Cetak Invoice (dengan filter & bulk print)
Route::get('/invoices/print', [App\Http\Controllers\InvoiceController::class, 'print'])
    ->name('invoice.print');

// Export PDF - Export Invoice ke PDF
Route::get('/invoices/export-pdf', [App\Http\Controllers\InvoiceController::class, 'exportPdf'])
    ->name('invoice.exportPdf');

// Delete - Hapus Invoice
Route::delete('/invoices/{id}', [App\Http\Controllers\InvoiceController::class, 'destroy'])
    ->name('invoice.destroy');

/* ================= LOGOUT ================= */
Route::get('/logout', function () {
    Session::flush();
    return redirect('/login');
});