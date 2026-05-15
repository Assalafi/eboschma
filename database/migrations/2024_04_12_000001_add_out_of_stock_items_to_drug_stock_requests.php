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
            $table->json('out_of_stock_items')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drug_stock_requests', function (Blueprint $table) {
            $table->dropColumn('out_of_stock_items');
        });
    }
};
