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
    // jumlah baris per halaman di tabel ranking
    private const PAGINATION_LIMIT = 25;

    // halaman utama ranking — kalau belum ada data, tampilkan state kosong
    public function index(Request $request)
    {
        $kriteria    = DB::table('kriteria')->orderBy('id')->get();
        $petugasList = DB::table('petugas')->orderBy('nama')->get();
        $periode     = $request->get('periode');

        $queryCount = DB::table('ranking');
        if ($periode) {
            $queryCount->where('periode', $periode);
        }
        $rankingCount = $queryCount->count();

        $periodeList = DB::table('ranking')->select('periode')->distinct()->orderBy('periode', 'desc')->pluck('periode');

        // belum pernah di-generate, kirim view dengan flag hasData = false
        if ($rankingCount === 0) {
            return view('staff.hasilranking.hasilranking', [
                'ranking'     => collect(),
                'normalisasi' => collect(),
                'kriteria'    => $kriteria,
                'stats'       => $this->getStatistics($request),
                'hasData'     => false,
                'filters'     => $this->getActiveFilters($request),
                'petugasList' => $petugasList,
                'periode'     => $periode,
                'periodeList' => $periodeList,
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
            'periode'     => $periode,
            'periodeList' => $periodeList,
        ]);
    }

    // query utama ranking — join ke penilaian, pelanggan, dan petugas
    private function getBaseRankingQuery(Request $request)
    {
        $query = DB::table('ranking')
            ->join('penilaian', function($join) {
                $join->on('penilaian.idpel', '=', 'ranking.idpel')
                     ->on('penilaian.periode', '=', 'ranking.periode');
            })
            ->leftJoin(
                'pelanggan',
                function($join) {
                    $join->on(DB::raw('CAST(pelanggan.idpel AS CHAR)'), '=', DB::raw('CAST(ranking.idpel AS CHAR)'))
                         ->on('pelanggan.periode', '=', 'ranking.periode');
                }
            )
            ->leftJoin('petugas', 'pelanggan.petugas_id', '=', 'petugas.id')
            ->select(
                'ranking.id',
                'ranking.idpel',
                'ranking.periode',          // ← diperlukan oleh getNormalisasiData()
                'ranking.nilai_akhir',
                'ranking.ranking',
                'ranking.updated_at',

                'pelanggan.nama',
                'pelanggan.unitap',
                'pelanggan.kode_petugas',
                'pelanggan.petugas_id',
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
            // hanya tampilkan yang belum lunas
            ->where(function ($q) {
                $q->whereNull('pelanggan.status_penagihan')
                  ->orWhere('pelanggan.status_penagihan', '!=', 'berhasil');
            });

        $this->applyFilters($query, $request);

        $query->orderBy('ranking.ranking', 'asc');

        return $query;
    }

    // bungkus query ranking ke dalam paginator manual
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

    // export ke CSV — pakai BOM UTF-8 supaya Excel tidak rusak enkodingnya
    public function exportCsv(Request $request)
    {
        $ranking   = $this->getBaseRankingQuery($request)->get();
        $stats     = $this->getStatistics($request);
        $max_nilai = $stats['max_nilai'] > 0 ? $stats['max_nilai'] : 1;

        $periode = $request->get('periode');
        $periodeFilename = $periode ? \Carbon\Carbon::createFromFormat('Y-m', $periode)->locale('id')->translatedFormat('F_Y') : 'Semua_Periode';
        $fileName = 'Ranking_' . $periodeFilename . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Ranking', 'IDPEL', 'Nama Pelanggan', 'Unitap',
            'Kode Petugas', 'Nama Petugas',
            'C1 (Historis Tunggakan)', 'C2 (Besar Tunggakan)',
            'C3 (Lama Tunggakan)', 'C4 (Total Tagihan)',
            'Nilai Vi', 'Persentase', 'Risiko / Prioritas', 'Periode', 'Tanggal Generate',
        ];

        $callback = function () use ($ranking, $columns, $max_nilai, $request) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
            
            $periode = $request->get('periode');
            $periodeHeader = $periode ? \Carbon\Carbon::createFromFormat('Y-m', $periode)->locale('id')->translatedFormat('F Y') : 'Semua Periode';
            fputcsv($file, ['Periode : ' . $periodeHeader], ';');
            fputcsv($file, [], ';');

            fputcsv($file, $columns, ';');

            foreach ($ranking as $row) {
                $pct = ($row->nilai_akhir / $max_nilai) * 100;

                fputcsv($file, [
                    $row->ranking,
                    $row->idpel,
                    $row->nama          ?? '-',
                    $row->unitap        ?? '-',
                    $row->kode_petugas  ?? '-',
                    $row->nama_petugas  ?? '-',
                    $row->c1 ?? 0,
                    $row->c2 ?? 0,
                    $row->c3 ?? 0,
                    $row->c4 ?? 0,
                    number_format($row->nilai_akhir, 4, ',', ''),
                    number_format($pct, 2, ',', '') . '%',
                    $row->kategori ?? 'Rendah',
                    $row->periode,
                    \Carbon\Carbon::parse($row->updated_at, 'UTC')->setTimezone('Asia/Jakarta')->format('d-m-Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    // export ke PDF landscape — pakai DomPDF, view terpisah khusus PDF
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

        $periode = $request->get('periode');
        $periodeFilename = $periode ? \Carbon\Carbon::createFromFormat('Y-m', $periode)->locale('id')->translatedFormat('F_Y') : 'Semua_Periode';
        return $pdf->download('Ranking_' . $periodeFilename . '.pdf');
    }

    // terapkan filter dari query string ke query ranking
    private function applyFilters($query, Request $request)
    {
        // filter kategori prioritas
        $kategori = $request->get('kategori');
        if ($kategori) {
            $query->where('penilaian.kategori', $kategori);
        }

        // pencarian idpel atau nama pelanggan
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ranking.idpel', 'like', "%{$search}%")
                  ->orWhere('pelanggan.nama', 'like', "%{$search}%");
            });
        }

        // batasi hanya top-N ranking
        $top = $request->get('top');
        if ($top && is_numeric($top)) {
            $query->limit((int) $top);
        }

        // filter per petugas
        $petugasId = $request->get('petugas_id');
        if ($petugasId) {
            $query->where('pelanggan.petugas_id', $petugasId);
        }

        // filter per periode
        $periode = $request->get('periode');
        if ($periode) {
            $query->where('ranking.periode', $periode);
        }
    }

    private function getNormalisasiData($ranking)
    {
        if ($ranking->isEmpty()) {
            return collect();
        }

        // kita perlu match by idpel and periode
        $conditions = $ranking->map(function($r) {
            return "('{$r->idpel}', '{$r->periode}')";
        })->unique()->toArray();

        // jika ranking tidak return periode, kita ambil dari periode request aja kalau ada.
        // wait, kita bisa gunakan array of (idpel, periode) atau gunakan WHERE idpel IN (...) jika periode difilter secara global.
        $idpels = $ranking->pluck('idpel')->toArray();
        $periode = request()->get('periode');

        $query = DB::table('normalisasi')->whereIn('idpel', $idpels);
        if ($periode) {
            $query->where('periode', $periode);
        }

        return $query->get()->keyBy('idpel'); // asumsikan satu periode saat difilter
    }

    // hitung statistik kartu ringkasan — bisa difilter per petugas
    private function getStatistics(Request $request = null)
    {
        $query = DB::table('ranking')
            ->join('penilaian', function($join) {
                $join->on('penilaian.idpel', '=', 'ranking.idpel')
                     ->on('penilaian.periode', '=', 'ranking.periode');
            });

        // kalau ada filter petugas, perlu join ke pelanggan dulu
        if ($request && $request->get('petugas_id')) {
            $query->leftJoin(
                'pelanggan',
                function($join) {
                    $join->on(DB::raw('CAST(pelanggan.idpel AS CHAR)'), '=', DB::raw('CAST(ranking.idpel AS CHAR)'))
                         ->on('pelanggan.periode', '=', 'ranking.periode');
                }
            )
            ->where('pelanggan.petugas_id', $request->get('petugas_id'));
        }

        if ($request && $request->get('periode')) {
            $query->where('ranking.periode', $request->get('periode'));
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

    // kumpulkan filter aktif — termasuk resolve nama petugas dari ID-nya
    private function getActiveFilters(Request $request)
    {
        $petugasId   = $request->get('petugas_id');
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
            'periode'      => $request->get('periode'),
        ];
    }

    // generate ranking dari data penilaian yang sudah ada
    public function generate(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
        ]);
        $periode = $request->periode;

        try {
            set_time_limit(300);

            Log::info('[RANKING] Generate dimulai.');

            $penilaian = $this->getPenilaianData($periode);

            if ($penilaian->isEmpty()) {
                Log::warning('[RANKING] Data penilaian kosong.');
                return redirect()->route('hasilranking.index')
                    ->with('error', 'Belum ada data penilaian pada periode tersebut. Silakan generate penilaian terlebih dahulu.');
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

            DB::table('normalisasi')->where('periode', $periode)->delete();
            DB::table('ranking')->where('periode', $periode)->delete();

            // insert per chunk 500 baris supaya tidak timeout
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

    // ambil data penilaian — exclude pelanggan yang status_penagihannya sudah 'berhasil'
    private function getPenilaianData($periode)
    {
        return DB::table('penilaian')
            ->join('pelanggan', function($join) {
                $join->on('penilaian.idpel', '=', 'pelanggan.idpel')
                     ->on('penilaian.periode', '=', 'pelanggan.periode');
            })
            ->where('penilaian.periode', $periode)
            ->where(function ($q) {
                $q->whereNull('pelanggan.status_penagihan')
                  ->orWhere('pelanggan.status_penagihan', '!=', 'berhasil');
            })
            ->select('penilaian.*')
            ->orderBy('penilaian.idpel')
            ->get();
    }

    // petakan hasil penilaian ke format baris tabel normalisasi
    private function buildNormalisasiRows($penilaian)
    {
        $rows = [];

        foreach ($penilaian as $row) {
            $rows[] = [
                'idpel'      => $row->idpel,
                'periode'    => $row->periode,
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

    // hitung nilai_akhir lalu urutkan descending dan assign nomor ranking
    private function buildRankingRows($penilaian)
    {
        $rows = [];

        foreach ($penilaian as $row) {
            $rows[] = [
                'idpel'       => $row->idpel,
                'periode'     => $row->periode,
                'nilai_akhir' => $row->total_nilai ?? 0,
                'ranking'     => 0, // diisi setelah sorting
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // nilai tertinggi = ranking 1
        usort($rows, fn ($a, $b) => (float) $b['nilai_akhir'] <=> (float) $a['nilai_akhir']);

        foreach ($rows as $i => &$row) {
            $row['ranking'] = $i + 1;
        }
        unset($row);

        return $rows;
    }
}