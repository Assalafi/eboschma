<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$claim = DB::table('facility_claims')->whereNotNull('encounter_id')->first();
if (!$claim) { echo "No claims with encounter_id\n"; exit; }
echo "Claim ID: {$claim->id}, Encounter: {$claim->encounter_id}\n";

$enc = App\Models\Encounter::with('consultations.diagnoses.icdCode')->find($claim->encounter_id);
if (!$enc) { echo "Encounter not found\n"; exit; }
echo "Consultations: " . $enc->consultations->count() . "\n";

foreach ($enc->consultations as $con) {
    echo "  Consultation {$con->id}: diagnoses=" . $con->diagnoses->count() . "\n";
    foreach ($con->diagnoses as $dx) {
        $code = $dx->icdCode ? $dx->icdCode->code : 'NO_ICD';
        $desc = $dx->icdCode ? $dx->icdCode->description : 'NO_ICD';
        echo "    Type={$dx->diagnosis_type} Code={$code} Desc={$desc}\n";
    }
}
