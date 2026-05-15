# Setup script only - creates bash path without spaces
# Run this with: Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
# Then: .\setup_only.ps1

Write-Host "=== BASH PATH SETUP (NO UPLOADS) ===" -ForegroundColor Green
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

Write-Host ""
Write-Host "=== SETUP COMPLETE ===" -ForegroundColor Green
Write-Host "✓ Bash path without spaces created: C:\git\bin\bash.exe" -ForegroundColor Green
Write-Host "✓ SSH connection tested" -ForegroundColor Green
Write-Host ""
Write-Host "To upload files later, run: upload_files.ps1" -ForegroundColor Cyan
Write-Host ""
Read-Host "Press Enter to exit"
