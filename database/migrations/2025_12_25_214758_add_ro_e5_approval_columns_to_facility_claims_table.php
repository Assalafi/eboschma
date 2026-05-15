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
        Schema::table('facility_claims', function (Blueprint $table) {
            // RO (Regional Office) approval fields
            $table->string('ro_status')->nullable()->after('status');
            $table->timestamp('ro_updated_at')->nullable()->after('ro_status');
            $table->char('ro_updated_by', 36)->nullable()->after('ro_updated_at');
            
            // E5 approval fields
            $table->string('e5_status')->nullable()->after('ro_updated_by');
            $table->timestamp('e5_updated_at')->nullable()->after('e5_status');
            $table->char('e5_updated_by', 36)->nullable()->after('e5_updated_at');
            
            // Add foreign key constraints
            $table->foreign('ro_updated_by')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('e5_updated_by')->references('id')->on('staff')->onDelete('set null');
            
            // Add index on approval status columns for better query performance
            $table->index('ro_status');
            $table->index('e5_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_claims', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['ro_updated_by']);
            $table->dropForeign(['e5_updated_by']);
            
            // Drop indexes
            $table->dropIndex(['ro_status']);
            $table->dropIndex(['e5_status']);
            
            // Drop columns
            $table->dropColumn([
                'ro_status',
                'ro_updated_at',
                'ro_updated_by',
                'e5_status',
                'e5_updated_at',
                'e5_updated_by'
            ]);
        });
    }
};
