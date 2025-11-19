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
        // Update spouses table if it exists
        if (Schema::hasTable('spouses')) {
            Schema::table('spouses', function (Blueprint $table) {
                // Get existing columns
                $columns = Schema::getColumnListing('spouses');
                
                // Drop existing columns if they exist
                $columnsToDrop = [];
                if (in_array('name', $columns)) $columnsToDrop[] = 'name';
                if (in_array('age', $columns)) $columnsToDrop[] = 'age';
                if (in_array('date_of_birth', $columns)) $columnsToDrop[] = 'date_of_birth';
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Add new columns if they don't exist
                if (!in_array('surname', $columns)) $table->string('surname')->nullable();
                if (!in_array('first_name', $columns)) $table->string('first_name')->nullable();
                if (!in_array('other_name', $columns)) $table->string('other_name')->nullable();
                if (!in_array('dob', $columns)) $table->date('dob')->nullable();
                if (!in_array('remarks', $columns)) $table->text('remarks')->nullable();
                
                // Rename phone_no to phone if it exists
                if (in_array('phone_no', $columns) && !in_array('phone', $columns)) {
                    $table->renameColumn('phone_no', 'phone');
                }
            });
        }
        
        // Update children table if it exists
        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                // Get existing columns
                $columns = Schema::getColumnListing('children');
                
                // Drop existing columns if they exist
                $columnsToDrop = [];
                if (in_array('name', $columns)) $columnsToDrop[] = 'name';
                if (in_array('age', $columns)) $columnsToDrop[] = 'age';
                if (in_array('date_of_birth', $columns)) $columnsToDrop[] = 'date_of_birth';
                if (in_array('birth_certificate_no', $columns)) $columnsToDrop[] = 'birth_certificate_no';
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Add new columns if they don't exist
                if (!in_array('surname', $columns)) $table->string('surname')->nullable();
                if (!in_array('first_name', $columns)) $table->string('first_name')->nullable();
                if (!in_array('other_name', $columns)) $table->string('other_name')->nullable();
                if (!in_array('dob', $columns)) $table->date('dob')->nullable();
                if (!in_array('remarks', $columns)) $table->text('remarks')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert spouses table changes if table exists
        if (Schema::hasTable('spouses')) {
            Schema::table('spouses', function (Blueprint $table) {
                // Get existing columns
                $columns = Schema::getColumnListing('spouses');
                
                // Remove new columns if they exist
                $columnsToDrop = [];
                if (in_array('surname', $columns)) $columnsToDrop[] = 'surname';
                if (in_array('first_name', $columns)) $columnsToDrop[] = 'first_name';
                if (in_array('other_name', $columns)) $columnsToDrop[] = 'other_name';
                if (in_array('dob', $columns)) $columnsToDrop[] = 'dob';
                if (in_array('remarks', $columns)) $columnsToDrop[] = 'remarks';
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Add back original columns
                if (!in_array('name', $columns)) $table->string('name')->nullable();
                if (!in_array('age', $columns)) $table->integer('age')->nullable();
                if (!in_array('date_of_birth', $columns)) $table->date('date_of_birth')->nullable();
                
                // Rename phone back to phone_no if needed
                if (in_array('phone', $columns) && !in_array('phone_no', $columns)) {
                    $table->renameColumn('phone', 'phone_no');
                }
            });
        }
        
        // Revert children table changes if table exists
        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                // Get existing columns
                $columns = Schema::getColumnListing('children');
                
                // Remove new columns if they exist
                $columnsToDrop = [];
                if (in_array('surname', $columns)) $columnsToDrop[] = 'surname';
                if (in_array('first_name', $columns)) $columnsToDrop[] = 'first_name';
                if (in_array('other_name', $columns)) $columnsToDrop[] = 'other_name';
                if (in_array('dob', $columns)) $columnsToDrop[] = 'dob';
                if (in_array('remarks', $columns)) $columnsToDrop[] = 'remarks';
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Add back original columns
                if (!in_array('name', $columns)) $table->string('name')->nullable();
                if (!in_array('age', $columns)) $table->integer('age')->nullable();
                if (!in_array('date_of_birth', $columns)) $table->date('date_of_birth')->nullable();
                if (!in_array('birth_certificate_no', $columns)) $table->string('birth_certificate_no')->nullable();
            });
        }
    }
};
