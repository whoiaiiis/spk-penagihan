<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Services\PelangganImportService;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\RankingController;

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

    return view('dashboard', compact('role'));
});

/* ================= MENU ADMIN ================= */

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

    // HALAMAN UTAMA
    Route::get('/', function () {
        return view('staff.inputpenagihan.inputpenagihan', [
            'preview'   => [],
            'errorList' => [],
            'stats'     => [
                'total' => 0,
                'error_rows' => 0,
            ],
            'fatal' => null,
        ]);
    })->name('inputpenagihan.penagihan');


    // UPLOAD FILE
    Route::post('/upload', function (\Illuminate\Http\Request $request) {

        $request->validate([
            'csv_file' => 'required|file|max:10240',
        ]);

        $preview = [
            [
                '_row' => 1,
                'idpel' => '12345',
                'nama' => 'Budi',
                'daya' => '900',
                'lembar' => '1',
                'rptag' => '100000',
                'rpbk'  => '3000',
                '_status' => 'valid',
                '_errors' => [],
            ],
            [
                '_row' => 2,
                'idpel' => '',
                'nama' => 'Ani',
                'daya' => '1300',
                'lembar' => '1',
                'rptag' => '120000',
                'rpbk'  => '3000',
                '_status' => 'error',
                '_errors' => ['IDPEL kosong'],
            ],
        ];

        $errorList = [
            'Baris 2 - IDPEL kosong',
        ];

        $stats = [
            'total' => count($preview),
            'error_rows' => 1,
        ];

        return view('staff.inputpenagihan.penagihan', compact('preview', 'errorList', 'stats'))
            ->with('active_tab', 'preview');

    })->name('penagihan.upload');


    // SIMPAN DATA
    Route::post('/save', function () {

        return redirect()
            ->route('penagihan.penagihan')
            ->with('save_success', 'Data berhasil disimpan!');

    })->name('penagihan.save');
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

Route::get('/monitoring', function (Request $request) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    $bulan   = $request->get('bulan', date('Y-m'));
    $wilayah = $request->get('wilayah', '');

    // ── 1. Daftar wilayah untuk filter dropdown ──────────────────
    $wilayahList = DB::table('petugas')
        ->select('wilayah')->distinct()->orderBy('wilayah')->get();

    // ── 2. Query kinerja per petugas ─────────────────────────────
    $query = DB::table('petugas')
        ->leftJoin('penagihan', function ($join) use ($bulan) {
            $join->on('penagihan.petugas_id', '=', 'petugas.id')
                 ->where('penagihan.bulan', $bulan);
        })
        ->leftJoin('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
        ->where('petugas.status', 'aktif')
        ->select(
            'petugas.id',
            'petugas.nama',
            'petugas.wilayah',
            'petugas.kode_petugas',
   DB::raw('COUNT(DISTINCT penagihan.idpel) as total_ditagih'),
DB::raw('SUM(CASE WHEN penagihan.status = "sudah" THEN 1 ELSE 0 END) as sudah_tagih'),
DB::raw('SUM(CASE WHEN penagihan.status = "belum" THEN 1 ELSE 0 END) as belum_tagih'),

/*
|--------------------------------------------------------------------------
| TOTAL NOMINAL TUNGGAKAN
|--------------------------------------------------------------------------
*/
DB::raw('COALESCE(SUM(pelanggan.rptag), 0) as total_rptag'),
DB::raw('COALESCE(SUM(pelanggan.rpbk),0) as total_rpbk'),

/*
|--------------------------------------------------------------------------
| TOTAL BERHASIL DITAGIH
|--------------------------------------------------------------------------
*/
DB::raw('COALESCE(SUM(
    CASE 
        WHEN penagihan.status = "sudah" 
        THEN penagihan.jumlah_bayar 
        ELSE 0 
    END
), 0) as total_tagih'),

/*
|--------------------------------------------------------------------------
| SISA TUNGGAKAN
|--------------------------------------------------------------------------
*/
DB::raw('
    COALESCE(SUM(pelanggan.rptag), 0)
    -
    COALESCE(SUM(
        CASE 
            WHEN penagihan.status = "sudah"
            THEN penagihan.jumlah_bayar
            ELSE 0
        END
    ), 0)
    as sisa_saldo
')
)
->groupBy(
    'petugas.id',
    'petugas.nama',
    'petugas.wilayah',
    'petugas.kode_petugas'
);

    if (!empty($wilayah)) {
        $query->where('petugas.wilayah', $wilayah);
    }

    $kinerja = $query->orderByDesc('sudah_tagih')->get();

    // ── 3. Hitung persentase & kategori ─────────────────────────
    $kinerja = $kinerja->map(function ($row) {
        $row->persen = $row->total_ditagih > 0
            ? round(($row->sudah_tagih / $row->total_ditagih) * 100, 1)
            : 0;

        $row->kategori = match(true) {
            $row->persen >= 80 => 'Baik',
            $row->persen >= 60 => 'Cukup',
            default            => 'Kurang',
        };

        return $row;
    });

   // ── 4. Summary keseluruhan ───────────────────────────────────
$summary = [

    'total_petugas'  => $kinerja->count(),

    'avg_kinerja' =>
        $kinerja->count() > 0
            ? round($kinerja->avg('persen'), 1)
            : 0,

    'total_target' =>
        $kinerja->sum('total_ditagih'),

    'total_sudah' =>
        $kinerja->sum('sudah_tagih'),

    'total_belum' =>
        $kinerja->sum('belum_tagih'),

    /*
    |--------------------------------------------------------------------------
    | TOTAL NOMINAL TAGIHAN
    |--------------------------------------------------------------------------
    */
    'total_rptag' =>
        $kinerja->sum('total_rptag'),

    /*
    |--------------------------------------------------------------------------
    | TOTAL RPBK
    |--------------------------------------------------------------------------
    */
    'total_rpbk' =>
        $kinerja->sum('total_rpbk'),

    /*
    |--------------------------------------------------------------------------
    | TOTAL BERHASIL DITAGIH
    |--------------------------------------------------------------------------
    */
    'total_tagih' =>
        $kinerja->sum('total_tagih'),

    /*
    |--------------------------------------------------------------------------
    | TOTAL SISA SALDO
    |--------------------------------------------------------------------------
    */
    'total_sisa' =>
        $kinerja->sum('sisa_saldo'),

    /*
    |--------------------------------------------------------------------------
    | KATEGORI PETUGAS
    |--------------------------------------------------------------------------
    */
    'petugas_baik' =>
        $kinerja->where('kategori', 'Baik')->count(),

    'petugas_cukup' =>
        $kinerja->where('kategori', 'Cukup')->count(),

    'petugas_kurang' =>
        $kinerja->where('kategori', 'Kurang')->count(),
];

    // ── 5. Data chart (untuk JS) ─────────────────────────────────
    $chartData = $kinerja->map(fn($r) => [
        'nama'    => $r->nama,
        'persen'  => $r->persen,
        'sudah'   => $r->sudah_tagih,
        'belum'   => $r->belum_tagih,
        'tagih'   => $r->total_tagih,
        'sisa'    => $r->sisa_saldo,
    ])->values()->all();

    return view('supervisor.monitoringkinerja', compact(
        'kinerja', 'summary', 'chartData',
        'bulan', 'wilayah', 'wilayahList'
    ));
});

