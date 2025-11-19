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
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Add sequence_number column for storing the numeric part (000001, 000002, etc.)
            // This makes it easier to manage sequential IDs
            $table->unsignedInteger('sequence_number')->nullable()->after('boschma_no');
            
            // Add unique index for faster lookups and prevent duplicates
            $table->unique('sequence_number');
            
            // Add index for better query performance
            $table->index('sequence_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropIndex(['sequence_number']);
            $table->dropUnique(['sequence_number']);
            $table->dropColumn('sequence_number');
        });
    }
};
