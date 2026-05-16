<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $kriteria = DB::table('kriteria')->orderBy('id')->get();

        // Join ranking + penilaian + pelanggan
        $query = DB::table('ranking')
            ->join('penilaian', 'penilaian.idpel', '=', 'ranking.idpel')
            ->leftJoin('pelanggan', DB::raw('CAST(pelanggan.idpel AS CHAR)'), '=', DB::raw('CAST(ranking.idpel AS CHAR)'))
            ->select(
                'ranking.idpel',
                'ranking.nilai_akhir',
                'ranking.ranking',
                'ranking.updated_at',
                'pelanggan.nama',
                'pelanggan.status_penagihan',
                'penilaian.norm_c1', 'penilaian.norm_c2',
                'penilaian.norm_c3', 'penilaian.norm_c4',
                'penilaian.kategori'
            )
            ->where(function ($q) {
                $q->whereNull('pelanggan.status_penagihan')
                  ->orWhere('pelanggan.status_penagihan', '!=', 'sudah');
            });

        // Filter: kategori
        $kategoriFilter = $request->get('kategori');
        if ($kategoriFilter) {
            $query->where('penilaian.kategori', $kategoriFilter);
        }

        // Filter: search
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ranking.idpel', 'like', "%{$search}%")
                  ->orWhere('pelanggan.nama', 'like', "%{$search}%");
            });
        }

        $ranking = $query->orderBy('ranking.ranking')->get();

        // Filter: top N
        $top = $request->get('top');
        if ($top) {
            $ranking = $ranking->take((int) $top);
        }

        // Normalisasi table
        $normalisasi = DB::table('normalisasi')->orderBy('idpel')->get();

        // Stats
        $allRanking = DB::table('ranking')
            ->join('penilaian', 'penilaian.idpel', '=', 'ranking.idpel')
            ->select('ranking.*', 'penilaian.kategori')
            ->get();

        $stats = [
            'total'        => $ranking->count(),
            'generated_at' => DB::table('ranking')->max('updated_at'),
        ];

        return view('staff.hasilranking.hasilranking', compact(
            'normalisasi', 'ranking', 'kriteria', 'stats'
        ));
    }

    public function generate()
    {
        try {
            set_time_limit(300);
            DB::beginTransaction();

            $penilaian = DB::table('penilaian')
                ->join('pelanggan', 'penilaian.idpel', '=', 'pelanggan.idpel')
                ->where(function ($q) {
                    $q->whereNull('pelanggan.status_penagihan')
                      ->orWhere('pelanggan.status_penagihan', '!=', 'sudah');
                })
                ->select('penilaian.*')
                ->orderBy('penilaian.idpel')
                ->get();

            if ($penilaian->isEmpty()) {
                return redirect()->route('hasilranking.index')
                    ->with('error', 'Belum ada data penilaian. Silakan generate penilaian terlebih dahulu.');
            }

            // Clear old data
            DB::table('normalisasi')->delete();
            DB::table('ranking')->delete();

            // Build normalisasi rows from penilaian
            $normRows = [];
            foreach ($penilaian as $row) {
                $normRows[] = [
                    'idpel'      => $row->idpel,
                    'r1'         => $row->norm_c1,
                    'r2'         => $row->norm_c2,
                    'r3'         => $row->norm_c3,
                    'r4'         => $row->norm_c4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Build ranking rows sorted by total_nilai DESC
            $rankingRows = [];
            foreach ($penilaian as $row) {
                $rankingRows[] = [
                    'idpel'       => $row->idpel,
                    'nilai_akhir' => $row->total_nilai,
                    'ranking'     => 0,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            // Sort DESC and assign ranking positions
            usort($rankingRows, fn($a, $b) => $b['nilai_akhir'] <=> $a['nilai_akhir']);
            foreach ($rankingRows as $i => &$r) {
                $r['ranking'] = $i + 1;
            }
            unset($r);

            // Insert normalisasi
            foreach (array_chunk($normRows, 500) as $chunk) {
                DB::table('normalisasi')->insert($chunk);
            }

            // Insert ranking
            foreach (array_chunk($rankingRows, 500) as $chunk) {
                DB::table('ranking')->insert($chunk);
            }

            DB::commit();

            return redirect()->route('hasilranking.index')
                ->with('success', 'Berhasil generate ranking untuk ' . count($rankingRows) . ' pelanggan.')
                ->with('active_tab', 'hasil');

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('hasilranking.index')
                ->with('error', 'Gagal generate ranking: ' . $e->getMessage());
        }
    }
}