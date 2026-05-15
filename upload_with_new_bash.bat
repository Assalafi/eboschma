@echo off
echo Using bash from path without spaces...
echo.

REM Test if new bash path works
echo Testing bash path...
C:\git\bin\bash.exe -c "echo 'Bash test successful!'"

if %errorLevel% neq 0 (
    echo ERROR: Bash not found at C:\git\bin\bash.exe
    echo Please run create_git_symlink.bat first
    pause
    exit /b 1
)

echo.
echo Listing remote directory...
C:\git\bin\bash.exe -c "ssh root@67.205.161.212 'ls -la /var/www/BornoStateGovernment/eboschma'"

echo.
echo Starting file uploads...
echo.

REM Upload files using new bash path
echo Uploading bulk-stock-request.blade.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/facility/pharmacy/bulk-stock-request.blade.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/facility/pharmacy/"

echo Uploading dispense.blade.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/dispense.blade.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"

echo Uploading DrugStockRequestController.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/app/Http/Controllers/DrugStockRequestController.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/"

echo Uploading show.blade.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/show.blade.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"

echo Uploading facility-requests.blade.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/facility-requests.blade.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"

echo Uploading edit.blade.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/edit.blade.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"

echo Uploading create.blade.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/create.blade.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"

echo Uploading migration file...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/database/migrations/2026_03_14_170000_increase_estimated_cost_column_size.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/database/migrations/"

echo Uploading web.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/routes/web.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/routes/"

echo Uploading debug.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/routes/debug.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/routes/"

echo Uploading Drug.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/app/Models/Drug.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/app/Models/"

echo Uploading PharmacyController.php...
C:\git\bin\bash.exe -c "scp 'C:/xampp/htdocs/Boschma/admin.enrolment.boschma/app/Http/Controllers/Facility/PharmacyController.php' root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/Facility/"

echo.
echo Upload process completed!
pause
