<?php
$tables = ['pelanggan', 'penilaian', 'ranking', 'normalisasi', 'invoice', 'petugas'];
foreach ($tables as $table) {
    echo "Table: $table\n";
    $cols = DB::select("SHOW FULL COLUMNS FROM $table");
    foreach ($cols as $col) {
        if ($col->Collation) {
            echo "  {$col->Field}: {$col->Collation}\n";
        }
    }
}
