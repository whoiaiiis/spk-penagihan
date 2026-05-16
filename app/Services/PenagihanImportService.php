<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PenagihanImportService
 *
 * Handles CSV parsing, row-level validation, and batch database insert
 * for penagihan data based on PLN format.
 */
class PenagihanImportService
{
    /**
     * CSV Column mapping based on user format:
     * A = UNIT
     * B = IDPEL
     * C = NAMA
     * D = TARIF
     * E = DAYA
     * F = LEMBAR
     * G = GARDU
     * H = ALAMAT
     * I = NO SURAT
     * J = RP TAG
     * K = TEXT RP TAG
     * L = TERBILANG
     * M = BULAN
     * N = RBM
     * O = LNS
     */
    private const COLUMNS = [
        'unit'         => 0,  // A
        'idpel'        => 1,  // B
        'nama'         => 2,  // C
        'tarif'        => 3,  // D
        'daya'         => 4,  // E
        'lembar'       => 5,  // F
        'gardu'        => 6,  // G
        'alamat'       => 7,  // H
        'nomor_surat'  => 8,  // I
        'rptag'        => 9,  // J
        'text_rptag'   => 10, // K
        'terbilang'    => 11, // L
        'bulan'        => 12, // M
        'rbm'          => 13, // N
        'lns'          => 14, // O
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

        $delimiter = ';';

        // Skip header
        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (! $rawHeader) {
            fclose($handle);
            return $this->emptyResult('File CSV kosong.');
        }

        // Fetch lookup data
        $existingPelanggan = DB::table('pelanggan')->pluck('idpel')->flip()->all();
        $petugasList = DB::table('petugas')->select('id', 'kode_petugas')->get()->keyBy('kode_petugas')->toArray();
        $rankingList = DB::table('ranking')->select('idpel', 'ranking', 'nilai_akhir')->get()->keyBy('idpel')->toArray();

        $seenInBatch = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            // Skip completely blank rows
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $data      = $this->extractRow($row);
            $rowErrors = [];

            // 1. Validation: IDPEL
            if ($data['idpel'] === '') {
                $rowErrors[] = 'IDPEL kosong';
            } elseif (!isset($existingPelanggan[$data['idpel']])) {
                $rowErrors[] = "IDPEL '{$data['idpel']}' tidak ditemukan di tabel pelanggan";
            }

            // 2. Validation: Petugas (UNIT)
            $petugasId = null;
            if ($data['unit'] === '') {
                $rowErrors[] = 'UNIT kosong';
            } elseif (!isset($petugasList[$data['unit']])) {
                $rowErrors[] = "Kode Petugas '{$data['unit']}' tidak ditemukan";
            } else {
                $petugasId = $petugasList[$data['unit']]->id;
            }
            $data['petugas_id'] = $petugasId;

            // 3. Validation: Ranking
            if ($data['idpel'] !== '') {
                if (!isset($rankingList[$data['idpel']])) {
                    $rowErrors[] = "Data ranking untuk IDPEL '{$data['idpel']}' tidak ditemukan";
                    $data['ranking_awal'] = null;
                    $data['nilai_spk'] = null;
                } else {
                    $data['ranking_awal'] = $rankingList[$data['idpel']]->ranking;
                    $data['nilai_spk']    = $rankingList[$data['idpel']]->nilai_akhir;
                }
            }

            // 4. Duplicate validation in file
            $uniqueKey = $data['idpel'] . '_' . $data['bulan'] . '_' . $data['nomor_surat'];
            if (isset($seenInBatch[$uniqueKey])) {
                $rowErrors[] = "Data duplikat dalam file (IDPEL + Bulan + No Surat)";
            }

            $preview[] = array_merge($data, [
                '_row'    => $rowNumber,
                '_status' => empty($rowErrors) ? 'valid' : 'error',
                '_errors' => $rowErrors,
            ]);

            if (empty($rowErrors)) {
                $valid[] = $data;
                $seenInBatch[$uniqueKey] = true;
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
        $insert = [];
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($validRows as $r) {
                // Check for existing DB duplicate
                $exists = DB::table('penagihan')
                    ->where('idpel', $r['idpel'])
                    ->where('bulan', $r['bulan'])
                    ->where('nomor_surat', $r['nomor_surat'])
                    ->exists();

                if (!$exists) {
                    $insert[] = [
                        'idpel'         => $r['idpel'],
                        'petugas_id'    => $r['petugas_id'],
                        'bulan'         => $r['bulan'],
                        'status'        => $r['status'],
                        'jumlah_tagih'  => $r['jumlah_bayar'],
                        'keterangan'    => '',
                        'tgl_tagih'     => $r['tanggal_tagih'],
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];

                    // Update pelanggan status
                    DB::table('pelanggan')
                        ->where('idpel', $r['idpel'])
                        ->update(['status_penagihan' => $r['status']]);

                    $count++;
                }
            }

            foreach (array_chunk($insert, 500) as $chunk) {
                DB::table('penagihan')->insert($chunk);
            }

            DB::commit();
            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal save import penagihan: ' . $e->getMessage());
            throw $e;
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ────────────────────────────────────────────────────────────────────────

    private function extractRow(array $row): array
    {
        $get = function(int $index) use ($row) {
            return isset($row[$index]) ? trim($row[$index]) : '';
        };

        // Normalisasi IDPEL: Hilangkan format scientific
        $rawIdpel = $get(self::COLUMNS['idpel']);
        $idpel = preg_replace('/[^0-9]/', '', $rawIdpel);
        if (stripos($rawIdpel, 'E') !== false) {
            $idpel = number_format((float)$rawIdpel, 0, '', '');
        }

        // Normalisasi nominal
        $rawNominal = $get(self::COLUMNS['rptag']);
        $jumlahBayar = (int) str_replace(['.', ','], '', $rawNominal);

        // Status dan tanggal tagih
        // Jika LNS tgl ada isi -> jumlah bayar diisi, status = sudah
        // Sesuai prompt user: jumlah_bayar > 0 -> status = sudah, jumlah_bayar <= 0 -> status = belum
        $lns = $get(self::COLUMNS['lns']);
        $status = 'belum';
        $tanggalTagih = null;

        if ($jumlahBayar > 0 && !empty($lns)) {
            $status = 'sudah';
            $tanggalTagih = now()->format('Y-m-d');
        } elseif ($jumlahBayar <= 0) {
            $status = 'belum';
            $jumlahBayar = 0;
            $tanggalTagih = null;
        }

        return [
            'unit'          => $get(self::COLUMNS['unit']),
            'idpel'         => $idpel,
            'nama'          => $get(self::COLUMNS['nama']),
            'daya'          => $get(self::COLUMNS['daya']),
            'lembar'        => $get(self::COLUMNS['lembar']),
            'nomor_surat'   => $get(self::COLUMNS['nomor_surat']),
            'jumlah_bayar'  => $jumlahBayar,
            'bulan'         => $get(self::COLUMNS['bulan']),
            'lns'           => $lns,
            'status'        => $status,
            'tanggal_tagih' => $tanggalTagih,
        ];
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
     */
    private function emptyResult(string $fatalError = ''): array
    {
        return [
            'preview'   => [],
            'valid'     => [],
            'errors'    => $fatalError ? [$fatalError] : [],
            'errorList' => $fatalError ? [$fatalError] : [],
            'stats'     => [
                'total'      => 0,
                'valid'      => 0,
                'error'      => 0,
                'error_rows' => 0,
            ],
            'fatal'     => $fatalError,
        ];
    }
}
