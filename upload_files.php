<?php
/**
 * File upload script for deploying files to remote server
 */

$files = [
    // Local file => Remote destination
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\facility\pharmacy\bulk-stock-request.blade.php' 
        => '/var/www/BornoStateGovernment/eboschma/resources/views/facility/pharmacy/bulk-stock-request.blade.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\dispense.blade.php' 
        => '/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/dispense.blade.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\app\Http\Controllers\DrugStockRequestController.php' 
        => '/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/DrugStockRequestController.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\show.blade.php' 
        => '/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/show.blade.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\facility-requests.blade.php' 
        => '/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/facility-requests.blade.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\edit.blade.php' 
        => '/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/edit.blade.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\create.blade.php' 
        => '/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/create.blade.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\database\migrations\2026_03_14_170000_increase_estimated_cost_column_size.php' 
        => '/var/www/BornoStateGovernment/eboschma/database/migrations/2026_03_14_170000_increase_estimated_cost_column_size.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\routes\web.php' 
        => '/var/www/BornoStateGovernment/eboschma/routes/web.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\routes\debug.php' 
        => '/var/www/BornoStateGovernment/eboschma/routes/debug.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\app\Models\Drug.php' 
        => '/var/www/BornoStateGovernment/eboschma/app/Models/Drug.php',
    
    'C:\xampp\htdocs\Boschma\admin.enrolment.boschma\app\Http\Controllers\Facility\PharmacyController.php' 
        => '/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/Facility/PharmacyController.php',
];

echo "Starting file upload process...\n";
echo "Server: root@67.205.161.212\n\n";

foreach ($files as $local => $remote) {
    if (!file_exists($local)) {
        echo "ERROR: Local file not found: $local\n";
        continue;
    }
    
    echo "Uploading: " . basename($local) . "\n";
    echo "  From: $local\n";
    echo "  To: $remote\n";
    
    // Create scp command
    $escaped_local = str_replace('\\', '/', $local);
    $command = "scp \"$escaped_local\" root@67.205.161.212:\"$remote\"";
    
    echo "  Command: $command\n";
    
    // Execute the command
    $output = [];
    $return_code = 0;
    exec($command, $output, $return_code);
    
    if ($return_code === 0) {
        echo "  Status: SUCCESS\n";
    } else {
        echo "  Status: FAILED\n";
        echo "  Error: " . implode("\n", $output) . "\n";
    }
    
    echo "\n";
}

echo "Upload process completed.\n";
?>
