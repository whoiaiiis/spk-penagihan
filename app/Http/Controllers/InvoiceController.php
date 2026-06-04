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
    | KOLOM YANG DIPILIH — REUSABLE
    |--------------------------------------------------------------------------
    | Dipakai di index, print, exportPdf agar konsisten.
    | ✅ Tidak ada pelanggan.rbm (sudah dihapus dari skema)
    | ✅ Join petugas lewat pelanggan.kode_petugas (bukan petugas_id)
    |--------------------------------------------------------------------------
    */
    private function baseInvoiceSelect(): array
    {
        return [
            'invoice.id as invoice_id',
            'invoice.idpel',
            'invoice.nomor_surat',
            'invoice.bulan',
            'invoice.total_tagihan',
            'invoice.status_cetak',
            'invoice.tanggal_cetak',

            'pelanggan.nama',
            'pelanggan.daya',
            'pelanggan.rptag',
            'pelanggan.rpbk',
            'pelanggan.unitap',           // ✅ ganti rbm

            'petugas.kode_petugas',
            'petugas.nama as nama_petugas',

            'ranking.id as ranking_id',
            'ranking.ranking',
            'ranking.nilai_akhir',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX — DAFTAR INVOICE (LANGSUNG DARI RANKING)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $query = DB::table('ranking')
                ->join('pelanggan', 'ranking.idpel', '=', 'pelanggan.idpel')
                // ✅ Join petugas lewat kode_petugas, BUKAN petugas_id
                ->leftJoin('petugas', 'pelanggan.kode_petugas', '=', 'petugas.kode_petugas')
                ->leftJoin('invoice', 'ranking.idpel', '=', 'invoice.idpel')
                ->select(
                    'ranking.id as ranking_id',
                    'ranking.idpel',
                    'ranking.ranking',
                    'ranking.nilai_akhir',

                    'pelanggan.nama',
                    'pelanggan.daya',
                    'pelanggan.rptag',
                    'pelanggan.rpbk',
                    'pelanggan.unitap',       // ✅ ganti rbm

                    'petugas.kode_petugas',
                    'petugas.nama as nama_petugas',

                    DB::raw('(pelanggan.rptag + pelanggan.rpbk) as total_tagihan'),

                    'invoice.id as invoice_id',
                    'invoice.nomor_surat',
                    'invoice.status_cetak',
                    'invoice.tanggal_cetak'
                );

            // Filter ranking min
            if ($request->filled('ranking_min')) {
                $query->where('ranking.ranking', '>=', (int) $request->ranking_min);
            }

            // Filter ranking max
            if ($request->filled('ranking_max')) {
                $query->where('ranking.ranking', '<=', (int) $request->ranking_max);
            }

            // Filter status cetak
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

            // Filter pencarian
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('pelanggan.nama', 'like', "%{$search}%")
                      ->orWhere('ranking.idpel', 'like', "%{$search}%");
                });
            }

            // Filter petugas
            if ($request->filled('kode_petugas')) {
                $query->where('pelanggan.kode_petugas', $request->kode_petugas);
            }

            // Clone query to compute global stats
            $statsQuery = clone $query;
            $totalCount = $statsQuery->count();
            $sudahCount = (clone $query)->where('invoice.status_cetak', 'sudah')->count();
            $belumCount = $totalCount - $sudahCount;

            $stats = [
                'total' => $totalCount,
                'sudah' => $sudahCount,
                'belum' => $belumCount,
            ];

            $petugasList = DB::table('petugas')->orderBy('nama')->get();

            $invoice = $query
                ->orderBy('ranking.ranking', 'asc')
                ->paginate(20)
                ->withQueryString();

            return view('staff.invoice.index', compact('invoice', 'stats', 'petugasList'));

        } catch (\Exception $e) {
            Log::error('[INVOICE] Index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PRINT — CETAK INVOICE (GENERATE + PREVIEW HALAMAN CETAK)
    |--------------------------------------------------------------------------
    | 1. Tentukan idpels dari parameter ids (bulk) atau idpel (single)
    | 2. Generate invoice ke tabel jika belum ada
    | 3. Update status_cetak = 'sudah'
    | 4. Tampilkan halaman cetak
    |--------------------------------------------------------------------------
    */
    public function print(Request $request)
    {
        try {
            $idpels = $this->resolveIdpels($request);

            if (empty($idpels)) {
                return back()->with('warning', 'Pilih minimal 1 pelanggan untuk dicetak.');
            }

            DB::beginTransaction();

            $this->upsertInvoices($idpels, statusCetak: 'sudah');

            DB::commit();

            $invoice = $this->fetchInvoiceData($idpels);

            return view('staff.invoice.print', compact('invoice'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[INVOICE] Print error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat cetak: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF — EXPORT INVOICE KE FILE PDF
    |--------------------------------------------------------------------------
    */
    public function exportPdf(Request $request)
    {
        try {
            $idpels = $this->resolveIdpels($request);

            if (empty($idpels)) {
                return back()->with('warning', 'Pilih minimal 1 pelanggan untuk di-export.');
            }

            DB::beginTransaction();

            $this->upsertInvoices($idpels, statusCetak: 'sudah');

            DB::commit();

            $invoice = $this->fetchInvoiceData($idpels);

            if ($invoice->isEmpty()) {
                return back()->with('warning', 'Tidak ada data invoice untuk di-export.');
            }

            $pdf = Pdf::loadView('staff.invoice.pdf', compact('invoice'))
                ->setPaper('a4', 'portrait');

            $filename = 'Invoice_' . Carbon::now()->format('YmdHis') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[INVOICE] Export PDF error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat export PDF: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE — BATCH GENERATE SEMUA INVOICE DARI RANKING
    |--------------------------------------------------------------------------
    */
    public function generate(Request $request)
    {
        try {
            DB::beginTransaction();

            $rankings = DB::table('ranking')
                ->join('pelanggan', 'ranking.idpel', '=', 'pelanggan.idpel')
                ->select(
                    'ranking.idpel',
                    'ranking.ranking',
                    'pelanggan.rptag',
                    'pelanggan.rpbk'
                )
                ->orderBy('ranking.ranking', 'asc')
                ->get();

            if ($rankings->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'Data ranking belum tersedia.');
            }

            $insert         = [];
            $duplicateCount = 0;

            foreach ($rankings as $item) {
                $exists = DB::table('invoice')
                    ->where('idpel', $item->idpel)
                    ->exists();

                if ($exists) {
                    $duplicateCount++;
                    continue;
                }

                $nomorSurat   = $this->buildNomorSurat($item->ranking);
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

                $msg = count($insert) . ' invoice berhasil dibuat.';
                if ($duplicateCount > 0) {
                    $msg .= ' (' . $duplicateCount . ' sudah ada, dilewati)';
                }

                return back()->with('success', $msg);
            }

            DB::rollBack();
            return back()->with('warning', 'Semua invoice sudah ada (' . $duplicateCount . ' duplikat).');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[INVOICE] Generate error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat generate invoice: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY — HAPUS INVOICE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $exists = DB::table('invoice')->where('id', $id)->exists();

            if (!$exists) {
                DB::rollBack();
                return back()->with('error', 'Invoice tidak ditemukan.');
            }

            DB::table('invoice')->where('id', $id)->delete();

            DB::commit();

            return back()->with('success', 'Invoice berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[INVOICE] Destroy error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus invoice: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil array idpel dari parameter request.
     * ids = comma-separated ranking IDs (bulk)
     * idpel = single idpel
     */
    private function resolveIdpels(Request $request): array
    {
        if ($request->filled('ids')) {
            $rankingIds = array_filter(explode(',', $request->ids));
            return DB::table('ranking')
                ->whereIn('id', $rankingIds)
                ->pluck('idpel')
                ->toArray();
        }

        if ($request->filled('idpel')) {
            return [$request->idpel];
        }

        return [];
    }

    /**
     * Buat nomor surat otomatis.
     * Format: PAAANAG{0001}/01/AGA.01.01/{ddmmyyyy}
     */
    private function buildNomorSurat(int $ranking): string
    {
        return 'PAAANAG'
            . str_pad($ranking, 4, '0', STR_PAD_LEFT)
            . '/01/AGA.01.01/'
            . Carbon::now()->format('dmY');
    }

    /**
     * Insert invoice baru atau update status_cetak jika sudah ada.
     * Dipanggil dari print() dan exportPdf().
     */
    private function upsertInvoices(array $idpels, string $statusCetak = 'sudah'): void
    {
        $rankings = DB::table('ranking')
            ->join('pelanggan', 'ranking.idpel', '=', 'pelanggan.idpel')
            ->whereIn('ranking.idpel', $idpels)
            ->select(
                'ranking.idpel',
                'ranking.ranking',
                'pelanggan.rptag',
                'pelanggan.rpbk'
            )
            ->orderBy('ranking.ranking', 'asc')
            ->get();

        foreach ($rankings as $item) {
            $exists = DB::table('invoice')
                ->where('idpel', $item->idpel)
                ->exists();

            if (!$exists) {
                DB::table('invoice')->insert([
                    'idpel'         => $item->idpel,
                    'nomor_surat'   => $this->buildNomorSurat($item->ranking),
                    'bulan'         => null,
                    'total_tagihan' => ($item->rptag ?? 0) + ($item->rpbk ?? 0),
                    'ranking'       => $item->ranking,
                    'status_cetak'  => $statusCetak,
                    'tanggal_cetak' => $statusCetak === 'sudah' ? now() : null,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } else {
                DB::table('invoice')
                    ->where('idpel', $item->idpel)
                    ->update([
                        'status_cetak'  => $statusCetak,
                        'tanggal_cetak' => $statusCetak === 'sudah' ? now() : null,
                        'updated_at'    => now(),
                    ]);
            }
        }
    }

    /**
     * Ambil data lengkap invoice untuk ditampilkan di view cetak / PDF.
     * ✅ Join petugas lewat kode_petugas
     * ✅ Tidak ada kolom rbm
     */
    private function fetchInvoiceData(array $idpels)
    {
        return DB::table('invoice')
            ->join('pelanggan', 'invoice.idpel', '=', 'pelanggan.idpel')
            // ✅ join petugas lewat kode_petugas
            ->leftJoin('petugas', 'pelanggan.kode_petugas', '=', 'petugas.kode_petugas')
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
                'pelanggan.unitap',       

                'petugas.kode_petugas',
                'petugas.nama as nama_petugas',

                'ranking.nilai_akhir'
            )
            ->orderBy('ranking.ranking', 'asc')
            ->get();
    }
}