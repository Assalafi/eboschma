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
        Schema::table('facility_wallets', function (Blueprint $table) {
            $table->string('wallet_number')->unique()->nullable()->after('id');
            $table->foreignId('program_id')->nullable()->after('facility_id')->constrained('programs')->onDelete('cascade');
            
            $table->dropForeign(['facility_id']);
            $table->dropUnique(['facility_id']);
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            
            $table->unique(['facility_id', 'program_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_wallets', function (Blueprint $table) {
            $table->dropUnique(['facility_id', 'program_id']);
            $table->unique('facility_id');
            
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
            $table->dropColumn('wallet_number');
        });
    }
};
