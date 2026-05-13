<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix penilaian table columns to match SAW requirements:
 * - c1 = rptag (bigint)
 * - c2 = lembar (integer, not decimal)
 * - c3 = rpbk (bigint)
 * - c4 = daya (integer)
 * - norm_c1..c4, total_nilai already exist
 * - Replace kat_c4 with kategori (string)
 * - Remove bulan (not needed — all data from pelanggan)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            // Fix c1: was decimal(15,2), change to bigint for rptag
            $table->bigInteger('c1')->default(0)->change();

            // Fix c2: was decimal(5,4), change to integer for lembar
            $table->integer('c2')->default(0)->change();

            // Fix c3: was int, change to bigint for rpbk
            $table->bigInteger('c3')->default(0)->change();

            // c4 (daya) stays as int — fine

            // Add kategori column if not exists
            if (!Schema::hasColumn('penilaian', 'kategori')) {
                $table->string('kategori', 30)->default('Rendah')->after('total_nilai');
            }
        });

        // Remove kat_c4 if exists (replaced by kategori)
        if (Schema::hasColumn('penilaian', 'kat_c4')) {
            Schema::table('penilaian', function (Blueprint $table) {
                $table->dropColumn('kat_c4');
            });
        }

        // Remove bulan if exists (not needed)
        if (Schema::hasColumn('penilaian', 'bulan')) {
            Schema::table('penilaian', function (Blueprint $table) {
                $table->dropColumn('bulan');
            });
        }
    }

    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->decimal('c1', 15, 2)->default(0)->change();
            $table->decimal('c2', 5, 4)->default(0)->change();
            $table->integer('c3')->default(0)->change();

            if (Schema::hasColumn('penilaian', 'kategori')) {
                $table->dropColumn('kategori');
            }
            if (!Schema::hasColumn('penilaian', 'kat_c4')) {
                $table->tinyInteger('kat_c4')->default(1);
            }
            if (!Schema::hasColumn('penilaian', 'bulan')) {
                $table->string('bulan', 7)->nullable();
            }
        });
    }
};
