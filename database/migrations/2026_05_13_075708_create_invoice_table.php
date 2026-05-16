<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();

            $table->string('idpel');
            $table->string('nomor_surat')->unique();

            $table->string('bulan')->nullable();

            $table->decimal('total_tagihan', 15, 2)->default(0);

            $table->integer('ranking')->nullable();

            $table->enum('status_cetak', [
                'belum',
                'sudah'
            ])->default('belum');

            $table->timestamps();

            $table->index('idpel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};