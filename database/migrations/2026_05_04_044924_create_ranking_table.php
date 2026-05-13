<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranking', function (Blueprint $table) {
            $table->id();
            $table->string('idpel');
            $table->decimal('nilai_akhir', 10, 6)->default(0); // Vi = Σ (bobot × normalisasi)
            $table->integer('ranking')->default(0);
            $table->timestamps();

            $table->index(['ranking']);
            $table->index('idpel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking');
    }
};