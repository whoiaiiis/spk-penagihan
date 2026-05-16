<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX - DAFTAR INVOICE (LANGSUNG DARI RANKING)
    |--------------------------------------------------------------------------
    | Menampilkan hasil ranking + data pelanggan secara langsung.
    | Tabel invoice HANYA digunakan untuk histori cetak.
    | Tidak ada filter bulan wajib — selalu tampilkan ranking terbaru.
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        try {
            // Query ranking + pelanggan + left join invoice untuk status cetak
            $query = DB::table('ranking')
                ->join('pelanggan', 'ranking.idpel', '=', 'pelanggan.idpel')
                ->leftJoin('invoice', 'ranking.idpel', '=', 'invoice.idpel')
                ->select(
                    'ranking.id as ranking_id',
                    'ranking.idpel',
                    'pelanggan.nama',
                    'pelanggan.daya',
                    'pelanggan.rptag',
                    'pelanggan.rpbk',
                    'ranking.ranking',
                    'ranking.nilai_akhir',
                    DB::raw('(pelanggan.rptag + pelanggan.rpbk) as total_tagihan'),
                    'invoice.id as invoice_id',
                    'invoice.nomor_surat',
                    'invoice.status_cetak',
                    'invoice.tanggal_cetak'
                );

            /*
            |--------------------------------------------------------------------------
            | FILTER OPSIONAL — RANKING
            |--------------------------------------------------------------------------
            */

            if ($request->filled('ranking_min')) {
                $query->where('ranking.ranking', '>=', (int) $request->ranking_min);
            }

            if ($request->filled('ranking_max')) {
                $query->where('ranking.ranking', '<=', (int) $request->ranking_max);
            }

            /*
            |--------------------------------------------------------------------------
            | FILTER OPSIONAL — STATUS CETAK
            |--------------------------------------------------------------------------
            */

            if ($request->filled('status_cetak')) {
                if ($request->status_cetak === 'sudah') {
                    $query->where('invoice.status_cetak', 'sudah');
                } elseif ($request->status_cetak === 'belum') {
                    $query->where(function ($q) {
                        $q->whereNull('invoice.status_cetak')
                          ->orWhere('invoice.status_cetak', 'belum');
                    });
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FILTER OPSIONAL — PENCARIAN
            |--------------------------------------------------------------------------
            */

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('pelanggan.nama', 'like', "%{$search}%")
                      ->orWhere('ranking.idpel', 'like', "%{$search}%");
                });
            }

            // Pagination — selalu urutkan berdasarkan ranking ASC
            $invoice = $query->orderBy('ranking.ranking', 'asc')->paginate(20)->withQueryString();

            return view('staff.invoice.index', compact('invoice'));
        } catch (\Exception $e) {
            Log::error('Index Invoice Error: ' . $e->getMessage());
            dd($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PRINT - CETAK INVOICE (GENERATE + PREVIEW)
    |--------------------------------------------------------------------------
    | Saat user klik PRINT:
    | 1. Generate invoice otomatis ke tabel invoice (jika belum ada)
    | 2. Update status_cetak = 'sudah'
    | 3. Tampilkan halaman cetak
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | AMBIL IDPEL YANG DIPILIH
            |--------------------------------------------------------------------------
            */

            $idpels = [];

            // Dari parameter 'ids' (comma-separated ranking IDs)
            if ($request->filled('ids')) {
                $rankingIds = array_filter(explode(',', $request->ids));
                $idpels = DB::table('ranking')
                    ->whereIn('id', $rankingIds)
                    ->pluck('idpel')
                    ->toArray();
            }

            // Dari parameter 'idpel' (single print)
            if ($request->filled('idpel')) {
                $idpels = [$request->idpel];
            }

            if (empty($idpels)) {
                return back()->with('warning', 'Pilih minimal 1 pelanggan untuk dicetak.');
            }

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | GENERATE INVOICE UNTUK IDPEL YANG BELUM ADA (TANPA BULAN FILTER)
            |--------------------------------------------------------------------------
            */

            $rankings = DB::table('ranking')
                ->join('pelanggan', 'ranking.idpel', '=', 'pelanggan.idpel')
                ->whereIn('ranking.idpel', $idpels)
                ->select(
                    'ranking.id as ranking_id',
                    'ranking.idpel',
                    'ranking.ranking',
                    'ranking.nilai_akhir',
                    'pelanggan.nama',
                    'pelanggan.daya',
                    'pelanggan.rptag',
                    'pelanggan.rpbk'
                )
                ->orderBy('ranking.ranking', 'asc')
                ->get();

            if ($rankings->isEmpty()) {
                DB::rollBack();
                return back()->with('warning', 'Data ranking tidak ditemukan.');
            }

            foreach ($rankings as $item) {

                // Cek apakah invoice sudah ada untuk idpel ini
                $exists = DB::table('invoice')
                    ->where('idpel', $item->idpel)
                    ->exists();

                if (!$exists) {

                    /*
                    |--------------------------------------------------------------------------
                    | AUTO GENERATE NOMOR SURAT
                    | Format: PAAANAG{ranking}/01/AGA.01.01/{ddmmyyyy}
                    |--------------------------------------------------------------------------
                    */

                    $rankingPadded = str_pad($item->ranking, 4, '0', STR_PAD_LEFT);
                    $datePadded = Carbon::now()->format('dmY');
                    $nomorSurat = 'PAAANAG' . $rankingPadded . '/01/AGA.01.01/' . $datePadded;

                    $totalTagihan = ($item->rptag ?? 0) + ($item->rpbk ?? 0);

                    DB::table('invoice')->insert([
                        'idpel'         => $item->idpel,
                        'nomor_surat'   => $nomorSurat,
                        'bulan'         => null,
                        'total_tagihan' => $totalTagihan,
                        'ranking'       => $item->ranking,
                        'status_cetak'  => 'sudah',
                        'tanggal_cetak' => now(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                } else {
                    // Update status cetak jika sudah ada
                    DB::table('invoice')
                        ->where('idpel', $item->idpel)
                        ->update([
                            'status_cetak'  => 'sudah',
                            'tanggal_cetak' => now(),
                            'updated_at'    => now(),
                        ]);
                }
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | AMBIL DATA INVOICE UNTUK DITAMPILKAN DI HALAMAN CETAK
            |--------------------------------------------------------------------------
            */

            $invoice = DB::table('invoice')
                ->join('pelanggan', 'invoice.idpel', '=', 'pelanggan.idpel')
                ->join('ranking', 'invoice.idpel', '=', 'ranking.idpel')
                ->whereIn('invoice.idpel', $idpels)
                ->select(
                    'invoice.id',
                    'invoice.idpel',
                    'invoice.nomor_surat',
                    'invoice.bulan',
                    'invoice.total_tagihan',
                    'invoice.ranking',
                    'invoice.status_cetak',
                    'invoice.tanggal_cetak',
                    'pelanggan.nama',
                    'pelanggan.daya',
                    'pelanggan.rptag',
                    'pelanggan.rpbk',
                    'ranking.nilai_akhir'
                )
                ->orderBy('ranking.ranking', 'asc')
                ->get();

            return view('staff.invoice.print', compact('invoice'));

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Print Invoice Error: ' . $e->getMessage());

            dd($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF - EXPORT INVOICE KE PDF
    |--------------------------------------------------------------------------
    | Sama seperti print, tapi output ke file PDF via DomPDF.
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | AMBIL IDPEL YANG DIPILIH
            |--------------------------------------------------------------------------
            */

            $idpels = [];

            if ($request->filled('ids')) {
                $rankingIds = array_filter(explode(',', $request->ids));
                $idpels = DB::table('ranking')
                    ->whereIn('id', $rankingIds)
                    ->pluck('idpel')
                    ->toArray();
            }

            if ($request->filled('idpel')) {
                $idpels = [$request->idpel];
            }

            if (empty($idpels)) {
                return back()->with('warning', 'Pilih minimal 1 pelanggan untuk di-export.');
            }

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | GENERATE INVOICE UNTUK IDPEL YANG BELUM ADA (TANPA BULAN FILTER)
            |--------------------------------------------------------------------------
            */

            $rankings = DB::table('ranking')
                ->join('pelanggan', 'ranking.idpel', '=', 'pelanggan.idpel')
                ->whereIn('ranking.idpel', $idpels)
                ->select(
                    'ranking.id as ranking_id',
                    'ranking.idpel',
                    'ranking.ranking',
                    'ranking.nilai_akhir',
                    'pelanggan.nama',
                    'pelanggan.daya',
                    'pelanggan.rptag',
                    'pelanggan.rpbk'
                )
                ->orderBy('ranking.ranking', 'asc')
                ->get();

            if ($rankings->isEmpty()) {
                DB::rollBack();
                return back()->with('warning', 'Data ranking tidak ditemukan.');
            }

            foreach ($rankings as $item) {

                $exists = DB::table('invoice')
                    ->where('idpel', $item->idpel)
                    ->exists();

                if (!$exists) {

                    $rankingPadded = str_pad($item->ranking, 4, '0', STR_PAD_LEFT);
                    $datePadded = Carbon::now()->format('dmY');
                    $nomorSurat = 'PAAANAG' . $rankingPadded . '/01/AGA.01.01/' . $datePadded;

                    $totalTagihan = ($item->rptag ?? 0) + ($item->rpbk ?? 0);

                    DB::table('invoice')->insert([
                        'idpel'         => $item->idpel,
                        'nomor_surat'   => $nomorSurat,
                        'bulan'         => null,
                        'total_tagihan' => $totalTagihan,
                        'ranking'       => $item->ranking,
                        'status_cetak'  => 'sudah',
                        'tanggal_cetak' => now(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                } else {
                    DB::table('invoice')
                        ->where('idpel', $item->idpel)
                        ->update([
                            'status_cetak'  => 'sudah',
                            'tanggal_cetak' => now(),
                            'updated_at'    => now(),
                        ]);
                }
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | AMBIL DATA INVOICE UNTUK PDF
            |--------------------------------------------------------------------------
            */

            $invoice = DB::table('invoice')
                ->join('pelanggan', 'invoice.idpel', '=', 'pelanggan.idpel')
                ->join('ranking', 'invoice.idpel', '=', 'ranking.idpel')
                ->whereIn('invoice.idpel', $idpels)
                ->select(
                    'invoice.id',
                    'invoice.idpel',
                    'invoice.nomor_surat',
                    'invoice.bulan',
                    'invoice.total_tagihan',
                    'invoice.ranking',
                    'invoice.status_cetak',
                    'invoice.tanggal_cetak',
                    'pelanggan.nama',
                    'pelanggan.daya',
                    'pelanggan.rptag',
                    'pelanggan.rpbk',
                    'ranking.nilai_akhir'
                )
                ->orderBy('ranking.ranking', 'asc')
                ->get();

            if ($invoice->isEmpty()) {
                return back()->with('warning', 'Tidak ada data invoice untuk di-export.');
            }

            /*
            |--------------------------------------------------------------------------
            | GENERATE PDF
            |--------------------------------------------------------------------------
            */

            $pdf = Pdf::loadView('staff.invoice.pdf', compact('invoice'))
                ->setPaper('a4')
                ->setOrientation('portrait');

            $filename = 'Invoice_' . Carbon::now()->format('YmdHis') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Export PDF Invoice Error: ' . $e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat export PDF: ' . $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE - BATCH GENERATE SEMUA INVOICE DARI RANKING
    |--------------------------------------------------------------------------
    */

    public function generate(Request $request)
    {
        try {

            DB::beginTransaction();

            $ranking = DB::table('ranking')
                ->join('pelanggan', 'ranking.idpel', '=', 'pelanggan.idpel')
                ->select(
                    'ranking.idpel',
                    'ranking.ranking',
                    'ranking.nilai_akhir',
                    'pelanggan.nama',
                    'pelanggan.daya',
                    'pelanggan.rptag',
                    'pelanggan.rpbk'
                )
                ->orderBy('ranking.ranking', 'asc')
                ->get();

            if ($ranking->count() <= 0) {
                DB::rollBack();
                return back()->with('error', 'Data ranking belum tersedia.');
            }

            $insert = [];
            $duplicateCount = 0;

            foreach ($ranking as $item) {

                /*
                |--------------------------------------------------------------------------
                | CEK DUPLIKAT BERDASARKAN IDPEL (TANPA BULAN FILTER)
                |--------------------------------------------------------------------------
                */

                $exists = DB::table('invoice')
                    ->where('idpel', $item->idpel)
                    ->exists();

                if ($exists) {
                    $duplicateCount++;
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | AUTO GENERATE NOMOR SURAT
                | Format: PAAANAG{ranking}/01/AGA.01.01/{ddmmyyyy}
                |--------------------------------------------------------------------------
                */

                $rankingPadded = str_pad($item->ranking, 4, '0', STR_PAD_LEFT);
                $datePadded = Carbon::now()->format('dmY');

                $nomorSurat = 'PAAANAG' . $rankingPadded . '/01/AGA.01.01/' . $datePadded;

                $totalTagihan = ($item->rptag ?? 0) + ($item->rpbk ?? 0);

                $insert[] = [
                    'idpel'         => $item->idpel,
                    'nomor_surat'   => $nomorSurat,
                    'bulan'         => null,
                    'total_tagihan' => $totalTagihan,
                    'ranking'       => $item->ranking,
                    'status_cetak'  => 'belum',
                    'tanggal_cetak' => null,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (count($insert) > 0) {

                foreach (array_chunk($insert, 100) as $chunk) {
                    DB::table('invoice')->insert($chunk);
                }

                DB::commit();

                $message = count($insert) . ' invoice berhasil dibuat.';

                if ($duplicateCount > 0) {
                    $message .= ' (' . $duplicateCount . ' duplikat terlewat)';
                }

                return back()->with('success', $message);
            }

            DB::rollBack();

            $message = 'Semua invoice sudah ada (' . $duplicateCount . ' duplikat).';

            return back()->with('warning', $message);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Generate Invoice Error: ' . $e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat generate invoice: ' . $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE - HAPUS INVOICE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {

            DB::beginTransaction();

            $invoice = DB::table('invoice')
                ->where('id', $id)
                ->first();

            if (!$invoice) {
                DB::rollBack();
                return back()->with('error', 'Invoice tidak ditemukan.');
            }

            DB::table('invoice')
                ->where('id', $id)
                ->delete();

            DB::commit();

            return back()->with('success', 'Invoice berhasil dihapus.');

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Delete Invoice Error: ' . $e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus invoice: ' . $e->getMessage()
            );
        }
    }
}