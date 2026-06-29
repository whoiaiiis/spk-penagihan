<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenilaianController extends Controller
{
    // halaman utama data penilaian — tampilkan hasil SAW yang sudah di-generate
    public function index(Request $request)
    {
        // ambil semua kriteria untuk ditampilkan di tabel header
        $kriteria = DB::table('kriteria')
            ->orderBy('id')
            ->get();

        $search   = $request->get('search');
        $kategori = $request->get('kategori');
        $sort     = $request->get('sort');
        $periode  = $request->get('periode');

        $query = DB::table('penilaian');

        if ($periode) {
            $query->where('periode', $periode);
        }

        // filter pencarian berdasarkan idpel
        if ($search) {
            $query->where('idpel', 'like', '%' . $search . '%');
        }

        // filter berdasarkan kategori prioritas
        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        // sorting kolom — default urut idpel
        switch ($sort) {
            case 'c1_desc': $query->orderByDesc('c1');          break;
            case 'c2_desc': $query->orderByDesc('c2');          break;
            case 'c3_desc': $query->orderByDesc('c3');          break;
            case 'c4_desc': $query->orderByDesc('c4');          break;
            case 'vi_desc': $query->orderByDesc('total_nilai'); break;
            default:        $query->orderBy('idpel');           break;
        }

        $penilaian = $query->paginate(50)->withQueryString();

        $baseQuery = DB::table('penilaian');
        if ($periode) {
            $baseQuery->where('periode', $periode);
        }

        // info kapan terakhir di-generate
        $stats = [
            'total_pelanggan' => (clone $baseQuery)->count(),
            'generated_at'    => (clone $baseQuery)->max('updated_at'),
        ];

        // ringkasan agregat untuk kartu statistik di atas tabel
        $summary = [
            'total_pelanggan' => (clone $baseQuery)->count(),
            'total_tunggakan' => (clone $baseQuery)->sum('c2'), // c2 = rptag
            'avg_lembar'      => (clone $baseQuery)->avg('c3'), // c3 = lembar
            'total_rpbk'      => (clone $baseQuery)->sum('c4'), // c4 = rpbk
            'avg_historis'    => (clone $baseQuery)->avg('c1'), // c1 = historis_tunggakan
        ];

        $periodeList = DB::table('penilaian')->select('periode')->distinct()->orderBy('periode', 'desc')->pluck('periode');

        return view(
            'staff.datapenilaian.datapenilaian',
            compact('kriteria', 'penilaian', 'stats', 'summary', 'periode', 'periodeList')
        );
    }

    // proses generate SAW — hitung normalisasi & total nilai semua pelanggan
    public function generate(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
        ]);
        $periode = $request->periode;

        // naikkan time limit, kalau data 50rb+ bisa timeout kalau cuma 30 detik
        set_time_limit(300);

        Log::info('[SAW] GENERATE DIMULAI', [
            'user'       => auth()->id() ?? 'guest',
            'periode'    => $periode,
            'started_at' => now()->toDateTimeString(),
        ]);

        try {
            Log::info('SEBELUM DELETE RANKING PERIODE ' . $periode);
            DB::table('ranking')->where('periode', $periode)->delete();

            Log::info('SEBELUM DELETE NORMALISASI PERIODE ' . $periode);
            DB::table('normalisasi')->where('periode', $periode)->delete();

            Log::info('SEBELUM DELETE PENILAIAN PERIODE ' . $periode);
            DB::table('penilaian')->where('periode', $periode)->delete();

            DB::beginTransaction();

            $pelangganQuery = DB::table('pelanggan')
                ->where('periode', $periode)
                ->where(function($q) {
                    $q->whereNull('status_penagihan')->orWhere('status_penagihan', '!=', 'berhasil');
                });

            $maxC1 = (clone $pelangganQuery)->max('historis_tunggakan') ?: 1;
            $maxC2 = (clone $pelangganQuery)->max('rptag')              ?: 1;
            $maxC3 = (clone $pelangganQuery)->max('lembar')             ?: 1;
            $maxC4 = (clone $pelangganQuery)->max('rpbk')               ?: 1;

            Log::info('[SAW] Nilai MAX kriteria.', [
                'maxC1 (historis)' => $maxC1,
                'maxC2 (rptag)'    => $maxC2,
                'maxC3 (lembar)'   => $maxC3,
                'maxC4 (rpbk)'     => $maxC4,
            ]);

            // ambil bobot dari tabel kriteria
            $kriteriaDB = DB::table('kriteria')->orderBy('id')->get();
            $w1 = ($kriteriaDB->firstWhere('id', 1)->bobot ?? 40) / 100; // historis tunggakan
            $w2 = ($kriteriaDB->firstWhere('id', 2)->bobot ?? 35) / 100; // rptag (tagihan)
            $w3 = ($kriteriaDB->firstWhere('id', 3)->bobot ?? 20) / 100; // lembar
            $w4 = ($kriteriaDB->firstWhere('id', 4)->bobot ?? 5) / 100;  // rpbk

            $totalProcessed = 0;

            // chunk 500 baris sekaligus supaya PHP tidak OOM waktu proses data besar
            $pelangganQuery
                ->select('idpel', 'rptag', 'lembar', 'rpbk', 'historis_tunggakan')
                ->orderBy('idpel')
                ->chunk(500, function ($pelangganChunk) use ($maxC1, $maxC2, $maxC3, $maxC4, $w1, $w2, $w3, $w4, &$totalProcessed, $periode) {

                    $rows = [];

                    foreach ($pelangganChunk as $p) {
                        // ambil nilai mentah, default 0 kalau null
                        $c1 = (int) ($p->historis_tunggakan ?? 0);
                        $c2 = (int) ($p->rptag  ?? 0);
                        $c3 = (int) ($p->lembar ?? 0);
                        $c4 = (int) ($p->rpbk   ?? 0);

                        // normalisasi benefit: nilai / max
                        $norm_c1 = $maxC1 > 0 ? $c1 / $maxC1 : 0;
                        $norm_c2 = $maxC2 > 0 ? $c2 / $maxC2 : 0;
                        $norm_c3 = $maxC3 > 0 ? $c3 / $maxC3 : 0;
                        $norm_c4 = $maxC4 > 0 ? $c4 / $maxC4 : 0;

                        // hitung total nilai SAW (weighted sum)
                        $total_nilai =
                            ($norm_c1 * $w1) +
                            ($norm_c2 * $w2) +
                            ($norm_c3 * $w3) +
                            ($norm_c4 * $w4);

                        // tentukan kategori berdasarkan threshold total nilai
                        if ($total_nilai >= 0.75) {
                            $kategori = 'Sangat Prioritas';
                        } elseif ($total_nilai >= 0.50) {
                            $kategori = 'Prioritas';
                        } elseif ($total_nilai >= 0.25) {
                            $kategori = 'Sedang';
                        } else {
                            $kategori = 'Rendah';
                        }

                        $rows[] = [
                            'idpel'       => $p->idpel,
                            'periode'     => $periode,
                            'c1'          => $c1,
                            'c2'          => $c2,
                            'c3'          => $c3,
                            'c4'          => $c4,
                            'norm_c1'     => round($norm_c1, 6),
                            'norm_c2'     => round($norm_c2, 6),
                            'norm_c3'     => round($norm_c3, 6),
                            'norm_c4'     => round($norm_c4, 6),
                            'total_nilai' => round($total_nilai, 6),
                            'kategori'    => $kategori,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ];
                    }

                    // batch insert per chunk — jauh lebih cepat dari insert satu-satu
                    DB::table('penilaian')->insert($rows);
                    $totalProcessed += count($rows);

                    Log::info('[SAW] Chunk berhasil diinsert.', [
                        'jumlah_insert'   => count($rows),
                        'total_sementara' => $totalProcessed,
                    ]);
                });

            // kalau tidak ada baris yang diproses, kemungkinan tabel pelanggan kosong
            if ($totalProcessed === 0) {
                DB::rollBack();
                Log::warning('[SAW] Data pelanggan kosong, generate dibatalkan.');
                return redirect()
                    ->route('datapenilaian.index')
                    ->with('error', 'Data pelanggan kosong. Silakan import data terlebih dahulu.');
            }

            DB::commit();

            $finalCount = DB::table('penilaian')->where('periode', $periode)->count();

            Log::info('[SAW] GENERATE SELESAI.', [
                'total_diproses'  => $totalProcessed,
                'total_tersimpan' => $finalCount,
                'match'           => $finalCount === $totalProcessed,
                'finished_at'     => now()->toDateTimeString(),
            ]);

            return redirect()
                ->route('datapenilaian.index')
                ->with('success', 'Generate penilaian berhasil. ' . $totalProcessed . ' pelanggan diproses.')
                ->with('active_tab', 'matriks');

        } catch (\Throwable $e) {
            DB::rollBack();

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

    // update bobot & tipe kriteria — validasi total harus pas 100% sebelum disimpan
    public function updateKriteria(Request $request)
    {
        $request->validate([
            'kriteria'         => 'required|array',
            'kriteria.*.id'    => 'required|exists:kriteria,id',
            'kriteria.*.bobot' => 'required|numeric|min:0|max:100',
            'kriteria.*.tipe'  => 'required|in:benefit,cost',
        ]);

        $totalBobot = collect($request->kriteria)->sum('bobot');

        // tolak kalau bobotnya tidak genap 100
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

            // jangan kosongkan ranking/normalisasi karena ini akan merusak data history
            // DB::table('normalisasi')->truncate();
            // DB::table('ranking')->truncate();

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