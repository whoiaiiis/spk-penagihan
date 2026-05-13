<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('idpel');                    // FK ke data pelanggan
            $table->decimal('c1', 15, 2)->default(0);  // Total jumlah bayar
            $table->decimal('c2', 5, 2)->default(0);   // Status lunas (0 atau 1)
            $table->integer('c3')->default(0);          // Jumlah penagihan
            $table->tinyInteger('c4')->default(1);      // Klasifikasi tunggakan (1–4)
            $table->decimal('total_nilai', 8, 4)->default(0); // Nilai akhir SAW
            $table->timestamps();

            $table->index('idpel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};