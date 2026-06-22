<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PelangganImportService
 *
 * Handles CSV parsing, row-level validation, and batch database upsert
 * for pelanggan data (format PLN). Uses native PHP fgetcsv.
 */
class PelangganImportService
{
    // Alias Header untuk Deteksi Kolom Dinamis
    private const COLUMN_ALIASES = [
        'unitap'       => ['unitap', 'unit_ap', 'ap'],
        'idpel'        => ['idpel', 'id_pel', 'id_pelanggan'],
        'nama'         => ['nama', 'nama_pelanggan'],
        'daya'         => ['daya', 'tarif_daya'],
        'kode_petugas' => ['rbm', 'kode_petugas', 'petugas'],
        'rptag'        => ['rptag', 'tagihan', 'rp_tag'],
        'rpbk'         => ['rpbk', 'bk', 'rp_bk'],
        'lembar'       => ['lembar', 'lbr'],
    ];

    // Fallback index positional PLN default (jika header tidak sesuai)
    private const FALLBACK_INDEX = [
        'unitap'       => 0,
        'idpel'        => 2,
        'nama'         => 3,
        'daya'         => 5,
        'kode_petugas' => 9,
        'rptag'        => 10,
        'rpbk'         => 11,
        'lembar'       => 12,
    ];

    /**
     * Parse a CSV file and return structured result.
     */
    public function processFile(string $filePath, array $importMeta = []): array
    {
        $preview = [];
        $valid   = [];
        $errors  = [];

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return $this->emptyResult('File tidak bisa dibuka.');
        }

        $delimiter = $this->detectDelimiter($handle);

        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (! $rawHeader) {
            fclose($handle);
            return $this->emptyResult('File CSV kosong atau header tidak ditemukan.');
        }

