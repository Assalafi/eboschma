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
        
        // Now update the category enum
        DB::statement("ALTER TABLE beneficiaries MODIFY COLUMN category ENUM('GL/STEP', 'Organized Private Sector', 'Others') NULL");
        
        // Update spouses table to use simplified structure (single name field)
        if (Schema::hasTable('spouses')) {
            Schema::table('spouses', function (Blueprint $table) {
                $columns = Schema::getColumnListing('spouses');
                
                // First, migrate existing data to the new name field
                if (in_array('surname', $columns) && in_array('first_name', $columns)) {
                    // Combine existing name fields into single name field
                    DB::statement("UPDATE spouses SET name = CONCAT_WS(' ', first_name, other_name, surname) WHERE name IS NULL OR name = ''");
                }
                
                // Drop separate name fields if they exist
                $columnsToDrop = [];
                if (in_array('surname', $columns)) $columnsToDrop[] = 'surname';
                if (in_array('first_name', $columns)) $columnsToDrop[] = 'first_name';
                if (in_array('other_name', $columns)) $columnsToDrop[] = 'other_name';
                if (in_array('age', $columns)) $columnsToDrop[] = 'age';
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Add simplified name field if it doesn't exist
                if (!in_array('name', $columns)) {
                    $table->string('name')->nullable()->after('boschma_no');
                }
            });
        }
        
        // Update children table to use simplified structure (single name field)
        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                $columns = Schema::getColumnListing('children');
                
                // First, migrate existing data to the new name field
                if (in_array('surname', $columns) && in_array('first_name', $columns)) {
                    // Combine existing name fields into single name field
                    DB::statement("UPDATE children SET name = CONCAT_WS(' ', first_name, other_name, surname) WHERE name IS NULL OR name = ''");
                }
                
                // Drop separate name fields if they exist
                $columnsToDrop = [];
                if (in_array('surname', $columns)) $columnsToDrop[] = 'surname';
                if (in_array('first_name', $columns)) $columnsToDrop[] = 'first_name';
                if (in_array('other_name', $columns)) $columnsToDrop[] = 'other_name';
                if (in_array('age', $columns)) $columnsToDrop[] = 'age';
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Add simplified name field if it doesn't exist
                if (!in_array('name', $columns)) {
                    $table->string('name')->nullable()->after('boschma_no');
                }
                
                // Ensure we have birth_certificate_no and birth_certificate_file fields
                if (!in_array('birth_certificate_no', $columns)) {
                    $table->string('birth_certificate_no')->nullable();
                }
                if (!in_array('birth_certificate_file', $columns)) {
                    $table->string('birth_certificate_file')->nullable();
                }
            });
        }
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
        
        // Revert spouses table to separated fields
        if (Schema::hasTable('spouses')) {
            Schema::table('spouses', function (Blueprint $table) {
                $columns = Schema::getColumnListing('spouses');
                
                // Drop simplified name field
                if (in_array('name', $columns)) {
                    $table->dropColumn('name');
                }
                
                // Add back separate name fields
                $table->string('surname')->nullable();
                $table->string('first_name')->nullable();
                $table->string('other_name')->nullable();
                $table->integer('age')->nullable();
            });
        }
        
        // Revert children table to separated fields
        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                $columns = Schema::getColumnListing('children');
                
                // Drop simplified name field
                if (in_array('name', $columns)) {
                    $table->dropColumn('name');
                }
                
                // Add back separate name fields
                $table->string('surname')->nullable();
                $table->string('first_name')->nullable();
                $table->string('other_name')->nullable();
                $table->integer('age')->nullable();
            });
        }
    }
};
