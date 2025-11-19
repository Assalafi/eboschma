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
        // Add NIN to beneficiaries table
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->string('nin', 11)->nullable()->after('id_no');
        });

        // Add NIN to spouses table
        Schema::table('spouses', function (Blueprint $table) {
            $table->string('nin', 11)->nullable()->after('boschma_no');
        });

        // Add NIN to children table
        Schema::table('children', function (Blueprint $table) {
            $table->string('nin', 11)->nullable()->after('boschma_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove NIN from beneficiaries table
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropColumn('nin');
        });

        // Remove NIN from spouses table
        Schema::table('spouses', function (Blueprint $table) {
            $table->dropColumn('nin');
        });

        // Remove NIN from children table
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn('nin');
        });
    }
};
