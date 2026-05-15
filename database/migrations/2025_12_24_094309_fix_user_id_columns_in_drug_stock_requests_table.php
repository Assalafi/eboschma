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
        Schema::table('drug_stock_requests', function (Blueprint $table) {
            // Change user ID columns from unsignedBigInteger to char(36) to match users table
            $table->char('requested_by', 36)->change();
            $table->char('approved_by', 36)->nullable()->change();
            $table->char('dispensed_by', 36)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drug_stock_requests', function (Blueprint $table) {
            // Revert back to unsignedBigInteger
            $table->unsignedBigInteger('requested_by')->change();
            $table->unsignedBigInteger('approved_by')->nullable()->change();
            $table->unsignedBigInteger('dispensed_by')->nullable()->change();
        });
    }
};
