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
    /**
     * Middleware to check session
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Session::has('user')) {
                return redirect('/login');
            }
            return $next($request);
        });
    }
    /**
     * Tampilkan halaman master data pelanggan dengan tabs
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'data');

        // ────────────────────────────────────────────────────────────────
        // TAB: DATA PELANGGAN (Master data dari database)
        // ────────────────────────────────────────────────────────────────
        if ($activeTab === 'data') {
            return $this->showMasterData($request);
        }

        // ────────────────────────────────────────────────────────────────
        // TAB: IMPORT CSV (Upload & Preview)
        // ────────────────────────────────────────────────────────────────
        if ($activeTab === 'import') {
            return $this->showImportTab();
        }

        // ────────────────────────────────────────────────────────────────
        // TAB: ERROR IMPORT (Riwayat error)
        // ────────────────────────────────────────────────────────────────
        if ($activeTab === 'errors') {
            return $this->showErrorTab($request);
        }

        // Default ke data
        return $this->showMasterData($request);
    }

    /**
     * Tampilkan master data pelanggan dari database
     */
    private function showMasterData(Request $request)
    {
        $search = $request->query('search', '');
        $sortBy = $request->query('sort', 'newest');
        $dayaMin = $request->query('daya_min', '');
        $dayaMax = $request->query('daya_max', '');
        $rptagMin = $request->query('rptag_min', '');
        $rptagMax = $request->query('rptag_max', '');
        $lembar = $request->query('lembar', '');
        $perPage = $request->query('per_page', 25);

        // ────────────────────────────────────────────────────────────────
        // Build query
        // ────────────────────────────────────────────────────────────────
        $query = Pelanggan::query();

        // Search
        if (!empty($search)) {
            $query->search($search);
        }

        // Filter daya
        if (!empty($dayaMin) || !empty($dayaMax)) {
            $query->byDayaRange($dayaMin ?: null, $dayaMax ?: null);
        }

        // Filter rptag
        if (!empty($rptagMin) || !empty($rptagMax)) {
            $query->byRptagRange($rptagMin ?: null, $rptagMax ?: null);
        }

        // Filter lembar
        if (!empty($lembar)) {
            $query->byLembar($lembar);
        }

        // Sort
        $query->orderByDynamic($sortBy);

        // Pagination
        $pelanggan = $query->paginate($perPage);

        // ────────────────────────────────────────────────────────────────
        // Summary statistics
        // ────────────────────────────────────────────────────────────────
        $totalPelanggan = Pelanggan::count();
        $totalDaya = Pelanggan::sum('daya') ?? 0;
        $totalRptag = Pelanggan::sum('rptag') ?? 0;
        $lembars = Pelanggan::distinct()->pluck('lembar')->sort();

        // ────────────────────────────────────────────────────────────────
        // Import logs (last 5)
        // ────────────────────────────────────────────────────────────────
       $importLogs = collect();

        return view('staff.datapelanggan.datapelanggan', [
            'activeTab' => 'data',
            'pelanggan' => $pelanggan,
            'search' => $search,
            'sortBy' => $sortBy,
            'dayaMin' => $dayaMin,
            'dayaMax' => $dayaMax,
            'rptagMin' => $rptagMin,
            'rptagMax' => $rptagMax,
            'lembar' => $lembar,
            'perPage' => $perPage,
            'totalPelanggan' => $totalPelanggan,
            'totalDaya' => $totalDaya,
            'totalRptag' => $totalRptag,
            'lembars' => $lembars,
            'importLogs' => $importLogs,
            'preview' => [],
            'errorList' => [],
            'stats' => [],
        ]);
    }

    /**
     * Tampilkan tab import (upload & preview)
     */
    private function showImportTab()
    {
        return view('staff.datapelanggan.datapelanggan', [
            'activeTab' => 'import',
            'preview' => Session::get('import_preview', []),
            'errorList' => Session::get('import_errors', []),
            'stats' => Session::get('import_stats', [
                'total' => 0,
                'valid' => 0,
                'error' => 0,
                'error_rows' => 0,
            ]),
            'fatal' => Session::get('import_fatal', null),
            'pelanggan' => [],
            'search' => '',
            'sortBy' => 'newest',
            'dayaMin' => '',
            'dayaMax' => '',
            'rptagMin' => '',
            'rptagMax' => '',
            'lembar' => '',
            'perPage' => 25,
            'totalPelanggan' => 0,
            'totalDaya' => 0,
            'totalRptag' => 0,
            'lembars' => [],
            'importLogs' => [],
        ]);
    }

    /**
     * Tampilkan tab error import
     */
    private function showErrorTab(Request $request)
    {
        $page = $request->query('page', 1);
        $perPage = 20;

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

        return view('staff.datapelanggan.datapelanggan', [
            'activeTab' => 'errors',
            'errorLogs' => $errorLogs,
            'preview' => [],
            'errorList' => [],
            'stats' => [],
            'pelanggan' => [],
            'search' => '',
            'sortBy' => 'newest',
            'dayaMin' => '',
            'dayaMax' => '',
            'rptagMin' => '',
            'rptagMax' => '',
            'lembar' => '',
            'perPage' => 25,
            'totalPelanggan' => 0,
            'totalDaya' => 0,
            'totalRptag' => 0,
            'lembars' => [],
            'importLogs' => [],
        ]);
    }

    /**
     * Proses upload CSV
     */
    public function upload(Request $request, PelangganImportService $service)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'csv_file.required' => 'Pilih file CSV terlebih dahulu.',
            'csv_file.mimes' => 'File harus berformat CSV.',
            'csv_file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        try {
            $file = $request->file('csv_file');

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

            // ────────────────────────────────────────────────────────────
            // Proses file CSV
            // ────────────────────────────────────────────────────────────
            $result = $service->processFile($path);

            // Validasi result
            if (!$result || !isset($result['preview'])) {
                return back()
                    ->with('import_fatal', 'Gagal membaca isi file CSV')
                    ->with('tab', 'import');
            }

            // ────────────────────────────────────────────────────────────
            // Simpan ke session
            // ────────────────────────────────────────────────────────────
            Session::put('import_preview', $result['preview'] ?? []);
            Session::put('import_valid', $result['valid'] ?? []);
            Session::put('import_errors', $result['errors'] ?? []);
            Session::put('import_stats', $result['stats'] ?? [
                'total' => 0,
                'valid' => 0,
                'error' => 0,
                'error_rows' => 0,
            ]);
            Session::put('import_fatal', $result['fatal'] ?? null);
            Session::put('import_filename', $file->getClientOriginalName());

            return redirect()->route('pelanggan.index', ['tab' => 'import'])
                ->with('upload_success', 'File berhasil diunggah. Periksa preview dan validasi data.');
        } catch (\Exception $e) {
            Log::error('Pelanggan upload error: ' . $e->getMessage());

            return back()
                ->with('import_fatal', 'Terjadi error saat membaca file: ' . $e->getMessage())
                ->with('tab', 'import');
        }
    }

    /**
     * Simpan data valid ke database (UPSERT)
     */
    public function save(Request $request, PelangganImportService $service)
    {
        $validRows = Session::get('import_valid', []);

        if (empty($validRows)) {
            return redirect()->route('pelanggan.index', ['tab' => 'import'])
                ->with('save_error', 'Tidak ada data valid yang bisa disimpan.')
                ->with('active_tab', 'import');
        }

        try {
            $filename = Session::get('import_filename', 'imported_' . now()->format('YmdHis') . '.csv');
            $userId = Session::get('user')->id ?? 'unknown';

            // ────────────────────────────────────────────────────────────
            // Simpan ke database dengan UPSERT
            // ────────────────────────────────────────────────────────────
            $result = $service->saveToDatabase($validRows, [
                'filename' => $filename,
                'imported_by' => $userId,
                'import_date' => now(),
            ]);

            // ────────────────────────────────────────────────────────────
            // Hapus session preview
            // ────────────────────────────────────────────────────────────
            Session::forget([
                'import_preview',
                'import_valid',
                'import_errors',
                'import_stats',
                'import_fatal',
                'import_filename',
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

    /**
     * Reset import (hapus session preview)
     */
    public function reset()
    {
        Session::forget([
            'import_preview',
            'import_valid',
            'import_errors',
            'import_stats',
            'import_fatal',
            'import_filename',
        ]);

        return redirect()->route('pelanggan.index', ['tab' => 'import'])
            ->with('reset_success', 'Preview import direset.');
    }

    /**
     * Tampilkan detail pelanggan
     */
    public function show($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pelanggan,
        ]);
    }

    /**
     * Edit pelanggan (form)
     */
    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pelanggan,
        ]);
    }

    /**
     * Update pelanggan
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'daya' => 'required|numeric|min:0',
            'lembar' => 'required|numeric|min:0',
            'rptag' => 'required|numeric|min:0',
            'rpbk' => 'required|numeric|min:0',
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

    /**
     * Hapus pelanggan
     */
    public function destroy($id)
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);
            $nama = $pelanggan->nama;
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

    /**
     * Export data pelanggan ke CSV
     */
    public function export()
    {
        try {
            $pelanggan = Pelanggan::orderBy('created_at', 'desc')->get();

            $filename = 'pelanggan_' . now()->format('YmdHis') . '.csv';

            $handle = fopen('php://output', 'w');

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            // Header
            fputcsv($handle, [
                'ID',
                'IDPEL',
                'NAMA',
                'DAYA',
                'LEMBAR',
                'RPTAG',
                'RPBK',
                'Dibuat Pada',
            ], ';');

            // Data
            foreach ($pelanggan as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->idpel,
                    $p->nama,
                    $p->daya,
                    $p->lembar,
                    $p->rptag,
                    $p->rpbk,
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
