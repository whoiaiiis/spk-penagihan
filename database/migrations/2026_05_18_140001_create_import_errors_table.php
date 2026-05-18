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
        Schema::create('import_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->constrained('import_logs')->onDelete('cascade');
            $table->integer('row_number')->nullable();
            $table->json('row_data')->nullable();
            $table->text('error_message')->nullable();
            $table->enum('error_type', [
                'validation',
                'duplicate',
                'not_found',
                'missing_column',
                'format_invalid',
            ])->default('validation');
            $table->timestamps();

            $table->index('import_log_id');
            $table->index('error_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_errors');
    }
};
