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
            // Add staff tracking columns
            $table->uuid('created_by')->nullable()->after('updated_at')->comment('Staff who created/started the enrollment');
            $table->uuid('submitted_by')->nullable()->after('created_by')->comment('Staff who finalized/submitted the enrollment');
            $table->uuid('updated_by')->nullable()->after('submitted_by')->comment('Staff who last updated the record');
            
            // Add foreign key constraints
            $table->foreign('created_by')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('submitted_by')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('staff')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['updated_by']);
            
            // Drop columns
            $table->dropColumn(['created_by', 'submitted_by', 'updated_by']);
        });
    }
};
