<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Samakan collation kolom `idpel` di semua tabel menjadi utf8mb4_unicode_ci.
     */
    public function up(): void
    {
        $tables = [
            'pelanggan'   => 'VARCHAR(20)',
            'penilaian'   => 'VARCHAR(20)',
            'normalisasi' => 'VARCHAR(20)',
            'ranking'     => 'VARCHAR(20)',
            'invoice'     => 'VARCHAR(20)',
        ];

        foreach ($tables as $table => $type) {
            $exists = DB::select("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME   = ?
                  AND COLUMN_NAME  = 'idpel'
            ", [$table]);

            if (!empty($exists)) {
                DB::statement("
                    ALTER TABLE `{$table}`
                    MODIFY COLUMN `idpel` {$type}
                        CHARACTER SET utf8mb4
                        COLLATE utf8mb4_unicode_ci
                        NOT NULL
                ");
            }
        }
    }

    public function down(): void
    {
        // intentionally left blank
    }
};
