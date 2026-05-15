#!/bin/bash

# Upload script for Git Bash - run from MINGW64
# Usage: ./upload_from_git_bash.sh

echo "=== UPLOADING FILES TO SERVER ==="
echo "Server: root@67.205.161.212"
echo ""

# Files to upload
declare -A files=(
    ["resources/views/facility/pharmacy/bulk-stock-request.blade.php"]="/var/www/BornoStateGovernment/eboschma/resources/views/facility/pharmacy/"
    ["resources/views/drug-stock-requests/dispense.blade.php"]="/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
    ["app/Http/Controllers/DrugStockRequestController.php"]="/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/"
    ["resources/views/drug-stock-requests/show.blade.php"]="/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
    ["resources/views/drug-stock-requests/facility-requests.blade.php"]="/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
    ["resources/views/drug-stock-requests/edit.blade.php"]="/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
    ["resources/views/drug-stock-requests/create.blade.php"]="/var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/"
    ["database/migrations/2026_03_14_170000_increase_estimated_cost_column_size.php"]="/var/www/BornoStateGovernment/eboschma/database/migrations/"
    ["routes/web.php"]="/var/www/BornoStateGovernment/eboschma/routes/"
    ["routes/debug.php"]="/var/www/BornoStateGovernment/eboschma/routes/"
    ["app/Models/Drug.php"]="/var/www/BornoStateGovernment/eboschma/app/Models/"
    ["app/Http/Controllers/Facility/PharmacyController.php"]="/var/www/BornoStateGovernment/eboschma/app/Http/Controllers/Facility/"
)

success_count=0
fail_count=0

for local_file in "${!files[@]}"; do
    remote_path="${files[$local_file]}"
    filename=$(basename "$local_file")
    
    echo "Uploading: $filename"
    
    if [ -f "$local_file" ]; then
        if scp "$local_file" "root@67.205.161.212:$remote_path"; then
            echo "  ✓ SUCCESS"
            ((success_count++))
        else
            echo "  ✗ FAILED"
            ((fail_count++))
        fi
    else
        echo "  ✗ FILE NOT FOUND: $local_file"
        ((fail_count++))
    fi
    echo ""
done

echo "=== UPLOAD SUMMARY ==="
echo "Successful: $success_count"
echo "Failed: $fail_count"

if [ $fail_count -eq 0 ]; then
    echo "🎉 All files uploaded successfully!"
else
    echo "⚠️  Some uploads failed"
fi

echo ""
echo "Verifying uploads..."
ssh root@67.205.161.212 "ls -la /var/www/BornoStateGovernment/eboschma/resources/views/facility/pharmacy/ | grep bulk-stock"
ssh root@67.205.161.212 "ls -la /var/www/BornoStateGovernment/eboschma/resources/views/drug-stock-requests/ | head -5"
