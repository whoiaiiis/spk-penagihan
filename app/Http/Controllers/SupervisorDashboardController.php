<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisorDashboardController extends Controller
{
    /**
     * Dashboard Supervisor - Monitoring Performa Petugas
     */
    public function index(Request $request)
    {
        // Filter bulan (default: bulan saat ini)
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));

        // ================================================================
        // 1. TOTAL PETUGAS AKTIF
        // ================================================================
        $totalPetugasAktif = DB::table('petugas')
            ->where('status', 'aktif')
            ->count();

        // ================================================================
        // 2. TOTAL PELANGGAN TERTAGIH (COUNT status = 'sudah' bulan ini)
        // ================================================================
        $totalPelangganTertagih = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'sudah')
            ->count();

        // ================================================================
        // 3. TOTAL NOMINAL TERTAGIH (SUM jumlah_bayar status = 'sudah')
        // ================================================================
        $totalNominalTertagih = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'sudah')
            ->sum('jumlah_bayar') ?? 0;

        // ================================================================
        // 4. PERSENTASE KINERJA PENAGIHAN
        // ================================================================
        $totalPenagihan = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->count();

        $persentaseKinerja = 0;
        if ($totalPenagihan > 0) {
            $persentaseKinerja = round(($totalPelangganTertagih / $totalPenagihan) * 100, 1);
        }

        // ================================================================
        // 5. TOP 10 PETUGAS TERBAIK (berdasarkan jumlah pelanggan tertagih)
        // ================================================================
        $topPetugas = DB::table('petugas')
            ->leftJoin('penagihan', function($join) use ($bulan) {
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
                DB::raw('COUNT(DISTINCT CASE WHEN penagihan.status = "sudah" THEN penagihan.idpel END) as jumlah_tagihan'),
                DB::raw('COALESCE(SUM(CASE WHEN penagihan.status = "sudah" THEN penagihan.jumlah_bayar ELSE 0 END), 0) as total_tertagih'),
                DB::raw('COUNT(DISTINCT CASE WHEN penagihan.status = "belum" THEN penagihan.idpel END) as jumlah_belum'),
                DB::raw('COALESCE(SUM(pelanggan.rptag), 0) as total_target')
            )
            ->groupBy('petugas.id', 'petugas.nama', 'petugas.wilayah', 'petugas.kode_petugas')
            ->orderByDesc('jumlah_tagihan')
            ->limit(10)
            ->get();

        // Hitung persentase untuk setiap petugas
        $topPetugas = $topPetugas->map(function($p) {
            $total = $p->jumlah_tagihan + $p->jumlah_belum;
            $p->persentase = $total > 0 ? round(($p->jumlah_tagihan / $total) * 100, 1) : 0;
            return $p;
        });

        // ================================================================
        // 6. RANKING KEBERHASILAN PENAGIHAN (ALL PETUGAS)
        // ================================================================
        $rankingPetugas = DB::table('petugas')
            ->leftJoin('penagihan', function($join) use ($bulan) {
                $join->on('penagihan.petugas_id', '=', 'petugas.id')
                     ->where('penagihan.bulan', $bulan);
            })
            ->where('petugas.status', 'aktif')
            ->select(
                'petugas.id',
                'petugas.nama',
                'petugas.wilayah',
                'petugas.kode_petugas',
                DB::raw('COUNT(DISTINCT CASE WHEN penagihan.status = "sudah" THEN penagihan.idpel END) as sudah_tagih'),
                DB::raw('COUNT(DISTINCT CASE WHEN penagihan.status = "belum" THEN penagihan.idpel END) as belum_tagih'),
                DB::raw('COUNT(DISTINCT penagihan.idpel) as total_ditagih')
            )
            ->groupBy('petugas.id', 'petugas.nama', 'petugas.wilayah', 'petugas.kode_petugas')
            ->orderByDesc('sudah_tagih')
            ->get();

        // Hitung persentase ranking
        $rankingPetugas = $rankingPetugas->map(function($r) {
            $r->persentase = $r->total_ditagih > 0 ? round(($r->sudah_tagih / $r->total_ditagih) * 100, 1) : 0;
            return $r;
        });

        // ================================================================
        // 7. REKAP WILAYAH TERBAIK
        // ================================================================
        $rekapWilayah = DB::table('petugas')
            ->leftJoin('penagihan', function($join) use ($bulan) {
                $join->on('penagihan.petugas_id', '=', 'petugas.id')
                     ->where('penagihan.bulan', $bulan);
            })
            ->where('petugas.status', 'aktif')
            ->select(
                'petugas.wilayah',
                DB::raw('COUNT(DISTINCT petugas.id) as total_petugas'),
                DB::raw('COUNT(DISTINCT CASE WHEN penagihan.status = "sudah" THEN penagihan.idpel END) as total_tagih'),
                DB::raw('COUNT(DISTINCT CASE WHEN penagihan.status = "belum" THEN penagihan.idpel END) as total_belum'),
                DB::raw('COUNT(DISTINCT penagihan.idpel) as total_ditagih')
            )
            ->groupBy('petugas.wilayah')
            ->orderByDesc('total_tagih')
            ->get();

        // Hitung persentase wilayah
        $rekapWilayah = $rekapWilayah->map(function($w) {
            $w->persentase = $w->total_ditagih > 0 ? round(($w->total_tagih / $w->total_ditagih) * 100, 1) : 0;
            return $w;
        });

        // ================================================================
        // 8. CHART DATA - Status Penagihan
        // ================================================================
        $statusChart = [
            'sudah' => DB::table('penagihan')
                ->where('bulan', $bulan)
                ->where('status', 'sudah')
                ->count(),
            'belum' => DB::table('penagihan')
                ->where('bulan', $bulan)
                ->where('status', 'belum')
                ->count(),
        ];

        // ================================================================
        // 9. CHART DATA - Performa Top 10 Petugas
        // ================================================================
        $topPetugasChartData = $topPetugas->map(function($p) {
            return [
                'nama' => substr($p->nama, 0, 12),
                'jumlah_tagihan' => (int)$p->jumlah_tagihan,
                'jumlah_belum' => (int)$p->jumlah_belum,
            ];
        })->values();

        // ================================================================
        // 10. CHART DATA - Performa Wilayah
        // ================================================================
        $wilayahChartData = $rekapWilayah->map(function($w) {
            return [
                'wilayah' => $w->wilayah ?? 'Tidak Diketahui',
                'total_tagih' => (int)$w->total_tagih,
                'persentase' => (float)$w->persentase,
            ];
        })->values();

        return view('supervisor.dashboard', compact(
            'bulan',
            'totalPetugasAktif',
            'totalPelangganTertagih',
            'totalNominalTertagih',
            'persentaseKinerja',
            'topPetugas',
            'rankingPetugas',
            'rekapWilayah',
            'statusChart',
            'topPetugasChartData',
            'wilayahChartData'
        ));
    }
}
