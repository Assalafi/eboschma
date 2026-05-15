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
        Schema::create('drug_stock_request_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->unsignedBigInteger('stock_request_id');
            $table->char('drug_id', 36);
            
            // Request details
            $table->integer('quantity_requested');
            $table->decimal('estimated_cost', 10, 2);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Timestamps
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('stock_request_id')->references('id')->on('drug_stock_requests')->onDelete('cascade');
            $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('cascade');
            
            // Indexes
            $table->index(['stock_request_id', 'drug_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_stock_request_items');
    }
};
