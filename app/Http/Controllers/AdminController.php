<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Dashboard Admin - Monitoring Penagihan
     */
    public function dashboard(Request $request)
    {
        // Filter bulan (default: bulan saat ini)
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));

        // ================================================================
        // 1. TOTAL PETUGAS AKTIF
        // ================================================================
        $totalPetugas = DB::table('petugas')
            ->where('status', 'aktif')
            ->count();

        // ================================================================
        // 2. TOTAL PELANGGAN
        // ================================================================
        $totalPelanggan = DB::table('pelanggan')
            ->count();

        // ================================================================
        // 3. TOTAL TAGIHAN (SUM rptag dari pelanggan)
        // ================================================================
        $totalTagihan = DB::table('pelanggan')
            ->sum('rptag') ?? 0;

        // ================================================================
        // 4. TOTAL BERHASIL DITAGIH (SUM jumlah_bayar dari penagihan bulan ini)
        // ================================================================
        $totalTertagih = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'sudah')
            ->sum('jumlah_bayar') ?? 0;

        // ================================================================
        // 5. TOTAL BELUM DITAGIH (COUNT status = 'belum' bulan ini)
        // ================================================================
        $totalBelumTagih = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'belum')
            ->count();

        // ================================================================
        // 6. PERSENTASE KEBERHASILAN PENAGIHAN
        // ================================================================
        $totalPenagihanBulan = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->count();

        $persentaseTertagih = 0;
        if ($totalPenagihanBulan > 0) {
            $sudahTagih = DB::table('penagihan')
                ->where('bulan', $bulan)
                ->where('status', 'sudah')
                ->count();
            $persentaseTertagih = round(($sudahTagih / $totalPenagihanBulan) * 100, 1);
        }

        // ================================================================
        // 7. TOP 5 PETUGAS BERDASARKAN JUMLAH PELANGGAN BERHASIL DITAGIH
        // ================================================================
        $petugasTeratas = DB::table('petugas')
            ->leftJoin('penagihan', function($join) use ($bulan) {
                $join->on('penagihan.petugas_id', '=', 'petugas.id')
                     ->where('penagihan.bulan', $bulan)
                     ->where('penagihan.status', 'sudah');
            })
            ->leftJoin('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
            ->where('petugas.status', 'aktif')
            ->select(
                'petugas.id',
                'petugas.nama',
                'petugas.wilayah',
                DB::raw('COUNT(DISTINCT penagihan.idpel) as jumlah_tagihan'),
                DB::raw('COALESCE(SUM(penagihan.jumlah_bayar), 0) as total_tertagih'),
                DB::raw('COALESCE(SUM(pelanggan.rptag), 0) as total_rptag')
            )
            ->groupBy('petugas.id', 'petugas.nama', 'petugas.wilayah')
            ->orderByDesc('jumlah_tagihan')
            ->limit(5)
            ->get();

        // Hitung persentase untuk TOP petugas
        $petugasTeratas = $petugasTeratas->map(function($p) {
            $p->persentase = 0;
            if ($p->total_rptag > 0) {
                $p->persentase = round(($p->total_tertagih / $p->total_rptag) * 100, 1);
            }
            return $p;
        });

        // ================================================================
        // 8. DAFTAR WILAYAH
        // ================================================================
        $wilayahList = DB::table('petugas')
            ->where('status', 'aktif')
            ->select('wilayah')
            ->distinct()
            ->orderBy('wilayah')
            ->get();

        // ================================================================
        // 9. REKAP 6 BULAN TERAKHIR
        // ================================================================
        $rekapBulan = [];
        
        // Generate 6 bulan terakhir
        for ($i = 5; $i >= 0; $i--) {
            $dataBulan = Carbon::now()->subMonths($i)->format('Y-m');
            
            $totalRptag = DB::table('pelanggan')->sum('rptag') ?? 0;
            
            $totalTagihBulan = DB::table('penagihan')
                ->where('bulan', $dataBulan)
                ->where('status', 'sudah')
                ->sum('jumlah_bayar') ?? 0;
            
            $rekapBulan[] = [
                'bulan' => $dataBulan,
                'bulan_label' => Carbon::createFromFormat('Y-m', $dataBulan)->translatedFormat('M Y'),
                'total_rptag' => $totalRptag,
                'total_tagih' => $totalTagihBulan,
            ];
        }

        // ================================================================
        // 10. REKAP PER WILAYAH
        // ================================================================
        $rekapWilayah = DB::table('petugas')
            ->leftJoin('penagihan', function($join) use ($bulan) {
                $join->on('penagihan.petugas_id', '=', 'petugas.id')
                     ->where('penagihan.bulan', $bulan);
            })
            ->leftJoin('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
            ->where('petugas.status', 'aktif')
            ->select(
                'petugas.wilayah',
                DB::raw('COUNT(DISTINCT petugas.id) as total_petugas'),
                DB::raw('COUNT(DISTINCT pelanggan.idpel) as total_pelanggan'),
                DB::raw('COALESCE(SUM(penagihan.jumlah_bayar), 0) as total_tertagih')
            )
            ->groupBy('petugas.wilayah')
            ->orderBy('petugas.wilayah')
            ->get();

        // ================================================================
        // DATA UNTUK CHART
        // ================================================================
        
        // Chart: Status Penagihan (Pie Chart)
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

        // Chart: Top Petugas untuk Bar Chart
        $topPetugasChart = $petugasTeratas->map(function($p) {
            return [
                'nama' => substr($p->nama, 0, 10), // short name
                'jumlah' => (int)$p->jumlah_tagihan,
                'tertagih' => (int)$p->total_tertagih,
            ];
        })->values();

        // Chart: Trend 6 bulan
        $trendChart = array_map(function($r) {
            return [
                'bulan' => substr($r['bulan_label'], 0, 3),
                'total_rptag' => (int)$r['total_rptag'],
                'total_tagih' => (int)$r['total_tagih'],
            ];
        }, $rekapBulan);

        return view('admin.dashboard', compact(
            'bulan',
            'totalPetugas',
            'totalPelanggan',
            'totalTagihan',
            'totalTertagih',
            'totalBelumTagih',
            'persentaseTertagih',
            'petugasTeratas',
            'wilayahList',
            'rekapBulan',
            'rekapWilayah',
            'statusChart',
            'topPetugasChart',
            'trendChart'
        ));
    }
}
