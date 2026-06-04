<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PenagihanImportService
 *
 * Membaca file CSV hasil pembayaran PLN.
 * Hanya membaca 2 kolom:
 *   - Kolom B (index 1) = TglBuku  → format YYYYMMDD, contoh: 20260302
 *   - Kolom D (index 3) = Idpel    → contoh: 141000020529
 *
 * Yang terjadi saat import:
 *   1. Cari pelanggan berdasarkan Idpel
 *   2. Update pelanggan.status_penagihan = 'lunas'
 *   3. Insert/update tabel penagihan (updateOrInsert)
 */
class PenagihanImportService
{
    // Mapping kolom CSV (index berbasis 0)
    private const COL_TGLBUKU = 1; // Kolom B
    private const COL_IDPEL   = 3; // Kolom D

    /**
     * Parse CSV dan kembalikan hasil terstruktur untuk preview.
     *
     * @param  string $filePath  Path absolut ke file CSV
     * @return array{preview: array, valid: array, errors: array, stats: array, fatal: string|null}
     */
    public function processFile(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return $this->emptyResult('File tidak bisa dibuka.');
        }

        // Deteksi delimiter otomatis (titik koma atau koma)
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        // Skip baris header
        $header = fgetcsv($handle, 0, $delimiter);
        if (! $header) {
            fclose($handle);
            return $this->emptyResult('File CSV kosong atau tidak valid.');
        }

        // Ambil semua idpel yang ada di tabel pelanggan sekali saja
        // format: [ '141000020529' => true, ... ]
        $pelangganAda = DB::table('pelanggan')
            ->pluck('idpel')
            ->flip()
            ->all();

        $preview    = [];
        $valid      = [];
        $errors     = [];
        $seenInFile = []; // cegah duplikat dalam satu file
        $rowNumber  = 1;  // header sudah dilewati

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            // Lewati baris yang sepenuhnya kosong
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $data      = $this->extractRow($row);
            $rowErrors = [];

            // ── Validasi TglBuku ─────────────────────────────────────────────
            if ($data['tgl_buku'] === '') {
    $rowErrors[] = 'TglBuku kosong';
} elseif (! $this->parseTglBuku($data['tgl_buku'])) {
    $rowErrors[] = "TglBuku '{$data['tgl_buku']}' tidak valid";
}

            // ── Validasi Idpel ───────────────────────────────────────────────
            if ($data['idpel'] === '') {
                $rowErrors[] = 'Idpel kosong';
            } elseif (! isset($pelangganAda[$data['idpel']])) {
                $rowErrors[] = "Idpel '{$data['idpel']}' tidak ditemukan di tabel pelanggan";
            }

            // ── Cegah duplikat dalam file ────────────────────────────────────
            if ($data['idpel'] !== '' && isset($seenInFile[$data['idpel']])) {
                $rowErrors[] = "Idpel '{$data['idpel']}' duplikat dalam file ini";
            }

            // ── Kumpulkan hasil ──────────────────────────────────────────────
            $preview[] = array_merge($data, [
                '_row'    => $rowNumber,
                '_status' => empty($rowErrors) ? 'valid' : 'error',
                '_errors' => $rowErrors,
            ]);

