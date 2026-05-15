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
        // Drop existing foreign key constraint if it exists using raw SQL
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::statement('ALTER TABLE tickets DROP FOREIGN KEY IF EXISTS tickets_assigned_to_foreign');
        } catch (\Exception $e) {
            // Ignore if foreign key doesn't exist
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        Schema::table('tickets', function (Blueprint $table) {
            // Change column type from bigint to char to match staff.id (UUID)
            $table->string('assigned_to', 36)->nullable()->change();
            
            // Add new foreign key constraint pointing to staff table
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('staff')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the staff foreign key
            $table->dropForeign(['assigned_to']);
            
            // Change column type back to bigint to match users.id
            $table->bigInteger('assigned_to')->unsigned()->nullable()->change();
            
            // Restore the users foreign key
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
