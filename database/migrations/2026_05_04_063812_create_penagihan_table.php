<?php

// ================================================================
// FILE: database/migrations/xxxx_create_penagihan_table.php
//
// PERUBAHAN dari versi lama:
// - Kolom bulan nullable (karena CSV hanya punya TglBuku, bukan bulan format YYYY-MM)
// - Kolom kode_petugas ditambah (diambil otomatis dari pelanggan)
// - Kolom tgl_tagih (dari TglBuku di CSV)
// - Unique key: idpel + bulan (petugas_id dihapus dari unique agar lebih fleksibel)
// - Kolom jumlah_tagih dan keterangan tetap ada untuk keperluan monitoring
// ================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penagihan', function (Blueprint $table) {
            $table->id();

            // Relasi ke pelanggan
            $table->string('idpel', 255);

            // Diambil otomatis dari pelanggan saat import — tidak dari CSV
            $table->string('kode_petugas', 50)->nullable();
            $table->unsignedBigInteger('petugas_id')->nullable();

            // Bulan penagihan (format: YYYY-MM), diambil dari TglBuku di CSV
            $table->string('bulan', 7)->nullable();

            // Status: belum / lunas
            $table->enum('status', ['belum', 'lunas'])->default('belum');

            // Tanggal penagihan dari kolom TglBuku di CSV
            $table->date('tgl_tagih')->nullable();

            // Nominal tagihan (opsional, untuk monitoring)
            $table->bigInteger('jumlah_tagih')->default(0);

            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Satu IDPEL hanya boleh tercatat satu kali per bulan
            $table->unique(['idpel', 'bulan'], 'unique_penagihan_idpel_bulan');

            // Foreign keys
            $table->foreign('idpel')
                  ->references('idpel')
                  ->on('pelanggan')
                  ->onDelete('cascade');

            $table->foreign('petugas_id')
                  ->references('id')
                  ->on('petugas')
                  ->onDelete('set null');

            $table->index(['petugas_id', 'bulan']);
            $table->index('bulan');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penagihan');
    }
};