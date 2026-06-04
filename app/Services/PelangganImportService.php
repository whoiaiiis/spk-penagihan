<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PelangganImportService
 *
 * Handles CSV parsing, row-level validation, and batch database upsert
 * for pelanggan data (format PLN). Uses native PHP fgetcsv — no external packages.
 *
 * ┌─────────────────────────────────────────────────────┐
 * │  FORMAT CSV PLN — MAPPING POSISI KOLOM              │
 * ├──────┬──────────┬──────────────────────────────────┤
 * │ Col  │ Index    │ Keterangan                        │
 * ├──────┼──────────┼──────────────────────────────────┤
 * │ A    │ 0        │ UNITAP                            │
 * │ B    │ 1        │ UNITUP (tidak dipakai)            │
 * │ C    │ 2        │ IDPEL                             │
 * │ D    │ 3        │ NAMA                              │
 * │ E    │ 4        │ (tidak dipakai)                   │
 * │ F    │ 5        │ DAYA                              │
 * │ G    │ 6        │ KOGOL (tidak dipakai)             │
 * │ H    │ 7        │ GARDU (tidak dipakai)             │
 * │ I    │ 8        │ ALAMAT (tidak dipakai)            │
 * │ J    │ 9        │ RBM → kode_petugas                │
 * │ K    │ 10       │ RPTAG                             │
 * │ L    │ 11       │ RPBK                              │
 * │ M    │ 12       │ LEMBAR                            │
 * └──────┴──────────┴──────────────────────────────────┘
 *
 * Catatan:
 *   - Delimiter default: titik koma (;)
 *   - kode_petugas diambil dari kolom J (RBM), bukan dari UNITAP
 *   - Jika kolom M (LEMBAR) kosong/tidak ada, default = 1
 */
class PelangganImportService
{
    // ─────────────────────────────────────────────────────────────────────────
    // Mapping posisi kolom CSV PLN (0-based index)
    // ─────────────────────────────────────────────────────────────────────────
    private const COL_UNITAP  = 0;
    private const COL_IDPEL   = 2;
    private const COL_NAMA    = 3;
    private const COL_DAYA    = 5;
    private const COL_RBM     = 9;   // → kode_petugas
    private const COL_RPTAG   = 10;
    private const COL_RPBK    = 11;
    private const COL_LEMBAR  = 12;

    // Jumlah kolom minimum yang harus ada dalam satu baris
    private const MIN_COLUMNS = 12;

    // ─────────────────────────────────────────────────────────────────────────
    // Header yang diharapkan di baris pertama CSV (lowercase, untuk validasi)
    // Jika header tidak cocok, import tetap berjalan tapi dicatat sebagai warning.
    // ─────────────────────────────────────────────────────────────────────────
    private const EXPECTED_HEADERS = [
        self::COL_UNITAP => 'unitap',
        self::COL_IDPEL  => 'idpel',
        self::COL_NAMA   => 'nama',
        self::COL_DAYA   => 'daya',
        self::COL_RBM    => 'rbm',
        self::COL_RPTAG  => 'rptag',
        self::COL_RPBK   => 'rpbk',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: processFile
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Parse a CSV file and return structured result.
     *
     * @param  string $filePath  Absolute path ke file CSV
     * @return array {
     *   preview: array,   // Semua baris + metadata _row/_status/_errors
     *   valid:   array,   // Baris yang lolos validasi
     *   errors:  array,   // Pesan error per baris
     *   stats:   array,   // total/valid/error/error_rows
     *   fatal:   string,  // Error fatal (file tidak bisa dibuka, dll)
     *   header_warning: string|null  // Warning jika header tidak sesuai
     * }
     */
    public function processFile(string $filePath): array
    {
        $preview = [];
        $valid   = [];
        $errors  = [];

        // ── Buka file ──────────────────────────────────────────────────────
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return $this->emptyResult('File tidak bisa dibuka.');
        }

        // ── Deteksi delimiter ──────────────────────────────────────────────
        $delimiter = $this->detectDelimiter($handle);

        // ── Baca & validasi header ─────────────────────────────────────────
        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (! $rawHeader) {
            fclose($handle);
            return $this->emptyResult('File CSV kosong atau header tidak ditemukan.');
        }

        $headerWarning = $this->checkHeader($rawHeader);

        // ── Ambil existing idpel dari DB untuk cek duplikat ────────────────
        $existingIds = DB::table('pelanggan')->pluck('idpel')->flip()->all();
        $seenInBatch = [];

        // ── Baca baris data ────────────────────────────────────────────────
        $rowNumber = 1; // baris 1 = header, data mulai baris 2
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            // Skip baris kosong
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $data      = $this->extractRow($row, $rowNumber);
            $rowErrors = $this->validateRow($data, $rowNumber, $existingIds, $seenInBatch);

            $preview[] = array_merge($data, [
                '_row'    => $rowNumber,
                '_status' => empty($rowErrors) ? 'valid' : 'error',
                '_errors' => $rowErrors,
            ]);

            if (empty($rowErrors)) {
                $valid[]                      = $data;
                $seenInBatch[$data['idpel']]  = true;
            } else {
                foreach ($rowErrors as $msg) {
                    $errors[] = "Baris {$rowNumber} – {$msg}";
                }
            }
        }

