<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$referral = App\Models\ServiceReferral::with(['encounter.patient.enrolleeDetails'])->latest()->first();

// Fake the generation to see what base64 string is produced
$patient = $referral->encounter->patient ?? null;
$enrolleeDetails = $patient ? $patient->enrolleeDetails : null;
$photoPath = null;
if ($enrolleeDetails && $enrolleeDetails->photo) {
    if (str_starts_with($enrolleeDetails->photo, 'http')) {
        $photoPath = $enrolleeDetails->photo;
    } else {
        $photoPath = storage_path('app/public/' . $enrolleeDetails->photo);
    }
}
if (!$photoPath || !file_exists($photoPath) && !str_starts_with($photoPath, 'http')) {
    $photoPath = public_path('assets/img/users/1.jpg');
}

$base64Photo = '';
if ($photoPath) {
    try {
        if (str_starts_with($photoPath, 'http')) {
            $imgData = file_get_contents($photoPath);
            $base64Photo = 'data:image/jpeg;base64,' . base64_encode($imgData);
        } elseif (file_exists($photoPath)) {
            $type = pathinfo($photoPath, PATHINFO_EXTENSION);
            if ($type == 'jpg') $type = 'jpeg';
            $imgData = file_get_contents($photoPath);
            $base64Photo = 'data:image/' . $type . ';base64,' . substr(base64_encode($imgData), 0, 50) . '...';
        }
    } catch (\Exception $e) {
        $base64Photo = 'ERROR: ' . $e->getMessage();
    }
}

echo "Enrollee Photo DB Value: " . ($enrolleeDetails->photo ?? 'NULL') . "\n";
echo "Resolved Photo Path: " . $photoPath . "\n";
echo "File Exists: " . (file_exists($photoPath) ? 'Yes' : 'No') . "\n";
echo "Base64: " . $base64Photo . "\n";
