<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sessions = DB::table('sessions')->get();
foreach($sessions as $session){
    $payload = base64_decode($session->payload);
    $data = @unserialize($payload);
    if($data && isset($data['import_errors']) && count($data['import_errors']) > 0){
        print_r(array_slice($data['import_errors'], 0, 5));
        echo "\nTotal Errors: " . count($data['import_errors']) . "\n";
        break;
    }
}
