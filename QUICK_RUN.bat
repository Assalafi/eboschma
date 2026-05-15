@echo off
echo Opening PowerShell as Administrator and running SETUP ONLY...
echo.

REM This will open PowerShell as Admin and run the setup script (no uploads)
powershell -Command "Start-Process powershell -Verb RunAs -ArgumentList '-NoProfile -ExecutionPolicy Bypass -File \"C:\xampp\htdocs\Boschma\admin.enrolment.boschma\setup_only.ps1\"'"

echo.
echo PowerShell Administrator window should open automatically...
echo If it asks for permission, click Yes
echo.
echo This will ONLY setup the bash path - NO FILES WILL BE UPLOADED
echo To upload files later, run: upload_files.ps1
echo.
pause
