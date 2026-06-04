<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    private const PAGINATION_LIMIT = 25;

    /**
     * Display ranking dashboard
     */
    public function index(Request $request)
    {
        $kriteria = DB::table('kriteria')->orderBy('id')->get();
        $petugasList = DB::table('petugas')->orderBy('nama')->get();

        $rankingCount = DB::table('ranking')->count();

        if ($rankingCount === 0) {
            return view('staff.hasilranking.hasilranking', [
                'ranking'     => collect(),
                'normalisasi' => collect(),
                'kriteria'    => $kriteria,
                'stats'       => $this->getStatistics($request),
                'hasData'     => false,
                'filters'     => $this->getActiveFilters($request),
                'petugasList' => $petugasList,
            ]);
        }

        $ranking     = $this->getPaginatedRanking($request);
        $normalisasi = $this->getNormalisasiData($ranking);
        $stats       = $this->getStatistics($request);

        return view('staff.hasilranking.hasilranking', [
            'ranking'     => $ranking,
            'normalisasi' => $normalisasi,
            'kriteria'    => $kriteria,
            'stats'       => $stats,
            'hasData'     => true,
            'filters'     => $this->getActiveFilters($request),
            'petugasList' => $petugasList,
        ]);
    }

    /**
     * Base query — TANPA pelanggan.rbm (sudah dihapus dari skema)
     * Kolom pengganti: pelanggan.unitap, pelanggan.kode_petugas
     */
    private function getBaseRankingQuery(Request $request)
    {
        $query = DB::table('ranking')
            ->join('penilaian', 'penilaian.idpel', '=', 'ranking.idpel')
            ->leftJoin(
                'pelanggan',
                DB::raw('CAST(pelanggan.idpel AS CHAR)'),
                '=',
                DB::raw('CAST(ranking.idpel AS CHAR)')
            )
            ->leftJoin('petugas', 'pelanggan.petugas_id', '=', 'petugas.id')
            ->select(
                'ranking.id',
                'ranking.idpel',
                'ranking.nilai_akhir',
                'ranking.ranking',
                'ranking.updated_at',

                // ✅ KOLOM BARU — rbm sudah tidak ada
                'pelanggan.nama',
                'pelanggan.unitap',          // ✅ ganti rbm
                'pelanggan.kode_petugas',    // ✅ tetap ada
                'pelanggan.petugas_id',      // ✅ tambahkan petugas_id
                'pelanggan.status_penagihan',

                'petugas.kode_petugas as kode_petugas_petugas',
                'petugas.nama as nama_petugas',

                'penilaian.norm_c1',
                'penilaian.norm_c2',
                'penilaian.norm_c3',
                'penilaian.norm_c4',
                'penilaian.c1',
                'penilaian.c2',
                'penilaian.c3',
                'penilaian.c4',
                'penilaian.kategori'
            )
            ->where(function ($q) {
                $q->whereNull('pelanggan.status_penagihan')
                  ->orWhere('pelanggan.status_penagihan', '!=', 'berhasil');
            });

        $this->applyFilters($query, $request);

        $query->orderBy('ranking.ranking', 'asc');

        return $query;
    }

    /**
     * Get paginated ranking data
     */
    private function getPaginatedRanking(Request $request)
    {
        $query = $this->getBaseRankingQuery($request);

        $page  = $request->get('page', 1);
        $total = $query->count();
        $items = $query->forPage($page, self::PAGINATION_LIMIT)->get();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            self::PAGINATION_LIMIT,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * Export ranking to CSV
     */
    public function exportCsv(Request $request)
    {
        $ranking   = $this->getBaseRankingQuery($request)->get();
        $stats     = $this->getStatistics($request);
        $max_nilai = $stats['max_nilai'] > 0 ? $stats['max_nilai'] : 1;

        $fileName = 'hasil-ranking-saw-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // ✅ Header CSV — rbm diganti unitap
        $columns = [
            'Ranking', 'IDPEL', 'Nama Pelanggan', 'Unitap',
            'Kode Petugas', 'Nama Petugas',
            'C1', 'C2', 'C3', 'C4',
            'Nilai Vi', 'Persentase', 'Risiko / Prioritas', 'Tanggal Generate',
        ];

        $callback = function () use ($ranking, $columns, $max_nilai) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 agar Excel tidak rusak encoding-nya
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns, ';');

            foreach ($ranking as $row) {
                $pct = ($row->nilai_akhir / $max_nilai) * 100;

                fputcsv($file, [
                    $row->ranking,
                    $row->idpel,
                    $row->nama ?? '-',
                    $row->unitap ?? '-',              // ✅ ganti rbm
                    $row->kode_petugas ?? '-',
                    $row->nama_petugas ?? '-',
                    $row->c1 ?? 0,
                    $row->c2 ?? 0,
                    $row->c3 ?? 0,
                    $row->c4 ?? 0,
                    number_format($row->nilai_akhir, 4, ',', ''),
                    number_format($pct, 2, ',', '') . '%',
                    $row->kategori ?? 'Rendah',
                    Carbon::parse($row->updated_at)->format('d-m-Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    /**
     * Export ranking to PDF
     */
    public function exportPdf(Request $request)
    {
        $ranking = $this->getBaseRankingQuery($request)->get();
        $stats   = $this->getStatistics($request);
        $filters = $this->getActiveFilters($request);

        $pdf = Pdf::loadView('staff.hasilranking.hasilranking_pdf', [
            'ranking' => $ranking,
            'stats'   => $stats,
            'filters' => $filters,
            'user'    => Auth::user() ?? (object) ['name' => 'System User'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Hasil-Ranking-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        $kategori = $request->get('kategori');
        if ($kategori) {
            $query->where('penilaian.kategori', $kategori);
        }

        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ranking.idpel', 'like', "%{$search}%")
                  ->orWhere('pelanggan.nama', 'like', "%{$search}%");
            });
        }

        $top = $request->get('top');
        if ($top && is_numeric($top)) {
            $query->limit((int) $top);
        }

        $petugasId = $request->get('petugas_id');
        if ($petugasId) {
            $query->where('pelanggan.petugas_id', $petugasId);
        }
    }

    /**
     * Get normalisasi data for displayed ranking rows
     */
    private function getNormalisasiData($ranking)
    {
        if ($ranking->isEmpty()) {
            return collect();
        }

        $idpels = $ranking->pluck('idpel')->toArray();

        return DB::table('normalisasi')
            ->whereIn('idpel', $idpels)
            ->get()
            ->keyBy('idpel');
    }

    /**
     * Get statistics for dashboard cards
     */
    private function getStatistics(Request $request = null)
    {
        $query = DB::table('ranking')
            ->join('penilaian', 'penilaian.idpel', '=', 'ranking.idpel');

        if ($request && $request->get('petugas_id')) {
            $query->leftJoin(
                'pelanggan',
                DB::raw('CAST(pelanggan.idpel AS CHAR)'),
                '=',
                DB::raw('CAST(ranking.idpel AS CHAR)')
            )
            ->where('pelanggan.petugas_id', $request->get('petugas_id'));
        }

        $allRanking = $query->select('ranking.*', 'penilaian.kategori')->get();

        $categories = $allRanking->groupBy('kategori');

        return [
            'total'            => $allRanking->count(),
            'sangat_prioritas' => $categories->get('Sangat Prioritas', collect())->count(),
            'prioritas'        => $categories->get('Prioritas', collect())->count(),
            'sedang'           => $categories->get('Sedang', collect())->count(),
            'rendah'           => $categories->get('Rendah', collect())->count(),
            'generated_at'     => DB::table('ranking')->max('updated_at'),
            'max_nilai'        => $allRanking->max('nilai_akhir'),
        ];
    }

    /**
     * Get active filters array
     */
    private function getActiveFilters(Request $request)
    {
        $petugasId = $request->get('petugas_id');
        $petugasNama = null;
        if ($petugasId) {
            $petugas = DB::table('petugas')->where('id', $petugasId)->first();
            if ($petugas) {
                $petugasNama = $petugas->nama;
            }
        }

        return [
            'kategori'     => $request->get('kategori'),
            'search'       => $request->get('search'),
            'top'          => $request->get('top'),
            'petugas_id'   => $petugasId,
            'petugas_nama' => $petugasNama,
        ];
    }

    /**
     * Generate ranking from penilaian data
     */
    public function generate()
    {
        try {

            set_time_limit(300);

            Log::info('[RANKING] Generate dimulai.');

            $penilaian = $this->getPenilaianData();

            if ($penilaian->isEmpty()) {
                Log::warning('[RANKING] Data penilaian kosong.');
                return redirect()->route('hasilranking.index')
                    ->with('error', 'Belum ada data penilaian. Silakan generate penilaian terlebih dahulu.');
            }

            Log::info('[RANKING] Data penilaian diambil.', ['total' => $penilaian->count()]);

            $normRows    = $this->buildNormalisasiRows($penilaian);
            $rankingRows = $this->buildRankingRows($penilaian);

            if (empty($rankingRows)) {
                Log::warning('[RANKING] Hasil perhitungan kosong.');
                return redirect()->route('hasilranking.index')
                    ->with('error', 'Hasil perhitungan kosong. Silakan periksa data penilaian.');
            }

            Log::info('[RANKING] Perhitungan selesai, mulai simpan ke database.', [
                'normalisasi' => count($normRows),
                'ranking'     => count($rankingRows),
            ]);

            try {

                // ✅ Pakai TRUNCATE agar lebih cepat
                DB::table('normalisasi')->truncate();
                DB::table('ranking')->truncate();

                foreach (array_chunk($normRows, 500) as $i => $chunk) {
                    DB::table('normalisasi')->insert($chunk);
                    Log::info('[RANKING] Normalisasi chunk ke-' . ($i + 1) . ' diinsert.');
                }

                foreach (array_chunk($rankingRows, 500) as $i => $chunk) {
                    DB::table('ranking')->insert($chunk);
                    Log::info('[RANKING] Ranking chunk ke-' . ($i + 1) . ' diinsert.');
                }

                Log::info('[RANKING] Generate selesai.', ['total' => count($rankingRows)]);

                return redirect()->route('hasilranking.index')
                    ->with('success', 'Berhasil generate ranking untuk ' . count($rankingRows) . ' pelanggan.')
                    ->with('active_tab', 'hasil');

            } catch (\Throwable $e) {
                throw $e;
            }

        } catch (\Throwable $e) {

            Log::error('[RANKING] Generate GAGAL.', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->route('hasilranking.index')
                ->with('error', 'Gagal generate ranking: ' . $e->getMessage());
        }
    }

    /**
     * Ambil data penilaian (pelanggan belum lunas)
     */
    private function getPenilaianData()
    {
        return DB::table('penilaian')
            ->join('pelanggan', 'penilaian.idpel', '=', 'pelanggan.idpel')
            ->where(function ($q) {
                $q->whereNull('pelanggan.status_penagihan')
                  ->orWhere('pelanggan.status_penagihan', '!=', 'sudah');
            })
            ->select('penilaian.*')
            ->orderBy('penilaian.idpel')
            ->get();
    }

    /**
     * Build rows untuk tabel normalisasi
     */
    private function buildNormalisasiRows($penilaian)
    {
        $rows = [];

        foreach ($penilaian as $row) {
            $rows[] = [
                'idpel'      => $row->idpel,
                'r1'         => $row->norm_c1 ?? 0,
                'r2'         => $row->norm_c2 ?? 0,
                'r3'         => $row->norm_c3 ?? 0,
                'r4'         => $row->norm_c4 ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $rows;
    }

    /**
     * Build rows untuk tabel ranking, diurutkan nilai_akhir DESC
     */
    private function buildRankingRows($penilaian)
    {
        $rows = [];

        foreach ($penilaian as $row) {
            $rows[] = [
                'idpel'       => $row->idpel,
                'nilai_akhir' => $row->total_nilai ?? 0,
                'ranking'     => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // Urutkan nilai tertinggi = ranking 1
        usort($rows, fn ($a, $b) => (float) $b['nilai_akhir'] <=> (float) $a['nilai_akhir']);

        // Assign nomor ranking
        foreach ($rows as $i => &$row) {
            $row['ranking'] = $i + 1;
        }
        unset($row);

        return $rows;
    }
}