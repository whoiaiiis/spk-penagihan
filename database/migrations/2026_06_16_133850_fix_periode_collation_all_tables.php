<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Samakan collation kolom `periode` di semua tabel menjadi utf8mb4_unicode_ci.
     * Hal ini mencegah error "Illegal mix of collations" saat JOIN antar tabel.
     */
    public function up(): void
    {
        $tables = [
            'pelanggan',
            'penilaian',
            'normalisasi',
            'ranking',
            'invoice',
        ];

        foreach ($tables as $table) {
            // Cek apakah tabel dan kolom `periode` ada
            $exists = DB::select("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME   = ?
                  AND COLUMN_NAME  = 'periode'
            ", [$table]);

            if (!empty($exists)) {
                DB::statement("
                    ALTER TABLE `{$table}`
                    MODIFY COLUMN `periode` VARCHAR(7)
                        CHARACTER SET utf8mb4
                        COLLATE utf8mb4_unicode_ci
                        NULL
                ");
            }
        }
    }

    /**
     * Tidak ada rollback — collation adalah koreksi teknis,
     * bukan perubahan struktural yang bisa di-undo secara aman.
     */
    public function down(): void
    {
        // intentionally left blank
    }
};
