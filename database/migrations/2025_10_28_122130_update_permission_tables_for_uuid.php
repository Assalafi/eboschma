<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Modify model_id column in model_has_roles
        \DB::statement('ALTER TABLE model_has_roles MODIFY model_id VARCHAR(36)');
        
        // Modify model_id column in model_has_permissions  
        \DB::statement('ALTER TABLE model_has_permissions MODIFY model_id VARCHAR(36)');
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Revert model_id column in model_has_roles
        \DB::statement('ALTER TABLE model_has_roles MODIFY model_id BIGINT UNSIGNED');
        
        // Revert model_id column in model_has_permissions  
        \DB::statement('ALTER TABLE model_has_permissions MODIFY model_id BIGINT UNSIGNED');
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