        // TAHAP 1: Buat riwayat Import Log tepat sebelum membaca file CSV
        $importLogId = DB::table('import_logs')->insertGetId([
            'type'        => 'pelanggan',
            'filename'    => $importMeta['filename']    ?? 'import_' . now()->format('YmdHis'),
            'imported_by' => $importMeta['imported_by'] ?? 'system',
            'import_date' => $importMeta['import_date'] ?? now(),
            'total_rows'  => 0, 
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $colIndex = $this->resolveColumnIndex($rawHeader);

        $existingIds = DB::table('pelanggan')->pluck('idpel')->flip()->all();
        $seenInBatch = [];
        $petugasCache = DB::table('petugas')->pluck('id', 'kode_petugas')->all();

        $rowNumber = 1; 
        $detailedErrors = []; // Penampung untuk DB

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $data = $this->extractRow($row, $colIndex);

            if (!isset($petugasCache[$data['kode_petugas']])) {
                $inferredFromUnitap = substr($data['unitap'], 0, 6);
                if (isset($petugasCache[$inferredFromUnitap])) {
                    $data['kode_petugas'] = $inferredFromUnitap;
                }
            }

            $rowErrors = $this->validateRow($data, $petugasCache, $seenInBatch);

            $preview[] = array_merge($data, [
                '_row'    => $rowNumber,
                '_status' => empty($rowErrors) ? 'valid' : 'error',
                '_errors' => $rowErrors,
            ]);

            if (empty($rowErrors)) {
                $valid[]                      = $data;
                $seenInBatch[$data['idpel']]  = true;
            } else {
                $pesanError = implode(', ', $rowErrors);
                $errors[] = "Baris {$rowNumber} – {$pesanError}";

                // TAHAP 2: Kumpulkan struktur error spesifik untuk database
                $detailedErrors[] = [
                    'import_log_id' => $importLogId,
                    'row_number'    => $rowNumber,
                    'row_data'      => json_encode($data),
                    'error_message' => $pesanError,
                    'error_type'    => 'validation',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        fclose($handle);

        // TAHAP 3: Simpan seluruh kumpulan error Preview ke tabel import_errors
        if (!empty($detailedErrors)) {
            foreach (array_chunk($detailedErrors, 500) as $chunk) {
                DB::table('import_errors')->insert($chunk);
            }
        }

        // TAHAP 4: Update jumlah baris di import_logs
        DB::table('import_logs')
            ->where('id', $importLogId)
            ->update([
                'total_rows' => count($preview),
                'failed'     => count($detailedErrors),
                'notes'      => 'Upload CSV selesai. Menunggu konfirmasi Save (bila ada data valid).',
            ]);

        return [
            'import_log_id'  => $importLogId,
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
            'header_warning' => null,
        ];
    }

    /**
     * Batch-upsert valid rows into the database (INSERT or UPDATE).
     */
    public function saveToDatabase(array $validRows, int $importLogId, string $periode): array
    {
        if (empty($validRows)) {
            return [
                'inserted'      => 0,
                'updated'       => 0,
                'total'         => 0,
                'import_log_id' => $importLogId,
            ];
        }

        DB::beginTransaction();

        try {
            $now      = now();
            $inserted = 0;
            $updated  = 0;
            $failed   = 0;

            $petugasMap = DB::table('petugas')
                ->pluck('id', 'kode_petugas')
                ->all();

            $historiInsertData = [];
            $periodeSekarang = date('Y-m');

            foreach ($validRows as $row) {
                try {
                    $petugasId = $petugasMap[$row['kode_petugas']] ?? null;

                    $data = [
                        'idpel'        => $row['idpel'],
                        'periode'      => $periode,
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
                        ->where('periode', $periode)
                        ->exists();

                    if ($exists) {
                        DB::table('pelanggan')
                            ->where('idpel', $row['idpel'])
                            ->where('periode', $periode)
                            ->update($data);
                        $updated++;
                    } else {
                        $data['created_at'] = $now;
                        DB::table('pelanggan')->insert($data);
                        $inserted++;
                    }

                    $historiInsertData[] = [
                        'idpel'      => $row['idpel'],
                        'periode'    => $periodeSekarang,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } catch (\Exception $rowEx) {
                    Log::warning("Gagal upsert idpel={$row['idpel']}: " . $rowEx->getMessage());
                    $failed++;
                }
            }

            if (!empty($historiInsertData)) {
                foreach (array_chunk($historiInsertData, 500) as $chunk) {
                    DB::table('histori_tunggakan')->insertOrIgnore($chunk);
                }
            }

            DB::statement("
                UPDATE pelanggan 
                SET historis_tunggakan = (
                    SELECT COUNT(id) 
                    FROM histori_tunggakan 
                    WHERE histori_tunggakan.idpel = pelanggan.idpel 
                      AND histori_tunggakan.periode >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 12 MONTH), '%Y-%m')
                )
                WHERE pelanggan.periode = ?
            ", [$periodeSekarang]);

            // 5. CUKUP UPDATE STATUS LOG YANG SUDAH DIBUAT SAAT UPLOAD
            DB::table('import_logs')
                ->where('id', $importLogId)
                ->update([
                    'inserted'   => $inserted,
                    'updated'    => $updated,
                    'failed'     => DB::raw("failed + {$failed}"),
                    'notes'      => "Proses Save Selesai. Inserted: {$inserted}, Updated: {$updated}, DB Failed: {$failed}",
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

    private function detectDelimiter($handle): string
    {
        $firstLine = fgets($handle);
        rewind($handle);

        $semicolonCount = substr_count($firstLine, ';');
        $commaCount     = substr_count($firstLine, ',');

        return $semicolonCount >= $commaCount ? ';' : ',';
    }

    private function resolveColumnIndex(array $rawHeader): array
    {
        $header = array_map(fn($h) => strtolower(trim($h)), $rawHeader);
        $headerFlip = array_flip($header);
        $resolved = [];

        foreach (self::COLUMN_ALIASES as $internalName => $aliases) {
            $found = false;
            foreach ($aliases as $alias) {
                if (isset($headerFlip[$alias])) {
                    $resolved[$internalName] = $headerFlip[$alias];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $resolved[$internalName] = self::FALLBACK_INDEX[$internalName] ?? 0;
            }
        }

        return $resolved;
    }

    private function extractRow(array $row, array $colIndex): array
    {
        $get = function (string $key) use ($row, $colIndex): string {
            $idx = $colIndex[$key] ?? -1;
            $value = isset($row[$idx]) ? trim($row[$idx]) : '';
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '', $value);
            return $value;
        };

        $getText = function (string $key) use ($row, $colIndex): string {
            $idx = $colIndex[$key] ?? -1;
            return strtoupper(trim($row[$idx] ?? ''));
        };

        $lembarRaw = $get('lembar');
        $lembar    = ($lembarRaw !== '' && is_numeric($lembarRaw)) ? $lembarRaw : '1';

        return [
            'idpel'        => $get('idpel'),
            'nama'         => $getText('nama'),
            'unitap'       => $getText('unitap'),
            'kode_petugas' => $getText('kode_petugas'),
            'daya'         => $get('daya'),
            'rptag'        => $get('rptag'),
            'rpbk'         => $get('rpbk'),
            'lembar'       => $lembar,
        ];
    }

    private function validateRow(array $data, array $petugasCache, array $seenInBatch): array
    {
        $errors = [];

        if ($data['idpel'] === '') {
            $errors[] = 'idpel wajib diisi';
        } elseif (isset($seenInBatch[$data['idpel']])) {
            $errors[] = "idpel '{$data['idpel']}' duplikat dalam file";
        }

        if ($data['nama'] === '') {
            $errors[] = 'nama wajib diisi';
        }

        if ($data['daya'] === '' || ! is_numeric($data['daya'])) {
            $errors[] = 'daya harus berupa angka';
        }

        if ($data['lembar'] === '' || ! is_numeric($data['lembar'])) {
            $errors[] = 'lembar harus berupa angka';
        }

        if ($data['rptag'] === '' || ! is_numeric($data['rptag'])) {
            $errors[] = 'rptag harus berupa angka';
        }

        if ($data['rpbk'] === '' || ! is_numeric($data['rpbk'])) {
            $errors[] = 'rpbk harus berupa angka';
        }

        if ($data['unitap'] === '') {
            $errors[] = 'UNITAP wajib diisi';
        }

        if ($data['kode_petugas'] === '') {
            $errors[] = 'Kode Petugas (kolom RBM) wajib diisi';
        } elseif (!isset($petugasCache[$data['kode_petugas']])) {
            $errors[] = "Kode Petugas '{$data['kode_petugas']}' tidak ditemukan di data petugas";
        }

        return $errors;
    }

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