// ── Detail drill-down per petugas ────────────────────────────────
Route::get('/monitoringkinerja/detail/{id}', function (Request $request, $id) {

    if (!Session::has('user')) {
        return redirect('/login');
    }

    $bulan   = $request->get('bulan', date('Y-m'));
    $petugas = DB::table('petugas')->where('id', $id)->first();

    if (!$petugas) {
        return redirect('/monitoringkinerja')->with('error', 'Data petugas tidak ditemukan.');
    }

    // Semua pelanggan beserta status penagihan petugas ini bulan ini
    $detail = DB::table('pelanggan')
        ->leftJoin('penagihan', function ($join) use ($id, $bulan) {
            $join->on('penagihan.idpel', '=', 'pelanggan.idpel')
                 ->where('penagihan.petugas_id', $id)
                 ->where('penagihan.bulan', $bulan);
        })
        ->select(
            'pelanggan.idpel',
            'pelanggan.nama',
            'pelanggan.daya',
            'pelanggan.lembar',
            'pelanggan.rptag',
'pelanggan.rpbk',
            DB::raw('COALESCE(penagihan.status, "belum") as status'),
            'penagihan.created_at as tgl_tagih',
           'penagihan.jumlah_bayar as jumlah_tagih',
            'penagihan.keterangan'
        )
        ->orderByRaw('COALESCE(penagihan.status, "belum") ASC') // belum duluan
        ->get();

    $stats = [
        'total'       => $detail->count(),
        'sudah'       => $detail->where('status', 'sudah')->count(),
        'belum'       => $detail->where('status', 'belum')->count(),
        'total_tagih' => $detail->sum('jumlah_tagih'),
       'sisa_saldo'  => $detail->sum('rptag') - $detail->sum('jumlah_tagih'),
        'persen'      => $detail->count() > 0
            ? round(($detail->where('status', 'sudah')->count() / $detail->count()) * 100, 1)
            : 0,
    ];

    return view('supervisor.detailkinerja', compact('petugas', 'detail', 'stats', 'bulan'));
});
 
