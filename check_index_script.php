<?php
$pelanggan = DB::select('SHOW INDEX FROM pelanggan');
$penilaian = DB::select('SHOW INDEX FROM penilaian');
$ranking = DB::select('SHOW INDEX FROM ranking');
$invoice = DB::select('SHOW INDEX FROM invoice');

echo json_encode(['pelanggan' => $pelanggan, 'penilaian' => $penilaian, 'ranking' => $ranking, 'invoice' => $invoice], JSON_PRETTY_PRINT);
