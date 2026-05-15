@echo off
echo Starting file upload to server...
echo.

REM Upload bulk-stock-request.blade.php
echo Uploading bulk-stock-request.blade.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/facility/pharmacy/bulk-stock-request.blade.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/facility/pharmacy/

REM Upload dispense.blade.php
echo Uploading dispense.blade.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/dispense.blade.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/

REM Upload DrugStockRequestController.php
echo Uploading DrugStockRequestController.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/app/Http/Controllers/DrugStockRequestController.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/

REM Upload show.blade.php
echo Uploading show.blade.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/show.blade.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/

REM Upload facility-requests.blade.php
echo Uploading facility-requests.blade.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/facility-requests.blade.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/

REM Upload edit.blade.php
echo Uploading edit.blade.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/edit.blade.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/

REM Upload create.blade.php
echo Uploading create.blade.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/resources/views/drug-stock-requests/create.blade.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/

REM Upload migration file
echo Uploading migration file...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/database/migrations/2026_03_14_170000_increase_estimated_cost_column_size.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/database/migrations/

REM Upload web.php
echo Uploading web.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/routes/web.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/routes/

REM Upload debug.php
echo Uploading debug.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/routes/debug.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/routes/

REM Upload Drug.php model
echo Uploading Drug.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/app/Models/Drug.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/app/Models/

REM Upload PharmacyController.php
echo Uploading PharmacyController.php...
scp "C:/xampp/htdocs/Boschma/admin.enrolment.boschma/app/Http/Controllers/Facility/PharmacyController.php" root@67.205.161.212:/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/Facility/

echo.
echo Upload process completed!
pause
