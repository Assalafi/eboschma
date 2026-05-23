<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/reports/ehr/drilldown', 'GET', [
    'type' => 'encounters_by_status',
    'status' => 'Registered',
    'facility_id' => '11',
    'date_from' => '2026-05-08',
    'date_to' => '2026-05-23'
]);
$response = $kernel->handle($request);
echo $response->getContent();
