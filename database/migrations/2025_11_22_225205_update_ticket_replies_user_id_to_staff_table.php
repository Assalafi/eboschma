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
        Schema::table('ticket_replies', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Change the column type to char(36) to match staff UUIDs
            $table->char('user_id', 36)->change();
            
            // Add the new foreign key constraint to staff table
            $table->foreign('user_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_replies', function (Blueprint $table) {
            // Drop the foreign key to staff
            $table->dropForeign(['user_id']);
            
            // Change back to foreignId (bigint) for users table
            $table->foreignId('user_id')->change();
            
            // Add the foreign key constraint back to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
