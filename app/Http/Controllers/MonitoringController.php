<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MonitoringController extends Controller
{
    /**
     * QUERY REUSABLE MONITORING
     */
    private function getMonitoringData($bulan)
    {
        // List wilayah
        $wilayahList = DB::table('petugas')
            ->select('wilayah')
            ->distinct()
            ->orderBy('wilayah')
            ->get();

        // Query utama monitoring
        $query = DB::table('petugas')
            ->leftJoin('pelanggan', 'pelanggan.petugas_id', '=', 'petugas.id')
            ->leftJoin('penagihan', function ($join) use ($bulan) {
                $join->on('penagihan.idpel', '=', 'pelanggan.idpel')
                     ->where('penagihan.bulan', $bulan);
            })
            ->where('petugas.status', 'aktif')
            ->select(
                'petugas.id',
                'petugas.nama',
                'petugas.wilayah',
                'petugas.kode_petugas',

                DB::raw('COUNT(DISTINCT pelanggan.idpel) as total_ditagih'),

                DB::raw('SUM(
                    CASE 
                        WHEN penagihan.status = "lunas"
                        THEN 1 ELSE 0
                    END
                ) as sudah_tagih'),

                DB::raw('COUNT(DISTINCT pelanggan.idpel) - SUM(
                    CASE 
                        WHEN penagihan.status = "lunas"
                        THEN 1 ELSE 0
                    END
                ) as belum_tagih'),

                DB::raw('COALESCE(SUM(pelanggan.rptag), 0) as total_rptag'),

                DB::raw('SUM(
    CASE
        WHEN penagihan.status = "lunas"
        THEN pelanggan.rptag
        ELSE 0
    END
) as total_tagih')
            )
            ->groupBy(
                'petugas.id',
                'petugas.nama',
                'petugas.wilayah',
                'petugas.kode_petugas'
            );

        // Data monitoring
        $kinerja = $query->get()->map(function ($row) {

            $row->sisa_saldo =
                max(0, $row->total_rptag - $row->total_tagih);

            $row->persen =
                $row->total_ditagih > 0
                    ? round(
                        ($row->sudah_tagih / $row->total_ditagih) * 100,
                        1
                    )
                    : 0;

            // Kategori
            if ($row->persen >= 80) {
                $row->kategori = 'Baik';
            } elseif ($row->persen >= 60) {
                $row->kategori = 'Cukup';
            } else {
                $row->kategori = 'Kurang';
            }

            return $row;
        });

        // Ranking terbaik
        $kinerja = $kinerja->sortByDesc('persen')->values();

        // Summary dashboard
        $summary = [
            'total_petugas' => $kinerja->count(),

            'petugas_baik'  => $kinerja->where('kategori', 'Baik')->count(),

            'petugas_cukup' => $kinerja->where('kategori', 'Cukup')->count(),

            'petugas_kurang'=> $kinerja->where('kategori', 'Kurang')->count(),

            'total_target'  => $kinerja->sum('total_ditagih'),

            'total_sudah'   => $kinerja->sum('sudah_tagih'),

            'total_belum'   => $kinerja->sum('belum_tagih'),

            'total_rptag'   => $kinerja->sum('total_rptag'),

            'total_tagih'   => $kinerja->sum('total_tagih'),

            'total_sisa'    => $kinerja->sum('sisa_saldo'),
        ];

        // Persentase rata-rata
        $summary['avg_kinerja'] =
            $summary['total_target'] > 0
                ? round(
                    ($summary['total_sudah'] / $summary['total_target']) * 100,
                    1
                )
                : 0;

        // Data chart
        $chartData = $kinerja->map(function ($r) {
            return [
                'nama'   => $r->nama,
                'persen' => $r->persen,
                'sudah'  => $r->sudah_tagih,
                'belum'  => $r->belum_tagih,
                'tagih'  => $r->total_tagih,
                'sisa'   => $r->sisa_saldo,
            ];
        })->values()->all();

        return [
            'kinerja'     => $kinerja,
            'summary'     => $summary,
            'chartData'   => $chartData,
            'wilayahList' => $wilayahList,
        ];
    }


    /**
     * HALAMAN MONITORING
     */
    public function index(Request $request)
    {
        $bulan   = $request->get('bulan', date('Y-m'));
      $data = $this->getMonitoringData($bulan);

       return view(
    'supervisor.monitoringkinerja',
    array_merge($data, [
        'bulan' => $bulan
    ])
);
    }

    /**
     * DETAIL PETUGAS
     */
    public function detail(Request $request, $id)
    {
        $bulan = $request->get('bulan', date('Y-m'));

        // Cari petugas
        $petugas = DB::table('petugas')
            ->where('id', $id)
            ->first();

        if (!$petugas) {
            return redirect()
                ->route('monitoring.index')
                ->with(
                    'error',
                    'Data petugas tidak ditemukan.'
                );
        }

        // Detail pelanggan
        $detail = DB::table('pelanggan')
            ->leftJoin('penagihan', function ($join) use ($bulan) {
                $join->on('penagihan.idpel', '=', 'pelanggan.idpel')
                     ->where('penagihan.bulan', $bulan);
            })
            ->where('pelanggan.petugas_id', $id)
            ->select(
                'pelanggan.idpel',
                'pelanggan.nama',
                'pelanggan.unitap',
                'pelanggan.daya',
                'pelanggan.lembar',
                'pelanggan.rptag',
                'pelanggan.rpbk',

                DB::raw('
                    COALESCE(
                        penagihan.status,
                        "belum"
                    ) as status
                '),

                DB::raw('
                    penagihan.tanggal_tagih as tgl_tagih
                '),

                DB::raw('
                    COALESCE(
                        penagihan.jumlah_bayar,
                        0
                    ) as jumlah_tagih
                '),

                'penagihan.keterangan'
            )
            ->orderByRaw('
                COALESCE(
                    penagihan.status,
                    "belum"
                ) ASC
            ')
            ->get();

        // Statistik detail
        $total_rptag = $detail->sum('rptag');
        $total_tagih = $detail->sum('jumlah_tagih');

        $stats = [
            'total'       => $detail->count(),

            'sudah'       => $detail
                ->where('status', 'lunas')
                ->count(),

            'belum'       => $detail
                ->where('status', 'belum')
                ->count(),

            'total_rptag' => $total_rptag,

            'total_tagih' => $total_tagih,

            'sisa_saldo'  => $total_rptag - $total_tagih,

            'persen'      => $detail->count() > 0
                ? round(
                    (
                        $detail->where('status', 'lunas')->count()
                        / $detail->count()
                    ) * 100,
                    1
                )
                : 0,
        ];

        return view(
            'supervisor.detailkinerja',
            compact(
                'petugas',
                'detail',
                'stats',
                'bulan'
            )
        );
    }

    /**
     * EXPORT PDF MONITORING
     */
    public function exportPdf(Request $request)
    {
        $bulan   = $request->get('bulan', date('Y-m'));
        $wilayah = $request->get('wilayah', '');

        $data = $this->getMonitoringData($bulan, $wilayah);

        // Jika kosong
        if ($data['kinerja']->count() <= 0) {
            return back()->with(
                'error',
                'Data monitoring tidak tersedia.'
            );
        }

        // Generate PDF
        $pdf = Pdf::loadView(
            'supervisor.monitoring_pdf',
            array_merge($data, [
                'bulan'   => $bulan,
                'wilayah' => $wilayah,
                'tanggal' => now(),
            ])
        )->setPaper('a4', 'landscape');

        // Nama file
        $filename =
            'monitoring-kinerja-petugas-' .
            $bulan .
            '.pdf';

        return $pdf->download($filename);
    }
}