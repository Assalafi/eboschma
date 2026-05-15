@echo off
echo Configuring Windsurf to use PowerShell by default...
echo.

REM Run PowerShell configuration script
powershell -ExecutionPolicy Bypass -File "C:\xampp\htdocs\Boschma\admin.enrolment.boschma\configure_windsurf_powershell.ps1"

echo.
echo Configuration complete!
echo Please restart Windsurf for changes to take effect.
echo.
pause
