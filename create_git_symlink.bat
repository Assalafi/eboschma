@echo off
echo Creating Git symlink to path without spaces...
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

REM Remove existing symlink if it exists
if exist C:\git (
    echo Removing existing C:\git...
    rmdir C:\git
)

REM Create symlink
echo Creating symlink: C:\git -> "C:\Program Files\Git"
mklink /D C:\git "C:\Program Files\Git"

if %errorLevel% == 0 (
    echo.
    echo SUCCESS: Symlink created!
    echo.
    echo Testing new bash path:
    C:\git\bin\bash.exe -c "echo 'Bash is working from path without spaces!'"
    echo.
    echo You can now use: C:\git\bin\bash.exe -c "your commands"
) else (
    echo.
    echo FAILED: Could not create symlink!
    echo Make sure Git is installed in "C:\Program Files\Git"
)

echo.
pause