            if (empty($rowErrors)) {
                $valid[]                    = $data;
                $seenInFile[$data['idpel']] = true;
            } else {
                foreach ($rowErrors as $msg) {
                    $errors[] = "Baris {$rowNumber} – {$msg}";
                }
            }
        }

        fclose($handle);

        return [
            'preview' => $preview,
            'valid'   => $valid,
            'errors'  => $errors,
            'stats'   => [
                'total'      => count($preview),
                'valid'      => count($valid),
                'error'      => count($errors),
                'error_rows' => count(array_filter($preview, fn($r) => $r['_status'] === 'error')),
            ],
            'fatal'   => null,
        ];
    }

    /**
     * Simpan baris valid ke database.
     *
     * Untuk setiap baris:
     *   1. Ambil petugas_id dan kode_petugas dari tabel pelanggan
     *   2. updateOrInsert ke tabel penagihan (idempoten — aman dijalankan ulang)
     *   3. Update pelanggan.status_penagihan = 'lunas'
     *
     * @param  array $validRows  Hasil dari processFile()['valid']
     * @return int   Jumlah baris yang berhasil diproses
     */
    public function saveToDatabase(array $validRows): int
    {
        if (empty($validRows)) {
            return 0;
        }

        $count = 0;
        $now   = now();

        DB::beginTransaction();

        try {
            foreach ($validRows as $r) {

                // Ambil data petugas dari pelanggan
                $pelanggan = DB::table('pelanggan')
                    ->where('idpel', $r['idpel'])
                    ->select('idpel', 'petugas_id', 'kode_petugas')
                    ->first();

                if (! $pelanggan) {
                    // Seharusnya tidak terjadi karena sudah divalidasi,
                    // tapi tetap kita skip dengan aman
                    Log::warning("[PENAGIHAN] Idpel {$r['idpel']} tidak ditemukan saat save, dilewati.");
                    continue;
                }

                // Bulan diambil dari TglBuku YYYYMMDD → format 'YYYY-MM'
                // Contoh: '20260302' → '2026-03'
                $bulan = substr($r['tgl_buku'], 0, 4) . '-' . substr($r['tgl_buku'], 4, 2);

                // Insert atau update baris penagihan
                // Unique key: idpel + bulan (satu pelanggan satu kali per bulan)
              DB::table('penagihan')->updateOrInsert(
[
    'idpel' => $r['idpel'],
    'bulan' => $bulan,
],
[
    'kode_petugas' => $pelanggan->kode_petugas,
    'petugas_id'   => $pelanggan->petugas_id,
    'status'       => 'lunas',
    'updated_at'   => $now,
    'created_at'   => $now,
]
);

                // Tandai pelanggan sebagai lunas
                DB::table('pelanggan')
                    ->where('idpel', $r['idpel'])
                    ->update([
                        'status_penagihan' => 'berhasil',
                        'updated_at'       => $now,
                    ]);

                $count++;
            }

            DB::commit();

            Log::info('[PENAGIHAN] Import selesai.', ['total' => $count]);

            return $count;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[PENAGIHAN] Import GAGAL: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Ekstrak kolom B (TglBuku) dan D (Idpel) dari satu baris CSV.
     *
     * Kembalikan:
     *   tgl_buku  — nilai mentah dari kolom B (untuk pesan error & ambil bulan)
     *   tgl_tagih — string 'Y-m-d' atau null jika format tidak valid
     *   idpel     — string angka bersih
     */
    private function extractRow(array $row): array
    {
        $get = fn(int $i) => isset($row[$i]) ? trim($row[$i]) : '';

        // ── Kolom B: TglBuku (YYYYMMDD) ─────────────────────────────────────
        $tglBuku  = $get(self::COL_TGLBUKU);
        $tglTagih = $this->parseTglBuku($tglBuku);

        // ── Kolom D: Idpel ───────────────────────────────────────────────────
        $rawIdpel = $get(self::COL_IDPEL);
        $idpel    = $this->normalizeIdpel($rawIdpel);

   return [
    'tgl_buku' => $tglBuku,
    'idpel'    => $idpel,
];
    }

    /**
     * Parse TglBuku format YYYYMMDD → string 'Y-m-d'.
     * Contoh: '20260302' → '2026-03-02'
     * Kembalikan null jika format tidak valid.
     */
    private function parseTglBuku(string $raw): ?string
    {
        // Harus tepat 8 digit angka
        if (! preg_match('/^\d{8}$/', $raw)) {
            return null;
        }

        $year  = substr($raw, 0, 4);
        $month = substr($raw, 4, 2);
        $day   = substr($raw, 6, 2);

        // Validasi kalender (cegah tanggal seperti 20261332)
        if (! checkdate((int) $month, (int) $day, (int) $year)) {
            return null;
        }

        return "{$year}-{$month}-{$day}";
    }

    /**
     * Bersihkan Idpel: hapus non-digit, tangani scientific notation Excel.
     * Contoh: '1.41E+11' → '141000000000'
     */
    private function normalizeIdpel(string $raw): string
    {
        if ($raw === '') {
            return '';
        }

        // Excel kadang mengekspor angka panjang sebagai scientific notation
        if (stripos($raw, 'E') !== false) {
            return number_format((float) $raw, 0, '', '');
        }

        return preg_replace('/[^0-9]/', '', $raw);
    }

    /**
     * Kembalikan struktur kosong dengan pesan error fatal.
     */
    private function emptyResult(string $fatalError = ''): array
    {
        return [
            'preview' => [],
            'valid'   => [],
            'errors'  => $fatalError ? [$fatalError] : [],
            'stats'   => ['total' => 0, 'valid' => 0, 'error' => 0, 'error_rows' => 0],
            'fatal'   => $fatalError,
        ];
    }
}