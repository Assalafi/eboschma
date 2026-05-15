<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find the 'formal' program ID
        $formalProgramId = DB::table('programs')
            ->whereRaw('LOWER(name) LIKE ?', ['%formal%'])
            ->value('id');

        // Add program_id to drug_store_stocks
        Schema::table('drug_store_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable()->after('drug_id');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('set null');
        });

        // Add program_id to drug_stock_requests
        Schema::table('drug_stock_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable()->after('facility_id');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('set null');
        });

        // Add program_id to drug_stocks
        Schema::table('drug_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable()->after('facility_id');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('set null');
        });

        // Set existing records to the 'formal' program
        if ($formalProgramId) {
            DB::table('drug_store_stocks')->whereNull('program_id')->update(['program_id' => $formalProgramId]);
            DB::table('drug_stock_requests')->whereNull('program_id')->update(['program_id' => $formalProgramId]);
            DB::table('drug_stocks')->whereNull('program_id')->update(['program_id' => $formalProgramId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drug_store_stocks', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::table('drug_stock_requests', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::table('drug_stocks', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
