<?php

namespace App\Http\Controllers;

use App\Services\PenagihanImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class PenagihanController extends Controller
{
    /**
     * Tampilkan halaman input penagihan
     */
    public function index()
    {
        $preview = Session::get('import_penagihan_preview', []);
        $errorList = Session::get('import_penagihan_errors', []);
        $stats   = Session::get('import_penagihan_stats', [
            'total' => 0,
            'valid' => 0,
            'error' => 0,
            'error_rows' => 0
        ]);
        $fatal   = Session::get('import_penagihan_fatal', null);

        $activeTab = Session::get('active_tab', 'import');

        return view('staff.inputpenagihan.inputpenagihan', compact('preview', 'errorList', 'stats', 'fatal', 'activeTab'));
    }

    /**
     * Proses upload CSV dan tampilkan preview
     */
    public function upload(Request $request, PenagihanImportService $service)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csv_file.required' => 'Pilih file CSV terlebih dahulu',
            'csv_file.mimes' => 'File harus berekstensi .csv',
            'csv_file.max' => 'Ukuran file maksimal 10MB',
        ]);

        try {
            $filePath = $request->file('csv_file')->getRealPath();
            $result   = $service->processFile($filePath);

            Session::put('import_penagihan_preview', $result['preview']);
            Session::put('import_penagihan_valid', $result['valid']);
            Session::put('import_penagihan_errors', $result['errors']);
            Session::put('import_penagihan_stats', $result['stats']);
            Session::put('import_penagihan_fatal', $result['fatal'] ?? null);

            return redirect()->route('penagihan.index')->with('active_tab', 'preview');

        } catch (\Exception $e) {
            return redirect()->route('penagihan.index')
                ->with('error', 'Gagal memproses file: ' . $e->getMessage())
                ->with('active_tab', 'import');
        }
    }

    /**
     * Simpan data yang valid ke database
     */
    public function save(PenagihanImportService $service)
    {
        try {
            $validRows = Session::get('import_penagihan_valid', []);

            if (empty($validRows)) {
                return redirect()->route('penagihan.index')
                    ->with('error', 'Tidak ada data valid untuk disimpan.')
                    ->with('active_tab', 'preview');
            }

            $count = $service->saveToDatabase($validRows);

            // Bersihkan session setelah save
            Session::forget([
                'import_penagihan_preview',
                'import_penagihan_valid',
                'import_penagihan_errors',
                'import_penagihan_stats',
                'import_penagihan_fatal'
            ]);

            return redirect()->route('penagihan.index')
                ->with('success', "{$count} data penagihan berhasil disimpan.")
                ->with('active_tab', 'import');

        } catch (\Exception $e) {
            return redirect()->route('penagihan.index')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->with('active_tab', 'preview');
        }
    }

    /**
     * Reset / Batal import
     */
    public function reset()
    {
        Session::forget([
            'import_penagihan_preview',
            'import_penagihan_valid',
            'import_penagihan_errors',
            'import_penagihan_stats',
            'import_penagihan_fatal'
        ]);

        return redirect()->route('penagihan.index')->with('active_tab', 'import');
    }
}
