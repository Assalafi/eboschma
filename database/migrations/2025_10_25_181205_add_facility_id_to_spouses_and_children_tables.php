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
        // Add facility_id to spouses table
        Schema::table('spouses', function (Blueprint $table) {
            $table->unsignedBigInteger('facility_id')->nullable()->after('beneficiary_id');
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('set null');
        });

        // Add facility_id to children table
        Schema::table('children', function (Blueprint $table) {
            $table->unsignedBigInteger('facility_id')->nullable()->after('beneficiary_id');
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop from spouses table
        Schema::table('spouses', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropColumn('facility_id');
        });

        // Drop from children table
        Schema::table('children', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropColumn('facility_id');
        });
    }
};
