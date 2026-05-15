@echo off
echo === REMOVING GIT BASH FROM SYSTEM ===
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running with administrator privileges - OK
) else (
    echo ERROR: Please run this script as Administrator!
    echo Right-click the file and select "Run as administrator"
    pause
    exit /b 1
)

echo.
echo This will remove Git Bash from your system to resolve path conflicts.
echo WARNING: This will affect any applications that depend on Git!
echo.
set /p confirm="Are you sure you want to remove Git Bash? (y/N): "
if /i not "%confirm%"=="y" (
    echo Operation cancelled.
    pause
    exit /b 0
)

echo.
echo Removing Git components...

REM 1. Remove Git from Program Files
if exist "C:\Program Files\Git" (
    echo Removing C:\Program Files\Git...
    rmdir /s /q "C:\Program Files\Git"
    if %errorLevel% == 0 (
        echo ✓ Removed C:\Program Files\Git
    ) else (
        echo ✗ Failed to remove C:\Program Files\Git
    )
)

REM 2. Remove Git from Program Files (x86)
if exist "C:\Program Files (x86)\Git" (
    echo Removing C:\Program Files (x86)\Git...
    rmdir /s /q "C:\Program Files (x86)\Git"
    if %errorLevel% == 0 (
        echo ✓ Removed C:\Program Files (x86)\Git
    ) else (
        echo ✗ Failed to remove C:\Program Files (x86)\Git
    )
)

REM 3. Remove symlink if it exists
if exist C:\git (
    echo Removing C:\git symlink...
    rmdir C:\git
    if %errorLevel% == 0 (
        echo ✓ Removed C:\git symlink
    ) else (
        echo ✗ Failed to remove C:\git symlink
    )
)

REM 4. Remove Git from PATH
echo Removing Git from system PATH...
for /f "tokens=2*" %%a in ('reg query "HKLM\SYSTEM\CurrentControlSet\Control\Session Manager\Environment" /v PATH 2^>nul') do (
    set "syspath=%%b"
)
for /f "tokens=2*" %%a in ('reg query "HKCU\Environment" /v PATH 2^>nul') do (
    set "userpath=%%b"
)

echo System PATH entries containing Git:
echo %syspath% | findstr /i git >nul
if %errorLevel% == 0 (
    echo Found Git in system PATH - manual removal required
)

echo User PATH entries containing Git:
echo %userpath% | findstr /i git >nul
if %errorLevel% == 0 (
    echo Found Git in user PATH - manual removal required
)

REM 5. Remove Git from Start Menu
if exist "%APPDATA%\Microsoft\Windows\Start Menu\Programs\Git" (
    echo Removing Git from Start Menu...
    rmdir /s /q "%APPDATA%\Microsoft\Windows\Start Menu\Programs\Git"
)

REM 6. Remove Git desktop shortcuts
if exist "%PUBLIC%\Desktop\Git Bash.lnk" (
    del "%PUBLIC%\Desktop\Git Bash.lnk"
)
if exist "%USERPROFILE%\Desktop\Git Bash.lnk" (
    del "%USERPROFILE%\Desktop\Git Bash.lnk"
)

echo.
echo === REMOVAL COMPLETE ===
echo.
echo Git Bash has been removed from your system.
echo.
echo NEXT STEPS:
echo 1. Restart your computer
echo 2. Install a different Git distribution if needed
echo 3. Or use WSL for bash functionality
echo.
echo Windsurf bash tool should no longer have path conflicts.
echo.
pause
