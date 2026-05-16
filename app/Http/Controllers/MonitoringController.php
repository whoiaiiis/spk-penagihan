<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Tampilkan halaman monitoring kinerja
     */
    public function index(Request $request)
    {
        $bulan   = $request->get('bulan', date('Y-m'));
        $wilayah = $request->get('wilayah', '');

        // Daftar wilayah untuk filter
        $wilayahList = DB::table('petugas')
            ->select('wilayah')->distinct()->orderBy('wilayah')->get();

        // Query kinerja per petugas
        $query = DB::table('petugas')
            ->leftJoin('penagihan', function ($join) use ($bulan) {
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
                DB::raw('COUNT(DISTINCT penagihan.idpel) as total_ditagih'),
                DB::raw('SUM(CASE WHEN penagihan.status = "sudah" THEN 1 ELSE 0 END) as sudah_tagih'),
                DB::raw('SUM(CASE WHEN penagihan.status = "belum" THEN 1 ELSE 0 END) as belum_tagih'),
                DB::raw('COALESCE(SUM(pelanggan.rptag), 0) as total_rptag'),
                DB::raw('COALESCE(SUM(penagihan.jumlah_bayar), 0) as total_tagih')
            )
            ->groupBy('petugas.id', 'petugas.nama', 'petugas.wilayah', 'petugas.kode_petugas');

        if ($wilayah) {
            $query->where('petugas.wilayah', $wilayah);
        }

        $kinerja = $query->get()->map(function ($row) {
            $row->sisa_saldo = max(0, $row->total_rptag - $row->total_tagih);
            $row->persen = $row->total_ditagih > 0
                ? round(($row->sudah_tagih / $row->total_ditagih) * 100, 1)
                : 0;

            if ($row->persen >= 80) {
                $row->kategori = 'Baik';
            } elseif ($row->persen >= 60) {
                $row->kategori = 'Cukup';
            } else {
                $row->kategori = 'Kurang';
            }

            return $row;
        });

        // Urutkan berdasarkan kinerja terbaik
        $kinerja = $kinerja->sortByDesc('persen')->values();

        // Rekapitulasi total
        $summary = [
            'total_petugas' => $kinerja->count(),
            'petugas_baik'  => $kinerja->where('kategori', 'Baik')->count(),
            'petugas_cukup' => $kinerja->where('kategori', 'Cukup')->count(),
            'petugas_kurang'=> $kinerja->where('kategori', 'Kurang')->count(),
            'total_target'  => $kinerja->sum('total_ditagih'),
            'total_sudah'   => $kinerja->sum('sudah_tagih'),
            'total_belum'   => $kinerja->sum('belum_tagih'),
            'total_rptag'    => $kinerja->sum('total_rptag'),
            'total_tagih'   => $kinerja->sum('total_tagih'),
            'total_sisa'    => $kinerja->sum('sisa_saldo'),
        ];
        $summary['avg_kinerja'] = $summary['total_target'] > 0
            ? round(($summary['total_sudah'] / $summary['total_target']) * 100, 1)
            : 0;

        $chartData = $kinerja->map(fn($r) => [
            'nama'    => $r->nama,
            'persen'  => $r->persen,
            'sudah'   => $r->sudah_tagih,
            'belum'   => $r->belum_tagih,
            'tagih'   => $r->total_tagih,
            'sisa'    => $r->sisa_saldo,
        ])->values()->all();

        return view('supervisor.monitoringkinerja', compact(
    'kinerja',
    'wilayahList',
    'bulan',
    'wilayah',
    'summary',
    'chartData'
));
    }

    /**
     * Detail drill-down per petugas
     */
    public function detail(Request $request, $id)
    {
        $bulan   = $request->get('bulan', date('Y-m'));
        $petugas = DB::table('petugas')->where('id', $id)->first();

        if (!$petugas) {
            return redirect()->route('monitoring.monitoringkinerja')->with('error', 'Data petugas tidak ditemukan.');
        }

        // Semua pelanggan beserta status penagihan petugas ini bulan ini
        $detail = DB::table('penagihan')
            ->join('pelanggan', 'penagihan.idpel', '=', 'pelanggan.idpel')
            ->where('penagihan.petugas_id', $id)
            ->where('penagihan.bulan', $bulan)
            ->select(
                'pelanggan.idpel',
                'pelanggan.nama',
                'pelanggan.daya',
                'pelanggan.lembar',
                'pelanggan.rptag',
                'pelanggan.rpbk',
                DB::raw('COALESCE(penagihan.status, "belum") as status'),
                DB::raw('penagihan.tanggal_tagih as tgl_tagih'),
                DB::raw('COALESCE(penagihan.jumlah_bayar, 0) as jumlah_tagih'),
                'penagihan.keterangan'
            )
            ->orderByRaw('COALESCE(penagihan.status, "belum") ASC')
            ->get();

        $total_rptag = $detail->sum('rptag');
        $total_tagih = $detail->sum('jumlah_tagih');

        $stats = [
            'total'       => $detail->count(),
            'sudah'       => $detail->where('status', 'sudah')->count(),
            'belum'       => $detail->where('status', 'belum')->count(),
            'total_rptag' => $total_rptag,
            'total_tagih' => $total_tagih,
            'sisa_saldo'  => $total_rptag - $total_tagih,
            'persen'      => $detail->count() > 0
                ? round(($detail->where('status', 'sudah')->count() / $detail->count()) * 100, 1)
                : 0,
        ];

        return view('supervisor.detailkinerja', compact('petugas', 'detail', 'stats', 'bulan'));
    }
}
