<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * PelangganImportService
 *
 * Handles CSV parsing, row-level validation, and batch database insert
 * for pelanggan data. Uses native PHP fgetcsv — no external packages.
 */
class PelangganImportService
{
    /**
     * Kolom wajib yang HARUS ada di CSV.
     * Kunci = nama kolom internal (dipakai di DB).
     * Value = array alias yang diterima dari file (semua lowercase).
     *
     * Cara baca: kolom internal 'idpel' akan dicari sebagai
     * 'idpel' ATAU 'id_pel' ATAU 'no_pelanggan' dst di header file.
     */
    private const COLUMN_ALIASES = [

    /*
    |--------------------------------------------------------------------------
    | ID PELANGGAN
    |--------------------------------------------------------------------------
    */
    'idpel' => [
        'idpel',
        'id_pel',
        'no_pelanggan',
        'nopel',
        'id pelanggan',
        'nomor pelanggan'
    ],

    /*
    |--------------------------------------------------------------------------
    | NAMA PELANGGAN
    |--------------------------------------------------------------------------
    */
    'nama' => [
        'nama',
        'nama_pelanggan',
        'name',
        'nama pelanggan'
    ],

    /*
    |--------------------------------------------------------------------------
    | DAYA
    |--------------------------------------------------------------------------
    */
    'daya' => [
        'daya',
        'daya_listrik',
        'tarif',
        'kva'
    ],

    /*
    |--------------------------------------------------------------------------
    | LEMBAR
    |--------------------------------------------------------------------------
    */
    'lembar' => [
        'lembar',
        'jumlah_lembar',
        'lbr',
        'jml lembar',
        'jml_lembar'
    ],

    /*
    |--------------------------------------------------------------------------
    | RPTAG
    |--------------------------------------------------------------------------
    */
    'rptag' => [
        'rptag',
        'rp_tagihan',
        'tagihan',
        'total_tagihan',
        'total tagihan',
        'jumlah_tagihan',
        'jumlah tagihan',
        'nilai_tagihan'
    ],

    /*
    |--------------------------------------------------------------------------
    | RPBK
    |--------------------------------------------------------------------------
    */
    'rpbk' => [
        'rpbk',
        'rpbk_total',
        'biaya_keterlambatan',
        'total_rpbk'
    ],
    ];

    /**
     * Parse a CSV file and return structured result.
     */
    public function processFile(string $filePath): array
    {
        $preview = [];
        $valid   = [];
        $errors  = [];

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return $this->emptyResult('File tidak bisa dibuka.');
        }

        // ── 1. Detect delimiter ────────────────────────────────────────────
        $firstLine = fgets($handle);
        rewind($handle);
       $delimiter = ';';

        // ── 2. Read header ─────────────────────────────────────────────────
        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (! $rawHeader) {
            fclose($handle);
            return $this->emptyResult('File CSV kosong atau header tidak ditemukan.');
        }

        // Normalise header: trim + lowercase
        $header = array_map(fn($h) => strtolower(trim($h)), $rawHeader);

        // ── 3. Resolve alias → internal column name ────────────────────────
        // Builds: ['idpel' => 2, 'nama' => 3, 'daya' => 5, ...]
        // Maps internal column name → index in the CSV row
        $colIndex = $this->resolveColumnIndex($header);

        // Check which internal columns could not be resolved
        $missing = array_keys(array_filter(
            array_keys(self::COLUMN_ALIASES),
            fn($col) => ! isset($colIndex[$col])
        , ARRAY_FILTER_USE_KEY));

        // Rebuild missing list properly
        $missing = [];
        foreach (array_keys(self::COLUMN_ALIASES) as $col) {
            if (! isset($colIndex[$col])) {
                $missing[] = $col . ' (dicari: ' . implode('/', self::COLUMN_ALIASES[$col]) . ')';
            }
        }

        if (! empty($missing)) {
            fclose($handle);
            return $this->emptyResult(
                'Kolom wajib tidak ditemukan: ' . implode(', ', $missing) .
                '. Header file: ' . implode(', ', $header)
            );
        }

        // ── 4. Fetch existing idpel from DB for duplicate check ────────────
        $existingIds = DB::table('pelanggan')->pluck('idpel')->flip()->all();
        $seenInBatch = [];

        // ── 5. Read rows ───────────────────────────────────────────────────
        $rowNumber = 1;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            // Skip completely blank rows
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $data      = $this->extractRow($row, $colIndex);

            $rowErrors = $this->validateRow($data, $rowNumber, $existingIds, $seenInBatch);

            $preview[] = array_merge($data, [
                '_row'    => $rowNumber,
                '_status' => empty($rowErrors) ? 'valid' : 'error',
                '_errors' => $rowErrors,
            ]);

            if (empty($rowErrors)) {
                $valid[] = $data;
                $seenInBatch[$data['idpel']] = true;
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
        ];
    }

    /**
     * Batch-insert valid rows into the database.
     */
    public function saveToDatabase(array $validRows): int
    {
        if (empty($validRows)) {
            return 0;
        }

        $now    = now();
        $insert = array_map(fn($r) => [
            'idpel'      => $r['idpel'],
            'nama'       => $r['nama'],
            'daya'       => (int) $r['daya'],
            'lembar'     => (int) $r['lembar'],
           'rptag' => (float) ($r['rptag'] ?? 0),
'rpbk'  => (float) ($r['rpbk'] ?? 0),
            'created_at' => $now,
            'updated_at' => $now,
        ], $validRows);

        foreach (array_chunk($insert, 500) as $chunk) {
            DB::table('pelanggan')->insert($chunk);
        }

        return count($validRows);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Resolve CSV header array → map of internal_column => csv_index.
     * Tries each alias for each internal column and picks the first match.
     */
    private function resolveColumnIndex(array $header): array
    {
        $headerFlip = array_flip($header); // 'rptag' => 4, 'lembar' => 3, ...
        $resolved   = [];

        foreach (self::COLUMN_ALIASES as $internalName => $aliases) {
            foreach ($aliases as $alias) {
                if (isset($headerFlip[$alias])) {
                    $resolved[$internalName] = $headerFlip[$alias];
                    break; // found — stop trying aliases for this column
                }
            }
        }

        return $resolved;
    }

   private function extractRow(array $row, array $colIndex): array
{
   $get = function(string $col) use ($colIndex, $row) {

    if (!isset($colIndex[$col])) {
        return '';
    }

    $index = $colIndex[$col];

    if (!isset($row[$index])) {
        return '';
    }

    $value = trim($row[$index]);

    // bersihkan format angka
    $value = str_replace('.', '', $value);
    $value = str_replace(',', '', $value);

    return $value;
};
    return [

        'idpel'  => $get('idpel'),
        'nama'   => $get('nama'),
        'daya'   => $get('daya'),
        'lembar' => $get('lembar'),

        'rptag'  => $get('rptag'),
        'rpbk'   => $get('rpbk'),

    ];
}

    private function validateRow(
        array $data,
        int $row,
        array $existingIds,
        array $seenInBatch
    ): array {
        $errors = [];

        if ($data['idpel'] === '') {
            $errors[] = 'idpel wajib diisi';
        } elseif (isset($existingIds[$data['idpel']])) {
            $errors[] = "idpel '{$data['idpel']}' sudah ada di database";
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

        return $errors;
    }

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