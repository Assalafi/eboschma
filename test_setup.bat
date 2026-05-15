@echo off
echo Testing bash setup...
echo.

echo 1. Checking if C:\git exists...
if exist C:\git (
    echo    ✓ C:\git directory found
) else (
    echo    ✗ C:\git directory NOT found
    echo    Setup may not have completed successfully
)

echo.
echo 2. Checking if bash executable exists...
if exist C:\git\bin\bash.exe (
    echo    ✓ bash.exe found at C:\git\bin\bash.exe
) else (
    echo    ✗ bash.exe NOT found at C:\git\bin\bash.exe
)

echo.
echo 3. Testing bash functionality...
C:\git\bin\bash.exe -c "echo '    ✓ Bash test successful: Hello from global bash!'" 2>nul
if %errorlevel% equ 0 (
    echo    Bash is working correctly!
) else (
    echo    ✗ Bash test failed
)

echo.
echo 4. Testing SSH connection...
C:\git\bin\bash.exe -c "ssh -o 'ConnectTimeout=5' root@67.205.161.212 'echo SSH connection successful'" 2>nul
if %errorlevel% equ 0 (
    echo    ✓ SSH connection working
) else (
    echo    ✗ SSH connection failed (may need authentication setup)
)

echo.
echo 5. Testing SCP...
C:\git\bin\bash.exe -c "scp -V" 2>nul
if %errorlevel% equ 0 (
    echo    ✓ SCP is available
) else (
    echo    ✗ SCP not available
)

echo.
echo === SETUP TEST COMPLETE ===
echo.
echo If all tests passed, you can now upload files with:
echo   upload_files.ps1
echo.
pause
