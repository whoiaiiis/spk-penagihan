<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenilaianController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | HALAMAN DATA PENILAIAN
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        // ambil data kriteria
        $kriteria = DB::table('kriteria')
            ->orderBy('id')
            ->get();

        // search & filter
        $search   = $request->get('search');
        $kategori = $request->get('kategori');
        $sort     = $request->get('sort');

        // query penilaian
        $query = DB::table('penilaian');

        if ($search) {
            $query->where('idpel', 'like', '%' . $search . '%');
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        // sorting
        switch ($sort) {
            case 'c1_desc':
                $query->orderByDesc('c1');
                break;
            case 'c2_desc':
                $query->orderByDesc('c2');
                break;
            case 'c3_desc':
                $query->orderByDesc('c3');
                break;
            case 'c4_desc':
                $query->orderByDesc('c4');
                break;
            case 'vi_desc':
                $query->orderByDesc('total_nilai');
                break;
            default:
                $query->orderBy('idpel');
                break;
        }

        // ambil data penilaian (paginated)
        $penilaian = $query
            ->paginate(50)
            ->withQueryString();

        // statistik
        $stats = [
            'total_pelanggan' => DB::table('penilaian')->count(),
            'generated_at'    => DB::table('penilaian')->max('updated_at'),
        ];

        // summary statistik
        $summary = [
            'total_pelanggan' => DB::table('penilaian')->count(),
            'total_tunggakan' => DB::table('penilaian')->sum('c1'),
            'avg_lembar'      => DB::table('penilaian')->avg('c2'),
            'total_rpbk'      => DB::table('penilaian')->sum('c3'),
            'max_daya'        => DB::table('penilaian')->max('c4'),
        ];

        return view(
            'staff.datapenilaian.datapenilaian',
            compact(
                'kriteria',
                'penilaian',
                'stats',
                'summary'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE PENILAIAN SAW
    |--------------------------------------------------------------------------
    |
    | 1. Hapus data lama (penilaian, normalisasi, ranking) — pakai TRUNCATE
    | 2. Ambil data pelanggan (hanya kolom yang dibutuhkan)
    | 3. Hitung C1–C4 dari pelanggan
    | 4. Normalisasi SAW (BENEFIT: value / max)
    | 5. Hitung total_nilai (Vi)
    | 6. Tentukan kategori berdasarkan Vi
    | 7. Simpan ke database dengan chunk 500
    |
    */
    public function generate(Request $request)
{
        // ----------------------------------------------------------------
        // Tambah waktu eksekusi agar tidak timeout untuk data besar
        // ----------------------------------------------------------------
        set_time_limit(300);

        // ----------------------------------------------------------------
        // LOG: generate dimulai
        // ----------------------------------------------------------------
        Log::info('[SAW] GENERATE DIMULAI', [
            'user'       => auth()->id() ?? 'guest',
            'started_at' => now()->toDateTimeString(),
        ]);

        try {

            DB::beginTransaction();

            // ------------------------------------------------------------
            // DELETE (bukan TRUNCATE) — aman di dalam transaksi MySQL.
            // TRUNCATE menyebabkan IMPLICIT COMMIT pada InnoDB sehingga
            // rollBack() tidak berfungsi jika INSERT gagal setelahnya.
            // Urutan: ranking → normalisasi → penilaian (child dulu).
            // ------------------------------------------------------------
            DB::table('ranking')->delete();
            DB::table('normalisasi')->delete();
            DB::table('penilaian')->delete();

            Log::info('[SAW] Tabel lama berhasil di-delete.', [
                'penilaian_count'   => DB::table('penilaian')->count(),
                'normalisasi_count' => DB::table('normalisasi')->count(),
                'ranking_count'     => DB::table('ranking')->count(),
            ]);

            // ------------------------------------------------------------
            // [PERBAIKAN 4] Hanya ambil kolom yang dibutuhkan,
            // bukan SELECT * agar lebih ringan di memori & jaringan DB
            // ------------------------------------------------------------
            $pelanggan = DB::table('pelanggan')
                ->select('idpel', 'rptag', 'lembar', 'rpbk', 'daya')
                ->get();

            // ------------------------------------------------------------
            // LOG: jumlah pelanggan yang akan diproses
            // ------------------------------------------------------------
            Log::info('[SAW] Data pelanggan berhasil diambil.', [
                'total' => $pelanggan->count(),
            ]);

            if ($pelanggan->isEmpty()) {

                DB::rollBack();

                Log::warning('[SAW] Data pelanggan kosong, generate dibatalkan.');

                return redirect()
                    ->route('datapenilaian.index')
                    ->with('error', 'Data pelanggan kosong. Silakan import data terlebih dahulu.');
            }

            /*
            |--------------------------------------------------------------
            | BOBOT SAW
            |--------------------------------------------------------------
            | C1 = Besar Tunggakan (rptag)  = 40%  BENEFIT
            | C2 = Lama Tunggakan (lembar)  = 35%  BENEFIT
            | C3 = Total Tagihan (rpbk)     = 20%  BENEFIT
            | C4 = Daya Listrik (daya)      = 5%   BENEFIT
            |--------------------------------------------------------------
            */
            $w1 = 0.40;
            $w2 = 0.35;
            $w3 = 0.20;
            $w4 = 0.05;

            /*
            |--------------------------------------------------------------
            | NILAI MAX UNTUK NORMALISASI (BENEFIT)
            |--------------------------------------------------------------
            | Semua kriteria BENEFIT → rumus: value / max(value)
            | Fallback ke 1 agar tidak division by zero
            |--------------------------------------------------------------
            */
            $maxC1 = $pelanggan->max('rptag')  ?: 1;
            $maxC2 = $pelanggan->max('lembar') ?: 1;
            $maxC3 = $pelanggan->max('rpbk')   ?: 1;
            $maxC4 = $pelanggan->max('daya')   ?: 1;

            Log::info('[SAW] Nilai MAX kriteria.', [
                'maxC1 (rptag)'  => $maxC1,
                'maxC2 (lembar)' => $maxC2,
                'maxC3 (rpbk)'   => $maxC3,
                'maxC4 (daya)'   => $maxC4,
            ]);

            $rows = [];

            foreach ($pelanggan as $p) {

                /*
                |----------------------------------------------------------
                | NILAI ASLI (dari pelanggan)
                |----------------------------------------------------------
                */
                $c1 = (int) ($p->rptag  ?? 0);
                $c2 = (int) ($p->lembar ?? 0);
                $c3 = (int) ($p->rpbk   ?? 0);
                $c4 = (int) ($p->daya   ?? 0);

                /*
                |----------------------------------------------------------
                | NORMALISASI SAW — BENEFIT: value / max(value)
                |----------------------------------------------------------
                */
                $norm_c1 = $maxC1 > 0 ? $c1 / $maxC1 : 0;
                $norm_c2 = $maxC2 > 0 ? $c2 / $maxC2 : 0;
                $norm_c3 = $maxC3 > 0 ? $c3 / $maxC3 : 0;
                $norm_c4 = $maxC4 > 0 ? $c4 / $maxC4 : 0;

                /*
                |----------------------------------------------------------
                | RUMUS SAW — Vi = Σ (wj × rij)
                |----------------------------------------------------------
                */
                $total_nilai =
                    ($norm_c1 * $w1) +
                    ($norm_c2 * $w2) +
                    ($norm_c3 * $w3) +
                    ($norm_c4 * $w4);

                /*
                |----------------------------------------------------------
                | KATEGORI PRIORITAS (berdasarkan Vi)
                |----------------------------------------------------------
                | >= 0.80 = Sangat Prioritas
                | >= 0.60 = Prioritas
                | >= 0.40 = Sedang
                | <  0.40 = Rendah
                |----------------------------------------------------------
                */
                if ($total_nilai >= 0.80) {
                    $kategori = 'Sangat Prioritas';
                } elseif ($total_nilai >= 0.60) {
                    $kategori = 'Prioritas';
                } elseif ($total_nilai >= 0.40) {
                    $kategori = 'Sedang';
                } else {
                    $kategori = 'Rendah';
                }

                /*
                |----------------------------------------------------------
                | SIMPAN KE ARRAY
                |----------------------------------------------------------
                */
                $rows[] = [
                    'idpel'      => $p->idpel,
                    'c1'         => $c1,
                    'c2'         => $c2,
                    'c3'         => $c3,
                    'c4'         => $c4,
                    'norm_c1'    => round($norm_c1, 6),
                    'norm_c2'    => round($norm_c2, 6),
                    'norm_c3'    => round($norm_c3, 6),
                    'norm_c4'    => round($norm_c4, 6),
                    'total_nilai' => round($total_nilai, 6),
                    'kategori'   => $kategori,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // ------------------------------------------------------------
            // LOG: sebelum insert
            // ------------------------------------------------------------
            Log::info('[SAW] Perhitungan SAW selesai, mulai insert ke database.', [
                'jumlah_rows' => count($rows),
            ]);

            // ------------------------------------------------------------
            // INSERT DATABASE — chunk 500 agar tidak melebihi
            // batas parameter MySQL (max_allowed_packet)
            // ------------------------------------------------------------
            foreach (array_chunk($rows, 500) as $i => $chunk) {

                DB::table('penilaian')->insert($chunk);

                Log::info('[SAW] Chunk ke-' . ($i + 1) . ' berhasil diinsert.', [
                    'jumlah' => count($chunk),
                ]);
            }

            // ------------------------------------------------------------
            // LOG: sebelum commit
            // ------------------------------------------------------------
            Log::info('[SAW] Semua chunk selesai, melakukan COMMIT.');

            DB::commit();

            // ------------------------------------------------------------
            // LOG: generate selesai + verifikasi jumlah tersimpan
            // ------------------------------------------------------------
            $finalCount = DB::table('penilaian')->count();

            Log::info('[SAW] GENERATE SELESAI.', [
                'total_diproses'  => count($rows),
                'total_tersimpan' => $finalCount,
                'match'           => $finalCount === count($rows),
                'finished_at'     => now()->toDateTimeString(),
            ]);

            return redirect()
                ->route('datapenilaian.index')
                ->with('success', 'Generate penilaian berhasil. ' . count($rows) . ' pelanggan diproses.')
                ->with('active_tab', 'matriks');

        } catch (\Throwable $e) {

            DB::rollBack();

            // ------------------------------------------------------------
            // LOG: catat error secara lengkap agar mudah di-debug
            // ------------------------------------------------------------
            Log::error('[SAW] GENERATE GAGAL.', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('datapenilaian.index')
                ->with('error', 'Gagal generate: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE KRITERIA
    |--------------------------------------------------------------------------
    */
    public function updateKriteria(Request $request)
    {
        $request->validate([
            'kriteria'           => 'required|array',
            'kriteria.*.id'      => 'required|exists:kriteria,id',
            'kriteria.*.bobot'   => 'required|numeric|min:0|max:100',
            'kriteria.*.tipe'    => 'required|in:benefit,cost',
        ]);

        /*
        |----------------------------------------------------------------------
        | VALIDASI TOTAL BOBOT = 100%
        |----------------------------------------------------------------------
        */
        $totalBobot = collect($request->kriteria)->sum('bobot');

        if ($totalBobot != 100) {
            return back()->with('error', 'Total bobot harus 100%. Saat ini: ' . $totalBobot . '%');
        }

        DB::beginTransaction();

        try {

            foreach ($request->kriteria as $k) {

                DB::table('kriteria')
                    ->where('id', $k['id'])
                    ->update([
                        'bobot' => $k['bobot'],
                        'tipe'  => $k['tipe'],
                    ]);
            }

            /*
            |------------------------------------------------------------------
            | RESET NORMALISASI & RANKING setelah bobot berubah
            |------------------------------------------------------------------
            */
            DB::table('normalisasi')->truncate();
            DB::table('ranking')->truncate();

            DB::commit();

            Log::info('[SAW] Kriteria bobot berhasil diperbarui.', [
                'total_bobot' => $totalBobot,
            ]);

            return redirect()
                ->route('datapenilaian.index')
                ->with('success', 'Bobot berhasil diperbarui. Silakan Generate ulang penilaian.')
                ->with('active_tab', 'kriteria');

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('[SAW] Gagal update kriteria.', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('datapenilaian.index')
                ->with('error', 'Gagal update kriteria: ' . $e->getMessage());
        }
    }
}