        fclose($handle);

        return [
            'preview'        => $preview,
            'valid'          => $valid,
            'errors'         => $errors,
            'stats'          => [
                'total'      => count($preview),
                'valid'      => count($valid),
                'error'      => count($errors),
                'error_rows' => count(array_filter($preview, fn($r) => $r['_status'] === 'error')),
            ],
            'fatal'          => null,
            'header_warning' => $headerWarning,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: saveToDatabase
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Batch-upsert valid rows into the database (INSERT or UPDATE).
     *
     * @param  array $validRows   Array of rows from processFile()['valid']
     * @param  array $importMeta  Optional metadata: filename, imported_by, import_date
     * @return array { inserted, updated, total, import_log_id }
     */
    public function saveToDatabase(array $validRows, array $importMeta = []): array
    {
        if (empty($validRows)) {
            return [
                'inserted'      => 0,
                'updated'       => 0,
                'total'         => 0,
                'import_log_id' => null,
            ];
        }

        DB::beginTransaction();

        try {
            $now      = now();
            $inserted = 0;
            $updated  = 0;
            $failed   = 0;

            // ── Buat import log ────────────────────────────────────────────
            $importLogId = DB::table('import_logs')->insertGetId([
                'type'        => 'pelanggan',
                'filename'    => $importMeta['filename']    ?? 'import_' . now()->format('YmdHis'),
                'imported_by' => $importMeta['imported_by'] ?? 'system',
                'import_date' => $importMeta['import_date'] ?? now(),
                'total_rows'  => count($validRows),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            // ── Cache petugas (kode_petugas → id) ─────────────────────────
            // Satu query untuk semua petugas, menghindari N+1 query
            $petugasMap = DB::table('petugas')
                ->pluck('id', 'kode_petugas')
                ->all();

            // ── Upsert per baris ───────────────────────────────────────────
            foreach ($validRows as $row) {
                try {
                    $petugasId = $petugasMap[$row['kode_petugas']] ?? null;

                    $data = [
                        'idpel'        => $row['idpel'],
                        'nama'         => $row['nama'],
                        'unitap'       => $row['unitap'],
                        'kode_petugas' => $row['kode_petugas'],
                        'petugas_id'   => $petugasId,
                        'daya'         => (int)   $row['daya'],
                        'lembar'       => (int)   $row['lembar'],
                        'rptag'        => (float) ($row['rptag'] ?? 0),
                        'rpbk'         => (float) ($row['rpbk']  ?? 0),
                        'updated_at'   => $now,
                    ];

                    $exists = DB::table('pelanggan')
                        ->where('idpel', $row['idpel'])
                        ->exists();

                    if ($exists) {
                        DB::table('pelanggan')
                            ->where('idpel', $row['idpel'])
                            ->update($data);
                        $updated++;
                    } else {
                        $data['created_at'] = $now;
                        DB::table('pelanggan')->insert($data);
                        $inserted++;
                    }
                } catch (\Exception $rowEx) {
                    Log::warning("Gagal upsert idpel={$row['idpel']}: " . $rowEx->getMessage());
                    $failed++;
                }
            }

            // ── Update import log dengan hasil ─────────────────────────────
            DB::table('import_logs')
                ->where('id', $importLogId)
                ->update([
                    'inserted'   => $inserted,
                    'updated'    => $updated,
                    'failed'     => $failed,
                    'notes'      => "Inserted: {$inserted}, Updated: {$updated}, Failed: {$failed}",
                    'updated_at' => $now,
                ]);

            DB::commit();

            return [
                'inserted'      => $inserted,
                'updated'       => $updated,
                'total'         => $inserted + $updated,
                'import_log_id' => $importLogId,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal save import pelanggan: ' . $e->getMessage());
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Deteksi delimiter CSV (titik koma atau koma).
     * Membaca baris pertama, menghitung kemunculan ; dan ,
     * lalu mengembalikan yang paling banyak.
     */
    private function detectDelimiter($handle): string
    {
        $firstLine = fgets($handle);
        rewind($handle);

        $semicolonCount = substr_count($firstLine, ';');
        $commaCount     = substr_count($firstLine, ',');

        return $semicolonCount >= $commaCount ? ';' : ',';
    }

    /**
     * Validasi header CSV.
     * Mengembalikan string warning jika header tidak sesuai, atau null jika OK.
     * Import tetap berjalan meski header tidak cocok (positional mapping).
     */
    private function checkHeader(array $rawHeader): ?string
    {
        $normalised = array_map(fn($h) => strtolower(trim($h)), $rawHeader);
        $mismatches = [];

        foreach (self::EXPECTED_HEADERS as $index => $expected) {
            $actual = $normalised[$index] ?? '(tidak ada)';
            if ($actual !== $expected) {
                $mismatches[] = "Kolom " . ($index + 1) . ": diharapkan '{$expected}', ditemukan '{$actual}'";
            }
        }

        if (empty($mismatches)) {
            return null;
        }

        return 'Peringatan header: ' . implode('; ', $mismatches) .
               '. Import tetap dilanjutkan menggunakan posisi kolom.';
    }

    /**
     * Ekstrak satu baris CSV berdasarkan posisi kolom.
     * Semua pembersihan format angka (titik pemisah ribuan) dilakukan di sini.
     *
     * @param  array $row       Baris CSV dari fgetcsv()
     * @param  int   $rowNumber Nomor baris (untuk logging saja)
     * @return array
     */
    private function extractRow(array $row, int $rowNumber): array
    {
        // Helper: ambil nilai dari kolom berdasarkan index
        $get = function (int $colIndex) use ($row): string {
            $value = trim($row[$colIndex] ?? '');
            // Bersihkan format angka PLN: "1.234.567" → "1234567"
            $value = str_replace('.', '', $value);
            // Bersihkan koma desimal jika ada
            $value = str_replace(',', '', $value);
            return $value;
        };

        // Helper: ambil teks (tanpa cleaning angka, uppercase)
        $getText = function (int $colIndex) use ($row): string {
            return strtoupper(trim($row[$colIndex] ?? ''));
        };

        $unitap      = $getText(self::COL_UNITAP);
        $kodePetugas = $getText(self::COL_RBM);   // RBM = kode_petugas

        // LEMBAR: kolom M (index 12), default 1 jika kosong atau tidak ada
        $lembarRaw = $get(self::COL_LEMBAR);
        $lembar    = ($lembarRaw !== '' && is_numeric($lembarRaw))
                        ? $lembarRaw
                        : '1';

        return [
            'idpel'        => $get(self::COL_IDPEL),
            'nama'         => $getText(self::COL_NAMA),
            'unitap'       => $unitap,
            'kode_petugas' => $kodePetugas,
            'daya'         => $get(self::COL_DAYA),
            'rptag'        => $get(self::COL_RPTAG),
            'rpbk'         => $get(self::COL_RPBK),
            'lembar'       => $lembar,
        ];
    }

    /**
     * Validasi satu baris data.
     *
     * @return string[] Array pesan error (kosong = valid)
     */
    private function validateRow(
        array $data,
        int   $rowNumber,
        array $existingIds,
        array $seenInBatch
    ): array {
        $errors = [];

        // ── IDPEL ──────────────────────────────────────────────────────────
        if ($data['idpel'] === '') {
            $errors[] = 'idpel wajib diisi';
        } elseif (isset($seenInBatch[$data['idpel']])) {
            $errors[] = "idpel '{$data['idpel']}' duplikat dalam file";
        }
        // Catatan: duplikat dengan DB tidak dianggap error karena akan di-UPDATE

        // ── NAMA ───────────────────────────────────────────────────────────
        if ($data['nama'] === '') {
            $errors[] = 'nama wajib diisi';
        }

        // ── DAYA ───────────────────────────────────────────────────────────
        if ($data['daya'] === '' || ! is_numeric($data['daya'])) {
            $errors[] = 'daya harus berupa angka (nilai: "' . $data['daya'] . '")';
        }

        // ── LEMBAR ─────────────────────────────────────────────────────────
        if ($data['lembar'] === '' || ! is_numeric($data['lembar'])) {
            $errors[] = 'lembar harus berupa angka (nilai: "' . $data['lembar'] . '")';
        }

        // ── RPTAG ──────────────────────────────────────────────────────────
        if ($data['rptag'] === '' || ! is_numeric($data['rptag'])) {
            $errors[] = 'rptag harus berupa angka (nilai: "' . $data['rptag'] . '")';
        }

        // ── RPBK ───────────────────────────────────────────────────────────
        if ($data['rpbk'] === '' || ! is_numeric($data['rpbk'])) {
            $errors[] = 'rpbk harus berupa angka (nilai: "' . $data['rpbk'] . '")';
        }

        // ── UNITAP ─────────────────────────────────────────────────────────
        if ($data['unitap'] === '') {
            $errors[] = 'UNITAP wajib diisi';
        }

        // ── KODE PETUGAS (RBM) ─────────────────────────────────────────────
        if ($data['kode_petugas'] === '') {
            $errors[] = 'Kode Petugas (kolom RBM) wajib diisi';
        } else {
            $petugasExists = DB::table('petugas')
                ->where('kode_petugas', $data['kode_petugas'])
                ->exists();

            if (! $petugasExists) {
                $errors[] = "Kode Petugas '{$data['kode_petugas']}' tidak ditemukan di data petugas";
            }
        }

        return $errors;
    }

    /**
     * Return struktur kosong dengan pesan error fatal.
     */
    private function emptyResult(string $fatalError = ''): array
    {
        return [
            'preview'        => [],
            'valid'          => [],
            'errors'         => $fatalError ? [$fatalError] : [],
            'stats'          => ['total' => 0, 'valid' => 0, 'error' => 0, 'error_rows' => 0],
            'fatal'          => $fatalError,
            'header_warning' => null,
        ];
    }
}