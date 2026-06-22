<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sessions = DB::table('sessions')->get();
foreach($sessions as $session){
    $payload = base64_decode($session->payload);
    $data = @unserialize($payload);
    if($data && isset($data['import_preview']) && count($data['import_preview']) > 0){
        print_r(array_slice($data['import_preview'], 0, 1));
        break;
    }
}
