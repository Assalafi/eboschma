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
        Schema::table('drug_stocks', function (Blueprint $table) {
            // Add status column for tracking stock status
            $table->enum('status', ['pending', 'approved', 'dispensed', 'rejected', 'expired'])->default('pending')->after('notes');
            
            // Add request_id to link with drug_stock_requests
            $table->unsignedBigInteger('request_id')->nullable()->after('status');
            
            // Add approval/dispense tracking
            $table->unsignedBigInteger('approved_by')->nullable()->after('request_id');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('dispensed_by')->nullable()->after('approved_at');
            $table->timestamp('dispensed_at')->nullable()->after('dispensed_by');
            
            // Add rejection tracking
            $table->text('rejection_reason')->nullable()->after('dispensed_at');
            
            // Foreign keys
            $table->foreign('request_id')->references('id')->on('drug_stock_requests')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('dispensed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['status', 'facility_id']);
            $table->index(['request_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drug_stocks', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['request_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['dispensed_by']);
            
            // Drop columns
            $table->dropColumn(['status', 'request_id', 'approved_by', 'approved_at', 'dispensed_by', 'dispensed_at', 'rejection_reason']);
            
            // Drop indexes
            $table->dropIndex(['status', 'facility_id']);
            $table->dropIndex(['request_id']);
        });
    }
};
