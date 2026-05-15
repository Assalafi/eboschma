# Upload files script - run after setup is complete
# Usage: .\upload_files.ps1

Write-Host "=== FILE UPLOAD ONLY ===" -ForegroundColor Green
Write-Host ""

# Check if bash path exists
if (-not (Test-Path "C:\git\bin\bash.exe")) {
    Write-Host "ERROR: Bash not found at C:\git\bin\bash.exe!" -ForegroundColor Red
    Write-Host "Please run setup_only.ps1 first to create the bash path." -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✓ Found bash at: C:\git\bin\bash.exe" -ForegroundColor Green

# Test SSH connection
Write-Host "Testing SSH connection..." -ForegroundColor Cyan
try {
    $sshTest = & "C:\git\bin\bash.exe" -c "ssh -o 'ConnectTimeout=10' root@67.205.161.212 'echo SSH connection successful'"
    Write-Host "✓ SSH connection working: $sshTest" -ForegroundColor Green
} catch {
    Write-Host "✗ SSH connection failed!" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Make sure you can authenticate with the server" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# List remote directory
Write-Host "Current remote directory contents:" -ForegroundColor Cyan
& "C:\git\bin\bash.exe" -c "ssh root@67.205.161.212 'ls -la /var/www/BornoStateGovernment/eboschma'"

Write-Host ""
Write-Host "=== UPLOADING FILES ===" -ForegroundColor Green

$files = @(
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\facility\pharmacy\bulk-stock-request.blade.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/resources/views/facility/pharmacy/"
        Name = "bulk-stock-request.blade.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\dispense.blade.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
        Name = "dispense.blade.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\app\Http\Controllers\DrugStockRequestController.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/"
        Name = "DrugStockRequestController.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\show.blade.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
        Name = "show.blade.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\facility-requests.blade.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
        Name = "facility-requests.blade.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\edit.blade.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
        Name = "edit.blade.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\resources\views\drug-stock-requests\create.blade.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
        Name = "create.blade.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\database\migrations\2026_03_14_170000_increase_estimated_cost_column_size.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/database/migrations/"
        Name = "migration file"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\routes\web.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/routes/"
        Name = "web.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\routes\debug.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/routes/"
        Name = "debug.php"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\app\Models\Drug.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/app/Models/"
        Name = "Drug.php model"
    },
    @{
        Local = "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\app\Http\Controllers\Facility\PharmacyController.php"
        Remote = "/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/Facility/"
        Name = "PharmacyController.php"
    }
)

$successCount = 0
$failCount = 0

foreach ($file in $files) {
    Write-Host "Uploading: $($file.Name)" -ForegroundColor Cyan
    
    if (Test-Path $file.Local) {
        try {
            $localPath = $file.Local -replace '\\', '/'
            $command = "scp '$localPath' root@67.205.161.212:'$($file.Remote)'"
            & "C:\git\bin\bash.exe" -c $command
            
            if ($LASTEXITCODE -eq 0) {
                Write-Host "  ✓ SUCCESS" -ForegroundColor Green
                $successCount++
            } else {
                Write-Host "  ✗ FAILED" -ForegroundColor Red
                $failCount++
            }
        } catch {
            Write-Host "  ✗ FAILED: $($_.Exception.Message)" -ForegroundColor Red
            $failCount++
        }
    } else {
        Write-Host "  ✗ FILE NOT FOUND: $($file.Local)" -ForegroundColor Red
        $failCount++
    }
}

# Summary
Write-Host ""
Write-Host "=== UPLOAD SUMMARY ===" -ForegroundColor Green
Write-Host "Successful uploads: $successCount" -ForegroundColor Green
Write-Host "Failed uploads: $failCount" -ForegroundColor Red

if ($failCount -eq 0) {
    Write-Host "🎉 All files uploaded successfully!" -ForegroundColor Green
} else {
    Write-Host "⚠️  Some uploads failed. Check the errors above." -ForegroundColor Yellow
}

Write-Host ""
Read-Host "Press Enter to exit"
