<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/reports/ehr/drilldown', 'GET', [
    'type' => 'awaiting_pharmacy',
    'facility_id' => '11'
]);

$controller = new \App\Http\Controllers\EhrReportController();
$response = $controller->drilldown($request);
echo $response->getContent();
