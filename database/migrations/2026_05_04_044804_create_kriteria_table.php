<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();   // C1, C2, C3, C4
            $table->string('nama');                  // Nama kriteria
            $table->enum('tipe', ['benefit', 'cost']); // benefit = max, cost = min
            $table->decimal('bobot', 5, 2);          // Bobot 0–100 (total harus = 100)
            $table->timestamps();
        });

        // Seed default kriteria SAW
        DB::table('kriteria')->insert([
            [
                'kode'   => 'C1',
                'nama'   => 'Total Jumlah Bayar',
                'tipe'   => 'benefit',
                'bobot'  => 30.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode'   => 'C2',
                'nama'   => 'Status Lunas',
                'tipe'   => 'benefit',
                'bobot'  => 25.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode'   => 'C3',
                'nama'   => 'Jumlah Penagihan',
                'tipe'   => 'benefit',
                'bobot'  => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode'   => 'C4',
                'nama'   => 'Klasifikasi Tunggakan',
                'tipe'   => 'cost',
                'bobot'  => 25.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria');
    }
};