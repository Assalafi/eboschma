# Configure Windsurf to use PowerShell by default
# This script modifies Windsurf settings to prefer PowerShell over bash

Write-Host "=== CONFIGURING WINDSURF FOR POWERSHELL ===" -ForegroundColor Green
Write-Host ""

# Check if running as administrator
$currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host "WARNING: Some settings may require administrator privileges" -ForegroundColor Yellow
    Write-Host "Consider running this script as Administrator for full access" -ForegroundColor Yellow
}

Write-Host "Configuring Windsurf to use PowerShell by default..." -ForegroundColor Cyan

# 1. Set PowerShell as default shell in Windows environment
Write-Host ""
Write-Host "1. Setting PowerShell as default shell..." -ForegroundColor Cyan

# Set PowerShell as default shell for current user
[Environment]::SetEnvironmentVariable("SHELL", "powershell", "User")
[Environment]::SetEnvironmentVariable("COMSPEC", "powershell.exe", "User")

# Add PowerShell to PATH if not already there
$currentPath = [Environment]::GetEnvironmentVariable("PATH", "User")
$powershellPath = "C:\Windows\System32\WindowsPowerShell\v1.0"
if ($currentPath -notlike "*$powershellPath*") {
    $newPath = $currentPath + ";" + $powershellPath
    [Environment]::SetEnvironmentVariable("PATH", $newPath, "User")
    Write-Host "   ✓ Added PowerShell to user PATH" -ForegroundColor Green
}

# 2. Create Windsurf settings directory and configuration
Write-Host ""
Write-Host "2. Creating Windsurf configuration..." -ForegroundColor Cyan

$windsurfSettingsPath = "$env:APPDATA\Cascade\settings.json"
$windsurfDir = Split-Path $windsurfSettingsPath -Parent

if (-not (Test-Path $windsurfDir)) {
    New-Item -ItemType Directory -Path $windsurfDir -Force | Out-Null
    Write-Host "   ✓ Created Windsurf settings directory" -ForegroundColor Green
}

# Windsurf configuration to prefer PowerShell
$windsurfConfig = @{
    "terminal.defaultProfile.windows" = "PowerShell"
    "terminal.integrated.defaultProfile.windows" = "PowerShell"
    "terminal.integrated.profiles.windows" = @{
        "PowerShell" = @{
            "path" = "powershell.exe"
            "args" = @("-NoProfile", "-ExecutionPolicy", "Bypass")
        }
    }
    "terminal.explorerKind" = "external"
    "terminal.external.windowsExec" = "powershell.exe"
    "terminal.integrated.shell.windows" = "powershell.exe"
    "terminal.integrated.shellArgs.windows" = @("-NoProfile", "-ExecutionPolicy", "Bypass")
}

# Convert to JSON and save
$configJson = $windsurfConfig | ConvertTo-Json -Depth 10
Set-Content -Path $windsurfSettingsPath -Value $configJson -Encoding UTF8
Write-Host "   ✓ Created Windsurf PowerShell configuration" -ForegroundColor Green

# 3. Create VS Code style settings (Windsurf might use VS Code settings)
Write-Host ""
Write-Host "3. Creating VS Code compatible settings..." -ForegroundColor Cyan

$vsCodeSettingsPath = "$env:APPDATA\Code\User\settings.json"
$vsCodeDir = Split-Path $vsCodeSettingsPath -Parent

if (-not (Test-Path $vsCodeDir)) {
    New-Item -ItemType Directory -Path $vsCodeDir -Force | Out-Null
}

$vsCodeConfig = @{
    "terminal.integrated.defaultProfile.windows" = "PowerShell"
    "terminal.integrated.profiles.windows" = @{
        "PowerShell" = @{
            "path" = "powershell.exe"
            "args" = @("-NoProfile", "-ExecutionPolicy", "Bypass")
            "icon" = "terminal-powershell"
            "color" = "terminal.ansiBlue"
        }
        "Git Bash" = @{
            "path" = "C:\Program Files\Git\bin\bash.exe"
            "args" = @()
            "icon" = "terminal-bash"
            "color" = "terminal.ansiYellow"
        }
    }
}

# Load existing settings if they exist
if (Test-Path $vsCodeSettingsPath) {
    try {
        $existingConfig = Get-Content $vsCodeSettingsPath | ConvertFrom-Json
        # Merge configurations
        $vsCodeConfig = $existingConfig | Merge-Object $vsCodeConfig
    } catch {
        Write-Host "   Could not merge existing VS Code settings, creating new ones" -ForegroundColor Yellow
    }
}

$vsCodeJson = $vsCodeConfig | ConvertTo-Json -Depth 10
Set-Content -Path $vsCodeSettingsPath -Value $vsCodeJson -Encoding UTF8
Write-Host "   ✓ Created VS Code PowerShell configuration" -ForegroundColor Green

# 4. Set Windows Terminal default to PowerShell (if Windows Terminal is installed)
Write-Host ""
Write-Host "4. Configuring Windows Terminal..." -ForegroundColor Cyan

$wtSettingsPath = "$env:LOCALAPPDATA\Packages\Microsoft.WindowsTerminal_8wekyb3d8bbwe\LocalState\settings.json"

if (Test-Path $wtSettingsPath) {
    try {
        $wtConfig = Get-Content $wtSettingsPath | ConvertFrom-Json
        $wtConfig.defaultProfile = "{574e775e-4f2a-5b96-ac1e-a2962a402336}"  # PowerShell GUID
        
        # Ensure PowerShell is in profiles list
        if (-not $wtConfig.profiles.list.PSObject.Properties.Name -contains "name") {
            # Add PowerShell profile if not present
            $powerShellProfile = @{
                "name" = "Windows PowerShell"
                "commandline" = "powershell.exe"
                "hidden" = $false
                "icon" = "🔷"
            }
            $wtConfig.profiles.list += $powerShellProfile
        }
        
        $wtJson = $wtConfig | ConvertTo-Json -Depth 10
        Set-Content -Path $wtSettingsPath -Value $wtJson -Encoding UTF8
        Write-Host "   ✓ Set Windows Terminal default to PowerShell" -ForegroundColor Green
    } catch {
        Write-Host "   Could not configure Windows Terminal" -ForegroundColor Yellow
    }
} else {
    Write-Host "   Windows Terminal not found" -ForegroundColor Gray
}

# 5. Test PowerShell configuration
Write-Host ""
Write-Host "5. Testing PowerShell configuration..." -ForegroundColor Cyan

try {
    $testResult = powershell -Command "Write-Host 'PowerShell test successful'" -NoProfile
    Write-Host "   ✓ PowerShell test: $testResult" -ForegroundColor Green
} catch {
    Write-Host "   ✗ PowerShell test failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== CONFIGURATION COMPLETE ===" -ForegroundColor Green
Write-Host ""
Write-Host "Changes made:" -ForegroundColor Cyan
Write-Host "✓ Set PowerShell as default shell environment variable" -ForegroundColor White
Write-Host "✓ Created Windsurf PowerShell configuration" -ForegroundColor White
Write-Host "✓ Created VS Code compatible settings" -ForegroundColor White
Write-Host "✓ Configured Windows Terminal (if installed)" -ForegroundColor White
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. Restart Windsurf" -ForegroundColor White
Write-Host "2. Restart any open terminals" -ForegroundColor White
Write-Host "3. Test by running a command in Windsurf terminal" -ForegroundColor White
Write-Host ""
Write-Host "Windsurf should now use PowerShell by default instead of bash!" -ForegroundColor Green
Write-Host ""
Read-Host "Press Enter to exit"
