# PowerShell test script - runs without bash dependency
# Usage: .\test_setup_powershell.ps1

Write-Host "=== POWERSHELL SETUP TEST ===" -ForegroundColor Green
Write-Host ""

# Test 1: Check if C:\git exists
Write-Host "1. Checking if C:\git directory exists..." -ForegroundColor Cyan
if (Test-Path "C:\git") {
    Write-Host "   ✓ C:\git directory found" -ForegroundColor Green
    
    # Check if it's a symlink
    $item = Get-Item "C:\git" -ErrorAction SilentlyContinue
    if ($item -and $item.Attributes -band [System.IO.FileAttributes]::ReparsePoint) {
        Write-Host "   ✓ C:\git is a symlink" -ForegroundColor Green
        $target = (Get-Item "C:\git").Target
        Write-Host "   → Points to: $target" -ForegroundColor Gray
    }
} else {
    Write-Host "   ✗ C:\git directory NOT found" -ForegroundColor Red
    Write-Host "   → Setup may not have completed" -ForegroundColor Yellow
}

# Test 2: Check bash executable
Write-Host ""
Write-Host "2. Checking bash executable..." -ForegroundColor Cyan
if (Test-Path "C:\git\bin\bash.exe") {
    Write-Host "   ✓ bash.exe found at C:\git\bin\bash.exe" -ForegroundColor Green
} else {
    Write-Host "   ✗ bash.exe NOT found at C:\git\bin\bash.exe" -ForegroundColor Red
}

# Test 3: Test bash functionality
Write-Host ""
Write-Host "3. Testing bash functionality..." -ForegroundColor Cyan
try {
    $bashTest = & "C:\git\bin\bash.exe" -c "echo 'Bash test successful'"
    Write-Host "   ✓ Bash output: $bashTest" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Bash test failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Test SSH connection
Write-Host ""
Write-Host "4. Testing SSH connection..." -ForegroundColor Cyan
try {
    $sshTest = & "C:\git\bin\bash.exe" -c "ssh -o 'ConnectTimeout=5' root@67.205.161.212 'echo SSH connection successful'"
    Write-Host "   ✓ SSH output: $sshTest" -ForegroundColor Green
} catch {
    Write-Host "   ✗ SSH failed: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   → May need SSH keys or password authentication" -ForegroundColor Yellow
}

# Test 5: Test SCP
Write-Host ""
Write-Host "5. Testing SCP availability..." -ForegroundColor Cyan
try {
    $scpVersion = & "C:\git\bin\bash.exe" -c "scp -V 2>&1"
    Write-Host "   ✓ SCP is available" -ForegroundColor Green
} catch {
    Write-Host "   ✗ SCP not available: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 6: List remote directory (if SSH works)
Write-Host ""
Write-Host "6. Testing remote directory access..." -ForegroundColor Cyan
try {
    $remoteList = & "C:\git\bin\bash.exe" -c "ssh root@67.205.161.212 'ls -la /var/www/BornoStateGovernment/eboschma' 2>&1"
    Write-Host "   ✓ Remote directory listing:" -ForegroundColor Green
    Write-Host $remoteList -ForegroundColor Gray
} catch {
    Write-Host "   ✗ Remote access failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== TEST SUMMARY ===" -ForegroundColor Green

if (Test-Path "C:\git\bin\bash.exe") {
    Write-Host "🎉 Setup appears successful!" -ForegroundColor Green
    Write-Host "You can now upload files with:" -ForegroundColor Cyan
    Write-Host "   .\upload_files.ps1" -ForegroundColor White
} else {
    Write-Host "❌ Setup failed - bash not accessible" -ForegroundColor Red
    Write-Host "Please run the setup again:" -ForegroundColor Yellow
    Write-Host "   QUICK_RUN.bat" -ForegroundColor White
}

Write-Host ""
Read-Host "Press Enter to exit"
