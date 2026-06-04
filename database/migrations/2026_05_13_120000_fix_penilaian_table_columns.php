<?php

// ================================================================
// FILE: database/migrations/xxxx_xx_xx_create_penagihan_table.php
// Jalankan: php artisan migrate
// Atau buat tabel manual dengan SQL di bawah
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
            $table->string('idpel', 50);            // FK ke pelanggan.idpel
            $table->unsignedBigInteger('petugas_id'); // FK ke petugas.id
            $table->string('bulan', 7);              // format: YYYY-MM  contoh: 2025-01
            $table->enum('status', ['sudah', 'belum'])->default('belum');
            $table->date('tgl_tagih')->nullable();   // tanggal penagihan dilakukan
            $table->decimal('jumlah_tagih', 15, 2)->default(0); // nominal yang berhasil ditagih
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Satu record per pelanggan per petugas per bulan
            $table->unique(['idpel', 'petugas_id', 'bulan'], 'unique_tagih');

            $table->foreign('idpel')->references('idpel')->on('pelanggan')->onDelete('cascade');
            $table->foreign('petugas_id')->references('id')->on('petugas')->onDelete('cascade');

            $table->index(['petugas_id', 'bulan']);
            $table->index('bulan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penagihan');
    }
};