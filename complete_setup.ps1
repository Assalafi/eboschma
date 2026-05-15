# Complete setup and upload script
# Run this with: Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
# Then: .\complete_setup.ps1

Write-Host "=== COMPLETE SETUP AND UPLOAD ===" -ForegroundColor Green
Write-Host ""

# Step 1: Check if running as administrator
$currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host "ERROR: Please run this script as Administrator!" -ForegroundColor Red
    Write-Host "Right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✓ Running with administrator privileges" -ForegroundColor Green

# Step 2: Remove existing symlink if it exists
if (Test-Path "C:\git") {
    Write-Host "Removing existing C:\git..." -ForegroundColor Yellow
    cmd /c "rmdir C:\git" 2>$null
}

# Step 3: Create symlink
Write-Host "Creating symlink: C:\git -> 'C:\Program Files\Git'" -ForegroundColor Cyan
$result = cmd /c 'mklink /D C:\git "C:\Program Files\Git" 2>&1'

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Symlink created successfully!" -ForegroundColor Green
} else {
    Write-Host "✗ Failed to create symlink!" -ForegroundColor Red
    Write-Host "Error: $result" -ForegroundColor Red
    Write-Host "Make sure Git is installed in 'C:\Program Files\Git'" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Step 4: Test bash
Write-Host "Testing bash from new path..." -ForegroundColor Cyan
try {
    $bashTest = & "C:\git\bin\bash.exe" -c "echo 'Bash test successful!'"
    Write-Host "✓ Bash is working: $bashTest" -ForegroundColor Green
} catch {
    Write-Host "✗ Bash test failed!" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

# Step 5: Test SSH connection
Write-Host "Testing SSH connection to server..." -ForegroundColor Cyan
try {
    $sshTest = & "C:\git\bin\bash.exe" -c "ssh -o 'ConnectTimeout=10' root@67.205.161.212 'echo SSH connection successful'"
    Write-Host "✓ SSH connection working: $sshTest" -ForegroundColor Green
} catch {
    Write-Host "✗ SSH connection failed!" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Make sure SSH keys are set up or you can authenticate with password" -ForegroundColor Yellow
}

# Step 6: List remote directory
Write-Host "Listing remote directory..." -ForegroundColor Cyan
try {
    & "C:\git\bin\bash.exe" -c "ssh root@67.205.161.212 'ls -la /var/www/BornoStateGovernment/eboschma'"
} catch {
    Write-Host "✗ Failed to list remote directory!" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Step 7: Upload files
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

# Step 8: Summary
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
Write-Host "=== SETUP COMPLETE ===" -ForegroundColor Green
Write-Host "You can now use: C:\git\bin\bash.exe -c 'your commands'" -ForegroundColor Cyan
Read-Host "Press Enter to exit"
