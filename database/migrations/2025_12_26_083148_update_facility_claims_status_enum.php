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
            // Update the status enum to include new workflow values
            $table->enum('status', [
                'draft', 'submitted', 'verified', 'approved', 'es_approved', 'paid', 'rejected', 'under_review'
            ])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_claims', function (Blueprint $table) {
            // Revert to original status values
            $table->enum('status', [
                'draft', 'submitted', 'approved', 'paid', 'rejected', 'under_review'
            ])->default('draft')->change();
        });
    }
};
