<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('normalisasi', function (Blueprint $table) {
            $table->id();
            $table->string('idpel');
            $table->decimal('r1', 8, 6)->default(0); // Normalisasi C1
            $table->decimal('r2', 8, 6)->default(0); // Normalisasi C2
            $table->decimal('r3', 8, 6)->default(0); // Normalisasi C3
            $table->decimal('r4', 8, 6)->default(0); // Normalisasi C4
            $table->timestamps();

            $table->index('idpel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('normalisasi');
    }
};