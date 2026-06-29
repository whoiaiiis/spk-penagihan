<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\ImportLog;
use App\Services\PelangganImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PelangganController extends Controller
{
    // cek session dulu sebelum masuk, kalau belum login langsung tendang ke halaman login
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Session::has('user')) {
                return redirect('/login');
            }
            return $next($request);
        });
    }

    // halaman utama, tinggal lihat tab mana yang aktif terus arahkan ke method yang sesuai
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'data');

        if ($activeTab === 'data') {
            return $this->showMasterData($request);
        }

        if ($activeTab === 'import') {
            return $this->showImportTab();
        }

        if ($activeTab === 'errors') {
            return $this->showErrorTab($request);
        }

        // kalau tab-nya aneh, fallback ke data aja
        return $this->showMasterData($request);
    }

    // ambil data pelanggan dari DB, lengkap dengan filter & sorting
    private function showMasterData(Request $request)
    {
        // ambil semua parameter dari query string
        $search     = $request->query('search', '');
        $sortBy     = $request->query('sort', 'newest');
        $dayaMin    = $request->query('daya_min', '');
        $dayaMax    = $request->query('daya_max', '');
        $rptagMin   = $request->query('rptag_min', '');
        $rptagMax   = $request->query('rptag_max', '');
        $lembar     = $request->query('lembar', '');
        $petugasId  = $request->query('petugas_id', '');
        $periode    = $request->query('periode', '');
        $perPage    = $request->query('per_page', 25);

        $query = Pelanggan::with('petugas');

        // filter berdasarkan periode
        if (!empty($periode)) {
            $query->byPeriode($periode);
        }

        // filter pencarian nama/idpel
        if (!empty($search)) {
            $query->search($search);
        }

        // filter range daya
        if (!empty($dayaMin) || !empty($dayaMax)) {
            $query->byDayaRange($dayaMin ?: null, $dayaMax ?: null);
        }

        // filter range rptag
        if (!empty($rptagMin) || !empty($rptagMax)) {
            $query->byRptagRange($rptagMin ?: null, $rptagMax ?: null);
        }

        // filter jumlah lembar tagihan
        if (!empty($lembar)) {
            $query->byLembar($lembar);
        }

        // filter berdasarkan petugas yang handle
        if (!empty($petugasId)) {
            $query->where('petugas_id', $petugasId);
        }

        $query->orderByDynamic($sortBy);

        $pelanggan = $query->paginate($perPage);

        // ringkasan total untuk ditampilkan di bagian atas tabel
        $totalPelanggan = Pelanggan::count();
        $totalDaya      = Pelanggan::sum('daya') ?? 0;
        $totalRptag     = Pelanggan::sum('rptag') ?? 0;
        $lembars        = Pelanggan::distinct()->pluck('lembar')->sort();

        // import log sementara dikosongkan, belum dipakai di tab ini
        $importLogs  = collect();
        $petugasList = \App\Models\Petugas::all();

        return view('staff.datapelanggan.datapelanggan', [
            'activeTab'      => 'data',
            'pelanggan'      => $pelanggan,
            'search'         => $search,
            'sortBy'         => $sortBy,
            'dayaMin'        => $dayaMin,
            'dayaMax'        => $dayaMax,
            'rptagMin'       => $rptagMin,
            'rptagMax'       => $rptagMax,
            'lembar'         => $lembar,
            'periode'        => $periode,
            'perPage'        => $perPage,
            'totalPelanggan' => $totalPelanggan,
            'totalDaya'      => $totalDaya,
            'totalRptag'     => $totalRptag,
            'lembars'        => $lembars,
            'petugasList'    => $petugasList,
            'petugasId'      => $petugasId,
            'importLogs'     => $importLogs,
            'preview'        => [],
            'errorList'      => [],
            'stats'          => [],
        ]);
    }

    // tab import — kalau ada preview/error dari session langsung ditampilkan
    private function showImportTab()
    {
        $petugasList = \App\Models\Petugas::all();

        return view('staff.datapelanggan.datapelanggan', [
            'activeTab' => 'import',

            // ambil data preview dan error dari session kalau ada
            'preview'   => Session::get('import_preview', []),
            'errorList' => Session::get('import_errors', []),

            // stats default kalau session kosong
            'stats' => Session::get('import_stats', [
                'total'      => 0,
                'valid'      => 0,
                'error'      => 0,
                'error_rows' => 0,
            ]),

            'fatal' => Session::get('import_fatal', null),

            // sisanya dikosongkan, tidak dipakai di tab ini
            'pelanggan'      => [],
            'search'         => '',
            'sortBy'         => 'newest',
            'dayaMin'        => '',
            'dayaMax'        => '',
            'rptagMin'       => '',
            'rptagMax'       => '',
            'lembar'         => '',
            'periode'        => Session::get('import_periode', date('Y-m')),
            'perPage'        => 25,
            'totalPelanggan' => 0,
            'totalDaya'      => 0,
            'totalRptag'     => 0,
            'lembars'        => [],
            'importLogs'     => [],
            'petugasList'    => $petugasList,
            'petugasId'      => '',
        ]);
    }

    // tab riwayat error import — di-paginate karena bisa banyak
    private function showErrorTab(Request $request)
    {
        $page    = $request->query('page', 1);
        $perPage = 20;

        // join ke import_logs supaya bisa tampil nama file & tanggalnya
        $errorLogs = DB::table('import_errors')
            ->join('import_logs', 'import_errors.import_log_id', '=', 'import_logs.id')
            ->select(
                'import_errors.id',
                'import_errors.row_number',
                'import_errors.row_data',
                'import_errors.error_message',
                'import_errors.error_type',
                'import_logs.filename',
                'import_logs.import_date',
                'import_errors.created_at'
            )
            ->orderBy('import_errors.created_at', 'desc')
            ->paginate($perPage, ['*'], 'error_page', $page);

        $petugasList = \App\Models\Petugas::all();

        return view('staff.datapelanggan.datapelanggan', [
            'activeTab'      => 'errors',
            'errorLogs'      => $errorLogs,
            'preview'        => [],
            'errorList'      => [],
            'stats'          => [],
            'pelanggan'      => [],
            'search'         => '',
            'sortBy'         => 'newest',
            'dayaMin'        => '',
            'dayaMax'        => '',
            'rptagMin'       => '',
            'rptagMax'       => '',
            'lembar'         => '',
            'periode'        => '',
            'perPage'        => 25,
            'totalPelanggan' => 0,
            'totalDaya'      => 0,
            'totalRptag'     => 0,
            'lembars'        => [],
            'importLogs'     => [],
            'petugasList'    => $petugasList,
            'petugasId'      => '',
        ]);
    }

    // handle upload CSV — validasi dulu, baru proses lewat service
    public function upload(Request $request, PelangganImportService $service)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            'periode'  => 'required|date_format:Y-m',
        ], [
            'csv_file.required' => 'Pilih file terlebih dahulu.',
            'csv_file.mimes'    => 'File harus berformat CSV atau Excel.',
            'csv_file.max'      => 'Ukuran file maksimal 5 MB.',
            'periode.required'  => 'Periode wajib diisi.',
            'periode.date_format'=> 'Format periode tidak valid.',
        ]);

        try {
            $file = $request->file('csv_file');

            // pastikan file tidak corrupt waktu upload
            if (!$file->isValid()) {
                return back()
                    ->with('import_fatal', 'File upload gagal atau corrupt')
                    ->with('tab', 'import');
            }

            $path = $file->getRealPath();

            if (!file_exists($path)) {
                return back()
                    ->with('import_fatal', 'File tidak ditemukan di server')
                    ->with('tab', 'import');
            }

            // serahkan ke service untuk parsing & validasi baris per baris
            $result = $service->processFile($path, [
                'filename'    => $file->getClientOriginalName(),
                'imported_by' => auth()->id() ?? 'system',
                'import_date' => now(),
            ]);

            // kalau service baliknya kosong atau tidak ada preview, berarti ada yang salah
            if (!$result || !isset($result['preview'])) {
                return back()
                    ->with('import_fatal', 'Gagal membaca isi file CSV')
                    ->with('tab', 'import');
            }

            // simpan semua hasil parsing ke session untuk ditampilkan di preview
            Session::put('import_log_id',  $result['import_log_id'] ?? null);
            Session::put('import_preview', $result['preview'] ?? []);
            Session::put('import_valid',   $result['valid'] ?? []);
            Session::put('import_errors',  $result['errors'] ?? []);
            Session::put('import_stats',   $result['stats'] ?? [
                'total'      => 0,
                'valid'      => 0,
                'error'      => 0,
                'error_rows' => 0,
            ]);
            Session::put('import_fatal',    $result['fatal'] ?? null);
            Session::put('import_filename', $file->getClientOriginalName());
            Session::put('import_periode',  $request->periode);

            return redirect()->route('pelanggan.index', ['tab' => 'import'])
                ->with('upload_success', 'File berhasil diunggah. Periksa preview dan validasi data.');

        } catch (\Exception $e) {
            Log::error('Pelanggan upload error: ' . $e->getMessage());

            return back()
                ->with('import_fatal', 'Terjadi error saat membaca file: ' . $e->getMessage())
                ->with('tab', 'import');
        }
    }

    // simpan baris-baris yang valid ke DB (pakai upsert supaya tidak duplikat)
    public function save(Request $request, PelangganImportService $service)
    {
        $validRows = Session::get('import_valid', []);

        // kalau session kosong, tidak ada yang bisa disimpan
        if (empty($validRows)) {
            return redirect()->route('pelanggan.index', ['tab' => 'import'])
                ->with('save_error', 'Tidak ada data valid yang bisa disimpan.')
                ->with('active_tab', 'import');
        }

        try {
            $filename = Session::get('import_filename', 'imported_' . now()->format('YmdHis') . '.csv');
            $userId   = Session::get('user')->id ?? 'unknown';
            $periode  = Session::get('import_periode', date('Y-m'));

            $importLogId = Session::get('import_log_id');
            $result      = $service->saveToDatabase($validRows, $importLogId, $periode);

            // bersihkan session setelah berhasil simpan
            Session::forget([
                'import_log_id',
                'import_preview',
                'import_valid',
                'import_errors',
                'import_stats',
                'import_fatal',
                'import_filename',
                'import_periode',
            ]);

            return redirect()->route('pelanggan.index', ['tab' => 'data'])
                ->with('save_success', "{$result['inserted']} data pelanggan baru disimpan, " .
                    "{$result['updated']} data diupdate. Total: " . $result['total'])
                ->with('tab', 'data');

        } catch (\Exception $e) {
            Log::error('Pelanggan save error: ' . $e->getMessage());

            return redirect()->route('pelanggan.index', ['tab' => 'import'])
                ->with('save_error', 'Gagal simpan: ' . $e->getMessage())
                ->with('tab', 'import');
        }
    }

    // reset preview — hapus semua session import dan kembali ke tab import
    public function reset()
    {
        Session::forget([
            'import_preview',
            'import_valid',
            'import_errors',
            'import_stats',
            'import_fatal',
            'import_filename',
            'import_periode',
        ]);

        return redirect()->route('pelanggan.index', ['tab' => 'import'])
            ->with('reset_success', 'Preview import direset.');
    }

    // ambil detail satu pelanggan, dipakai untuk modal/popup di frontend
    public function show($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $pelanggan,
        ]);
    }

    // sama seperti show, tapi endpoint khusus untuk form edit
    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $pelanggan,
        ]);
    }

    // update data pelanggan — field yang bisa diubah dibatasi pakai only()
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'   => 'required|string|max:255',
            'daya'   => 'required|numeric|min:0',
            'lembar' => 'required|numeric|min:0',
            'rptag'  => 'required|numeric|min:0',
            'rpbk'   => 'required|numeric|min:0',
        ]);

        try {
            $pelanggan = Pelanggan::findOrFail($id);
            $pelanggan->update($request->only(['nama', 'daya', 'lembar', 'rptag', 'rpbk']));

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil diupdate.',
            ]);

        } catch (\Exception $e) {
            Log::error('Pelanggan update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal update data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // hapus satu pelanggan berdasarkan ID
    public function destroy($id)
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);
            $nama      = $pelanggan->nama;
            $pelanggan->delete();

            return response()->json([
                'success' => true,
                'message' => "Pelanggan '{$nama}' berhasil dihapus.",
            ]);

        } catch (\Exception $e) {
            Log::error('Pelanggan delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // export data pelanggan ke CSV — langsung stream ke browser
    // Nama file mengikuti periode filter yang dipilih pengguna
    public function export(Request $request)
    {
        try {
            $periode = $request->get('periode');

            $query = Pelanggan::orderBy('created_at', 'desc');
            if ($periode) {
                $query->where('periode', $periode);
            }
            $pelanggan = $query->get();

            // Nama file dari periode filter (bukan tanggal sistem)
            $filename = 'Pelanggan_' . periode_ke_nama_file($periode) . '.csv';

            $handle = fopen('php://output', 'w');

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            // BOM UTF-8 agar Excel tidak rusak enkodingnya
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Baris info periode di awal file
            fputcsv($handle, ['Periode : ' . periode_ke_label($periode)], ';');
            fputcsv($handle, [], ';');

            // Baris header kolom
            fputcsv($handle, [
                'ID', 'IDPEL', 'NAMA', 'HISTORIS TUNGGAKAN',
                'DAYA', 'LEMBAR', 'RPTAG', 'RPBK', 'PERIODE', 'Dibuat Pada',
            ], ';');

            // Tulis data baris per baris
            foreach ($pelanggan as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->idpel,
                    $p->nama,
                    $p->historis_tunggakan,
                    $p->daya,
                    $p->lembar,
                    $p->rptag,
                    $p->rpbk,
                    $p->periode,
                    $p->created_at->format('Y-m-d H:i:s'),
                ], ';');
            }

            fclose($handle);
            exit;

        } catch (\Exception $e) {
            Log::error('Pelanggan export error: ' . $e->getMessage());

            return back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }
}