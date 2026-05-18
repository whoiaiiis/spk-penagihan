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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['pelanggan', 'penagihan'])->default('pelanggan');
            $table->string('filename')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('inserted')->default(0);
            $table->integer('updated')->default(0);
            $table->integer('failed')->default(0);
            $table->string('imported_by')->nullable();
            $table->timestamp('import_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('import_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
