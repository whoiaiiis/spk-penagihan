<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            
            // Kolom idpel harus string default (255) dan unique agar sesuai dengan
            // referensi di tabel penagihan: $table->string('idpel', 255);
            $table->string('idpel')->unique();
            
            $table->string('nama');
            
            // Casts dari model
            $table->integer('daya')->default(0);
            $table->integer('lembar')->default(0);
            $table->double('rptag', 15, 2)->default(0);
            $table->double('rpbk', 15, 2)->default(0);
            
            $table->string('status_penagihan')->nullable();
            $table->integer('historis_tunggakan')->default(0);
            
            // Relasi ke tabel petugas
            $table->unsignedBigInteger('petugas_id')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
