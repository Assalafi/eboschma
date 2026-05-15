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
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['created_by']);
            
            // Change the column type to char(36) to match staff UUIDs
            $table->char('created_by', 36)->change();
            
            // Add the new foreign key constraint to staff table
            $table->foreign('created_by')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the foreign key to staff
            $table->dropForeign(['created_by']);
            
            // Change back to foreignId (bigint) for users table
            $table->foreignId('created_by')->change();
            
            // Add the foreign key constraint back to users table
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
