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
        // 3. TOTAL BERHASIL DITAGIH
        //    ✅ Diperbaiki: status 'sudah' → 'lunas'
        //    ✅ Diperbaiki: sum(jumlah_bayar) → join pelanggan + sum(rptag)
        // ================================================================
        $totalTertagih = DB::table('penagihan')
            ->join('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
            ->where('penagihan.bulan', $bulan)
            ->where('penagihan.status', 'lunas')
            ->sum('pelanggan.rptag') ?? 0;

        // ================================================================
        // 4. TOTAL BELUM DITAGIH
        //    ✅ Diperbaiki: status 'sudah' → 'lunas'
        // ================================================================
        $sudahTagihCount = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'lunas')
            ->count();

        $totalBelumTagih = $totalPelanggan - $sudahTagihCount;

        // ================================================================
        // 5. PERSENTASE KEBERHASILAN
        //    ✅ Diperbaiki: status 'sudah' → 'lunas'
        // ================================================================
        $persentaseKeberhasilan = 0;
        if ($totalPelanggan > 0) {
            $persentaseKeberhasilan = round(($sudahTagihCount / $totalPelanggan) * 100, 1);
        }

        // ================================================================
        // 6. TREN 6 BULAN TERAKHIR
        //    ✅ Diperbaiki: status 'sudah' → 'lunas'
        //    ✅ Diperbaiki: sum(jumlah_bayar) → join pelanggan + sum(rptag)
        //    ✅ Diperbaiki: ambil bulan yang benar-benar ada di tabel penagihan
        //       (mengikuti pola AdminController agar data sinkron)
        // ================================================================
        $bulanAda = DB::table('penagihan')
            ->select('bulan')
            ->distinct()
            ->orderBy('bulan', 'asc')
            ->pluck('bulan')
            ->toArray();

        // Ambil 6 bulan terakhir dari yang benar-benar ada
        $bulanAda = array_slice($bulanAda, -6);

        $trendBulan = [];
        foreach ($bulanAda as $dataBulan) {
            $targetBulan = DB::table('pelanggan')->sum('rptag') ?? 0;

            // ✅ join pelanggan + sum(rptag) + status lunas
            $terbayarBulan = DB::table('penagihan')
                ->join('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
                ->where('penagihan.bulan', $dataBulan)
                ->where('penagihan.status', 'lunas')
                ->sum('pelanggan.rptag') ?? 0;

            $trendBulan[] = [
                'bulan'        => $dataBulan,
                'bulan_label'  => Carbon::createFromFormat('Y-m', $dataBulan)->translatedFormat('M Y'),
                'target'       => (int)$targetBulan,
                'terbayar'     => (int)$terbayarBulan,
            ];
        }

        // ================================================================
        // 7. TOP 5 PELANGGAN TAGIHAN TERBESAR
        //    ✅ Diperbaiki: cek status penagihan pakai 'lunas' bukan 'sudah'
        // ================================================================
        $topPelanggan = DB::table('pelanggan')
            ->orderByDesc('rptag')
            ->limit(5)
            ->get()
            ->map(function($p) use ($bulan) {
                $penagihan = DB::table('penagihan')
                    ->where('idpel', $p->idpel)
                    ->where('bulan', $bulan)
                    ->first();

                // ✅ status 'lunas' bukan 'sudah', tampilkan rptag sebagai jumlah_bayar
                $p->status_tagih = ($penagihan && $penagihan->status === 'lunas') ? 'sudah' : 'belum';
                $p->jumlah_bayar = ($penagihan && $penagihan->status === 'lunas') ? $p->rptag : 0;

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
        //    ✅ Diperbaiki: status 'sudah' → 'lunas'
        // ================================================================
        $lunasCount = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'lunas')
            ->count();

        $statusChart = [
            'sudah' => $lunasCount,
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
                'bulan'    => substr($t['bulan_label'], 0, 3),
                'target'   => (int)$t['target'],
                'terbayar' => (int)$t['terbayar'],
            ];
        }, $trendBulan);

        // Top pelanggan chart data
        $topPelangganChartData = $topPelanggan->map(function($p) {
            return [
                'nama'  => substr($p->nama, 0, 12),
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