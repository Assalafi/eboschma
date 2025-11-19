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
            // Add missing columns that don't already exist
            // Most columns already exist in the base table, only adding new ones if needed
            if (!Schema::hasColumn('beneficiaries', 'signature_date')) {
                $table->date('signature_date')->nullable();
            }
            // Note: All other columns already exist in the base beneficiaries table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Only drop columns that were actually added in the up() method
            if (Schema::hasColumn('beneficiaries', 'signature_date')) {
                $table->dropColumn('signature_date');
            }
        });
    }
};
