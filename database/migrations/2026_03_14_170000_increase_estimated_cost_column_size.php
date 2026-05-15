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
            // Change estimated_cost to handle larger values (up to 999,999,999,999.99)
            $table->decimal('estimated_cost', 14, 2)->change();
        });
        
        Schema::table('drug_stock_request_items', function (Blueprint $table) {
            // Also update the items table to match
            $table->decimal('estimated_cost', 14, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drug_stock_requests', function (Blueprint $table) {
            $table->decimal('estimated_cost', 10, 2)->change();
        });
        
        Schema::table('drug_stock_request_items', function (Blueprint $table) {
            $table->decimal('estimated_cost', 10, 2)->change();
        });
    }
};
