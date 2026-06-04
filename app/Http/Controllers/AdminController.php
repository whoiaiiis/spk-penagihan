<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Dashboard Admin — Monitoring Penagihan
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
        $totalPelanggan = DB::table('pelanggan')->count();

        // ================================================================
        // 3. TOTAL TAGIHAN — SUM rptag seluruh pelanggan
        // ================================================================
        $totalTagihan = DB::table('pelanggan')->sum('rptag') ?? 0;

        // ================================================================
        // 4. TOTAL BERHASIL DITAGIH
        //    → SUM rptag pelanggan yang statusnya 'lunas' bulan ini
        //    ✅ Tidak pakai jumlah_bayar (NULL), pakai pelanggan.rptag
        // ================================================================
        $totalTertagih = DB::table('penagihan')
            ->join('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
            ->where('penagihan.bulan', $bulan)
            ->where('penagihan.status', 'lunas')
            ->sum('pelanggan.rptag') ?? 0;

        // ================================================================
        // 5. TOTAL BELUM DITAGIH — COUNT status = 'belum' bulan ini
        // ================================================================
        $totalBelumTagih = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'belum')
            ->count();

        // ================================================================
        // 6. PERSENTASE KEBERHASILAN PENAGIHAN
        //    → lunas bulan ini / total pelanggan keseluruhan
        //    ✅ Bukan dibagi total penagihan saja (yang bisa 3 data)
        // ================================================================
        $sudahTagihBulan = DB::table('penagihan')
            ->where('bulan', $bulan)
            ->where('status', 'lunas')
            ->count();

        $persentaseTertagih = 0;
        if ($totalPelanggan > 0) {
            $persentaseTertagih = round(($sudahTagihBulan / $totalPelanggan) * 100, 2);
        }

        // ================================================================
        // 7. TOP 5 PETUGAS
        //    → berdasarkan jumlah pelanggan lunas & total rptag lunas
        //    ✅ Join petugas lewat kode_petugas (bukan petugas_id)
        // ================================================================
        $petugasTeratas = DB::table('petugas')
            ->leftJoin('pelanggan', 'pelanggan.kode_petugas', '=', 'petugas.kode_petugas') // ✅
            ->leftJoin('penagihan', function ($join) use ($bulan) {
                $join->on('penagihan.idpel', '=', 'pelanggan.idpel')
                     ->where('penagihan.bulan', $bulan)
                     ->where('penagihan.status', 'lunas');
            })
            ->where('petugas.status', 'aktif')
            ->select(
                'petugas.id',
                'petugas.kode_petugas',
                'petugas.nama',
                'petugas.wilayah',

                // Jumlah pelanggan yang berhasil lunas bulan ini
                DB::raw('COUNT(DISTINCT penagihan.idpel) as jumlah_tagihan'),

                // Total rptag pelanggan yang lunas
                DB::raw("
                    COALESCE(
                        SUM(
                            CASE
                                WHEN penagihan.status = 'lunas'
                                THEN pelanggan.rptag
                                ELSE 0
                            END
                        ), 0
                    ) as total_tertagih
                "),

                // Total rptag semua pelanggan petugas ini (pembagi persentase)
                DB::raw('COALESCE(SUM(pelanggan.rptag), 0) as total_rptag')
            )
            ->groupBy('petugas.id', 'petugas.kode_petugas', 'petugas.nama', 'petugas.wilayah')
            ->orderByDesc('jumlah_tagihan')
            ->limit(5)
            ->get();

        // Hitung persentase per petugas
        $petugasTeratas = $petugasTeratas->map(function ($p) {
            $p->persentase = 0;
            if ($p->total_rptag > 0) {
                $p->persentase = round(($p->total_tertagih / $p->total_rptag) * 100, 1);
            }
            return $p;
        });

        // ================================================================
        // 8. DAFTAR WILAYAH AKTIF
        // ================================================================
        $wilayahList = DB::table('petugas')
            ->where('status', 'aktif')
            ->select('wilayah')
            ->distinct()
            ->orderBy('wilayah')
            ->get();

        // ================================================================
        // 9. REKAP 6 BULAN TERAKHIR
        //    ✅ Ambil bulan yang BENAR-BENAR ada di tabel penagihan
        //       bukan loop statis 6 bulan terakhir dari Carbon::now()
        // ================================================================
        $bulanAda = DB::table('penagihan')
            ->select('bulan')
            ->distinct()
            ->orderBy('bulan', 'asc')
            ->pluck('bulan')
            ->toArray();

        // Ambil 6 bulan terakhir dari yang benar-benar ada
        $bulanAda = array_slice($bulanAda, -6);

        $rekapBulan = [];

        foreach ($bulanAda as $bln) {
            // Total rptag semua pelanggan (tetap, tidak berubah per bulan)
            $totalRptag = DB::table('pelanggan')->sum('rptag') ?? 0;

            // Total rptag pelanggan yang lunas bulan ini
            // ✅ Tidak pakai jumlah_bayar (NULL), pakai pelanggan.rptag
            $totalTagihBulan = DB::table('penagihan')
                ->join('pelanggan', 'pelanggan.idpel', '=', 'penagihan.idpel')
                ->where('penagihan.bulan', $bln)
                ->where('penagihan.status', 'lunas')
                ->sum('pelanggan.rptag') ?? 0;

            $rekapBulan[] = [
                'bulan'       => $bln,
                'bulan_label' => Carbon::createFromFormat('Y-m', $bln)->translatedFormat('M Y'),
                'total_rptag' => (int) $totalRptag,
                'total_tagih' => (int) $totalTagihBulan,
            ];
        }

        // ================================================================
        // 10. REKAP PER WILAYAH
        //     ✅ Join petugas lewat kode_petugas (bukan petugas_id)
        // ================================================================
        $rekapWilayah = DB::table('petugas')
            ->leftJoin('pelanggan', 'pelanggan.kode_petugas', '=', 'petugas.kode_petugas') // ✅
            ->leftJoin('penagihan', function ($join) use ($bulan) {
                $join->on('penagihan.idpel', '=', 'pelanggan.idpel')
                     ->where('penagihan.bulan', $bulan);
            })
            ->where('petugas.status', 'aktif')
            ->select(
                'petugas.wilayah',
                DB::raw('COUNT(DISTINCT petugas.id) as total_petugas'),
                DB::raw('COUNT(DISTINCT pelanggan.idpel) as total_pelanggan'),

                // ✅ Pakai pelanggan.rptag, bukan jumlah_bayar
                DB::raw("
                    COALESCE(
                        SUM(
                            CASE
                                WHEN penagihan.status = 'lunas'
                                THEN pelanggan.rptag
                                ELSE 0
                            END
                        ), 0
                    ) as total_tertagih
                ")
            )
            ->groupBy('petugas.wilayah')
            ->orderBy('petugas.wilayah')
            ->get();

        // ================================================================
        // DATA UNTUK CHART
        // ================================================================

        // Pie Chart: Status Penagihan bulan ini
        $statusChart = [
            'sudah' => DB::table('penagihan')
                ->where('bulan', $bulan)
                ->where('status', 'lunas')
                ->count(),
            'belum' => DB::table('penagihan')
                ->where('bulan', $bulan)
                ->where('status', 'belum')
                ->count(),
        ];

        // Bar Chart: Top 5 Petugas
        $topPetugasChart = $petugasTeratas->map(function ($p) {
            return [
                'nama'     => Str::limit($p->nama, 12, ''),
                'jumlah'   => (int) $p->jumlah_tagihan,
                'tertagih' => (int) $p->total_tertagih,
            ];
        })->values();

        // Line Chart: Trend 6 bulan (dari $rekapBulan yang sudah benar)
        $trendChart = array_map(function ($r) {
            return [
                'bulan'       => substr($r['bulan_label'], 0, 3),
                'total_rptag' => (int) $r['total_rptag'],
                'total_tagih' => (int) $r['total_tagih'],
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