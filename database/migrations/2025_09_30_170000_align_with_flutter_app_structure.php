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
        // Add dp_no field to beneficiaries table
        if (Schema::hasTable('beneficiaries')) {
            Schema::table('beneficiaries', function (Blueprint $table) {
                $columns = Schema::getColumnListing('beneficiaries');
                
                // Add dp_no field if it doesn't exist
                if (!in_array('dp_no', $columns)) {
                    $table->string('dp_no')->nullable()->after('occupation');
                }
                
                // Update category enum to include GL/STEP instead of Civil Service
                $table->enum('category', ['GL/STEP', 'Organized Private Sector', 'Others'])->nullable()->change();
            });
        }
        
        // Update spouses table to use simplified structure (single name field)
        if (Schema::hasTable('spouses')) {
            Schema::table('spouses', function (Blueprint $table) {
                $columns = Schema::getColumnListing('spouses');
                
                // Drop separate name fields if they exist
                $columnsToDrop = [];
                if (in_array('surname', $columns)) $columnsToDrop[] = 'surname';
                if (in_array('first_name', $columns)) $columnsToDrop[] = 'first_name';
                if (in_array('other_name', $columns)) $columnsToDrop[] = 'other_name';
                if (in_array('age', $columns)) $columnsToDrop[] = 'age';
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Add simplified name field
                if (!in_array('name', $columns)) {
                    $table->string('name')->nullable()->after('boschma_no');
                }
            });
        }
        
        // Update children table to use simplified structure (single name field)
        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                $columns = Schema::getColumnListing('children');
                
                // Drop separate name fields if they exist
                $columnsToDrop = [];
                if (in_array('surname', $columns)) $columnsToDrop[] = 'surname';
                if (in_array('first_name', $columns)) $columnsToDrop[] = 'first_name';
                if (in_array('other_name', $columns)) $columnsToDrop[] = 'other_name';
                if (in_array('age', $columns)) $columnsToDrop[] = 'age';
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Add simplified name field
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
                
                // Revert category enum
                $table->enum('category', ['Civil Service', 'Organized Private Sector', 'Others'])->nullable()->change();
            });
        }
        
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
