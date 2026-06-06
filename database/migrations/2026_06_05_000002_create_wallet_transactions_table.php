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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->string('type'); // funding, drug_stock_deduction, dispensation_return, adjustment
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference')->nullable(); // links to stock request ID or dispensation ID
            $table->string('reference_type')->nullable(); // DrugStockRequest, PharmacyDispensation
            $table->string('drug_name')->nullable(); // for dispensation_return records
            $table->integer('drug_quantity')->nullable(); // for dispensation_return records
            $table->decimal('drug_cost', 15, 2)->nullable(); // full cost before 10%
            $table->text('description')->nullable();
            $table->string('performed_by')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('facility_wallets')->onDelete('cascade');
            $table->index(['wallet_id', 'type']);
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