/* ================================================================
   CETAK INVOICE
   /invoice          → halaman daftar + filter sebelum cetak
   /invoice/print    → halaman cetak (2 per A4, tanpa layout)
================================================================ */
 
// ── Halaman pilih filter sebelum cetak ──────────────────────────
Route::get('/invoice', function (Request $request) {
 
    if (!Session::has('user')) {
        return redirect('/login');
    }
 
    $bulan   = $request->get('bulan', date('Y-m'));
    $search  = $request->get('search', '');
 
    // Ambil daftar petugas untuk filter
    $petugasList = DB::table('petugas')
        ->where('status', 'aktif')
        ->orderBy('nama')
        ->get();
 
    $petugasId = $request->get('petugas_id', '');
 
    // Query data penagihan + pelanggan
    $query = DB::table('penagihan')
        ->join('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
        ->join('petugas',   'petugas.id',      '=', 'penagihan.petugas_id')
        ->select(
            'penagihan.id as tagih_id',
            'penagihan.status',
            'penagihan.jumlah_bayar',
            'penagihan.tanggal_tagih',
            'pelanggan.idpel',
            'pelanggan.nama',
            'pelanggan.daya',
            'pelanggan.lembar',
            'pelanggan.rptag',
            'pelanggan.rpbk',
            'pelanggan.alamat',
            'petugas.id as petugas_id',
            'petugas.nama as nama_petugas',
            'petugas.wilayah'
        );
 
    // Filter bulan dari kolom tanggal_tagih atau created_at
    if ($bulan) {
        $query->whereRaw("DATE_FORMAT(penagihan.tanggal_tagih, '%Y-%m') = ?", [$bulan]);
    }
 
    if ($petugasId) {
        $query->where('penagihan.petugas_id', $petugasId);
    }
 
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('pelanggan.nama', 'like', "%{$search}%")
              ->orWhere('pelanggan.idpel', 'like', "%{$search}%");
        });
    }
 
    $tagihan = $query->orderBy('petugas.nama')->orderBy('pelanggan.nama')->paginate(20)->withQueryString();
 
    return view('staff.cetakinvoice.cetakinvoice', compact(
        'tagihan', 'petugasList', 'bulan', 'petugasId', 'search'
    ));
});
 
// ── Halaman cetak — tanpa layout, 2 invoice per A4 ──────────────
Route::get('/invoice/print', function (Request $request) {
 
    if (!Session::has('user')) {
        return redirect('/login');
    }
 
    $bulan     = $request->get('bulan', date('Y-m'));
    $petugasId = $request->get('petugas_id', '');
    $ids       = $request->get('ids', ''); // comma-separated tagih_id
 
    $query = DB::table('penagihan')
        ->join('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
        ->join('petugas',   'petugas.id',      '=', 'penagihan.petugas_id')
        ->select(
            'penagihan.id as tagih_id',
            'penagihan.status',
            'penagihan.jumlah_bayar',
            'penagihan.tanggal_tagih',
            'pelanggan.idpel',
            'pelanggan.nama',
            'pelanggan.daya',
            'pelanggan.lembar',
            'pelanggan.rptag',
            'pelanggan.alamat',
            'petugas.nama as nama_petugas',
            'petugas.wilayah'
        );
 
    // Jika ada ids spesifik (cetak pilihan)
    if ($ids) {
        $idArray = array_filter(explode(',', $ids));
        $query->whereIn('penagihan.id', $idArray);
    } else {
        // Cetak semua hasil filter
        if ($bulan) {
            $query->whereRaw("DATE_FORMAT(penagihan.tanggal_tagih, '%Y-%m') = ?", [$bulan]);
        }
        if ($petugasId) {
            $query->where('penagihan.petugas_id', $petugasId);
        }
    }
 
    $tagihan = $query->orderBy('pelanggan.nama')->get();
 
    // Bulan untuk ditampilkan di invoice (format: MARET 2026)
    $bulanLabel = $bulan
        ? strtoupper(\Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y'))
        : strtoupper(now()->translatedFormat('F Y'));
 
    // Nomor urut tagihan untuk No TAG
    $tagihan = $tagihan->map(function ($item, $idx) use ($bulan) {
        $item->no_tag = 'PAAANAG' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT)
            . '/01/AGA.01.01/'
            . now()->format('dmY');
        return $item;
    });
 
    return view('staff.cetakinvoice.print', compact('tagihan', 'bulanLabel', 'bulan'));
});

/* ================= LOGOUT ================= */
Route::get('/logout', function () {
    Session::flush();
    return redirect('/login');
});