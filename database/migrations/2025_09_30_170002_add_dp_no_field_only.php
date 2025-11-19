<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing category data to match new enum values
        DB::table('beneficiaries')
            ->where('category', 'Civil Service')
            ->update(['category' => 'GL/STEP']);
            
        DB::table('beneficiaries')
            ->where('category', 'Employee')
            ->update(['category' => 'GL/STEP']);
            
        DB::table('beneficiaries')
            ->where('category', 'Retiree')
            ->update(['category' => 'GL/STEP']);
            
        DB::table('beneficiaries')
            ->where('category', 'Board Member')
            ->update(['category' => 'Others']);
            
        DB::table('beneficiaries')
            ->where('category', 'Staff')
            ->update(['category' => 'GL/STEP']);
            
        DB::table('beneficiaries')
            ->where('category', 'Other')
            ->update(['category' => 'Others']);

        // Add dp_no field to beneficiaries table
        if (Schema::hasTable('beneficiaries')) {
            Schema::table('beneficiaries', function (Blueprint $table) {
                $columns = Schema::getColumnListing('beneficiaries');
                
                // Add dp_no field if it doesn't exist
                if (!in_array('dp_no', $columns)) {
                    $table->string('dp_no')->nullable()->after('occupation');
                }
            });
        }
        
        // Now update the category enum using raw SQL
        DB::statement("ALTER TABLE beneficiaries MODIFY COLUMN category ENUM('GL/STEP', 'Organized Private Sector', 'Others') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove dp_no field from beneficiaries
        if (Schema::hasTable('beneficiaries')) {
            Schema::table('beneficiaries', function (Blueprint $table) {
                $columns = Schema::getColumnListing('beneficiaries');
                
                if (in_array('dp_no', $columns)) {
                    $table->dropColumn('dp_no');
                }
            });
        }
        
        // Revert category enum
        DB::statement("ALTER TABLE beneficiaries MODIFY COLUMN category ENUM('Civil Service', 'Organized Private Sector', 'Others') NULL");
        
        // Revert category data
        DB::table('beneficiaries')
            ->where('category', 'GL/STEP')
            ->update(['category' => 'Civil Service']);
    }
};
