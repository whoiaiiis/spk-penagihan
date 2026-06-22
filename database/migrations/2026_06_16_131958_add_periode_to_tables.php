<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $currentPeriode = date('Y-m');
        
        Schema::table('pelanggan', function (Blueprint $table) use ($currentPeriode) {
            $table->string('periode', 7)->default($currentPeriode)->after('idpel');
            // Drop unique index idpel if exists
            $indexes = Schema::getIndexes('pelanggan');
            $hasUnique = false;
            foreach ($indexes as $index) {
                if ($index['name'] === 'idpel' || $index['name'] === 'pelanggan_idpel_unique') {
                    $hasUnique = true;
                    $table->dropUnique($index['name']);
                    break;
                }
            }
        });
        
        // Add composite unique index
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->unique(['idpel', 'periode'], 'pelanggan_idpel_periode_unique');
        });

        Schema::table('penilaian', function (Blueprint $table) use ($currentPeriode) {
            $table->string('periode', 7)->default($currentPeriode)->after('idpel');
            $table->index('periode');
        });
        
        Schema::table('normalisasi', function (Blueprint $table) use ($currentPeriode) {
            $table->string('periode', 7)->default($currentPeriode)->after('idpel');
            $table->index('periode');
        });

        Schema::table('ranking', function (Blueprint $table) use ($currentPeriode) {
            $table->string('periode', 7)->default($currentPeriode)->after('idpel');
            $table->index('periode');
        });

        Schema::table('invoice', function (Blueprint $table) use ($currentPeriode) {
            $table->string('periode', 7)->default($currentPeriode)->after('idpel');
            $table->index('periode');
        });
    }

    public function down(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropIndex(['periode']);
            $table->dropColumn('periode');
        });

        Schema::table('ranking', function (Blueprint $table) {
            $table->dropIndex(['periode']);
            $table->dropColumn('periode');
        });
        
        Schema::table('normalisasi', function (Blueprint $table) {
            $table->dropIndex(['periode']);
            $table->dropColumn('periode');
        });

        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropIndex(['periode']);
            $table->dropColumn('periode');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropUnique('pelanggan_idpel_periode_unique');
            $table->unique('idpel', 'idpel'); // restore old unique index
            $table->dropColumn('periode');
        });
    }
};
