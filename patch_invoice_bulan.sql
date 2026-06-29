-- =============================================================================
-- PATCH: Isi kolom `bulan` pada tabel invoice dari kolom `periode`
-- Jalankan sekali saat MySQL aktif (via phpMyAdmin, MySQL CLI, atau HeidiSQL)
-- =============================================================================

-- Cek dulu berapa banyak record yang perlu dipatch
SELECT COUNT(*) AS perlu_dipatch
FROM invoice
WHERE bulan IS NULL OR bulan = '';

-- Patch: set bulan = periode untuk semua record yang bulannya masih NULL/kosong
UPDATE invoice
SET bulan = periode
WHERE bulan IS NULL OR bulan = '';

-- Verifikasi setelah patch
SELECT COUNT(*) AS total_invoice,
       SUM(CASE WHEN bulan IS NULL OR bulan = '' THEN 1 ELSE 0 END) AS masih_null,
       SUM(CASE WHEN bulan IS NOT NULL AND bulan != '' THEN 1 ELSE 0 END) AS sudah_terisi
FROM invoice;
