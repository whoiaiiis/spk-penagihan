<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
    /**
     * Dashboard Staff - Monitoring Penagihan
     */
    public function index(Request $request)
    {
        // Filter bulan (default: bulan saat ini)
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));

        // ================================================================
        // 1. TOTAL PELANGGAN
        // ================================================================
        $totalPelanggan = DB::table('pelanggan')->count();

        // ================================================================
        // 2. TOTAL TAGIHAN BULAN BERJALAN (SUM rptag dari semua pelanggan)
        // ================================================================
        $totalTagihan = DB::table('pelanggan')->sum('rptag') ?? 0;

        // ================================================================
        // 3. TOTAL BERHASIL DITAGIH (SUM jumlah_bayar status = sudah bulan ini)
        // ================================================================
        $totalTertagih = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'sudah')
            ->sum('jumlah_bayar') ?? 0;

        // ================================================================
        // 4. TOTAL BELUM DITAGIH (COUNT status = 'belum' bulan ini)
        // ================================================================
        $totalBelumTagih = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'belum')
            ->count();

        // ================================================================
        // 5. PERSENTASE KEBERHASILAN (sudah / total tagihan bulan ini) * 100
        // ================================================================
        $totalPenagihanBulan = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->count();

        $persentaseKeberhasilan = 0;
        if ($totalPenagihanBulan > 0) {
            $sudahTagih = DB::table('penagihan')
                ->where('bulan', $bulan)
                ->where('status', 'sudah')
                ->count();
            $persentaseKeberhasilan = round(($sudahTagih / $totalPenagihanBulan) * 100, 1);
        }

        // ================================================================
        // 6. TREN 6 BULAN TERAKHIR
        // ================================================================
        $trendBulan = [];
        for ($i = 5; $i >= 0; $i--) {
            $dataBulan = Carbon::now()->subMonths($i)->format('Y-m');
            
            $targetBulan = DB::table('pelanggan')->sum('rptag') ?? 0;
            
            $terbayarBulan = DB::table('penagihan')
                ->where('bulan', $dataBulan)
                ->where('status', 'sudah')
                ->sum('jumlah_bayar') ?? 0;
            
            $trendBulan[] = [
                'bulan' => $dataBulan,
                'bulan_label' => Carbon::createFromFormat('Y-m', $dataBulan)->translatedFormat('M Y'),
                'target' => $targetBulan,
                'terbayar' => $terbayarBulan,
            ];
        }

        // ================================================================
        // 7. TOP 5 PELANGGAN TAGIHAN TERBESAR
        // ================================================================
        $topPelanggan = DB::table('pelanggan')
            ->orderByDesc('rptag')
            ->limit(5)
            ->get()
            ->map(function($p) use ($bulan) {
                // Cari status penagihan untuk bulan ini
                $penagihan = DB::table('penagihan')
                    ->where('idpel', $p->idpel)
                    ->where('bulan', $bulan)
                    ->first();
                
                $p->status_tagih = $penagihan->status ?? 'belum';
                $p->jumlah_bayar = $penagihan->jumlah_bayar ?? 0;
                
                return $p;
            });

        // ================================================================
        // 8. AKTIVITAS PENAGIHAN TERBARU (10 record terakhir)
        // ================================================================
        $aktivitasTerbaru = DB::table('penagihan')
            ->join('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
            ->where('penagihan.bulan', $bulan)
            ->select(
                'penagihan.id',
                'pelanggan.idpel',
                'pelanggan.nama',
                'pelanggan.rptag',
                'penagihan.status',
                'penagihan.jumlah_bayar',
                'penagihan.tanggal_tagih',
                'penagihan.created_at'
            )
            ->orderByDesc('penagihan.updated_at')
            ->limit(10)
            ->get();

        // ================================================================
        // 9. REKAP STATUS TAGIHAN (Pie chart data)
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
        // CHART DATA FOR CHART.JS
        // ================================================================
        
        // Trend chart data
        $trendChartData = array_map(function($t) {
            return [
                'bulan' => substr($t['bulan_label'], 0, 3),
                'target' => (int)$t['target'],
                'terbayar' => (int)$t['terbayar'],
            ];
        }, $trendBulan);

        // Top pelanggan chart data
        $topPelangganChartData = $topPelanggan->map(function($p) {
            return [
                'nama' => substr($p->nama, 0, 12),
                'rptag' => (int)$p->rptag,
            ];
        })->values();

        return view('staff.dashboard', compact(
            'bulan',
            'totalPelanggan',
            'totalTagihan',
            'totalTertagih',
            'totalBelumTagih',
            'persentaseKeberhasilan',
            'trendBulan',
            'topPelanggan',
            'aktivitasTerbaru',
            'statusChart',
            'trendChartData',
            'topPelangganChartData'
        ));
    }
}
