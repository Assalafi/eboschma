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
        Schema::create('drug_stock_requests', function (Blueprint $table) {
            $table->id();
            
            // Request details
            $table->unsignedBigInteger('facility_id');
            $table->char('drug_id', 36);
            $table->integer('quantity_requested');
            $table->decimal('estimated_cost', 10, 2);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            
            // Request tracking
            $table->enum('status', ['pending', 'approved', 'rejected', 'dispensed'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // User tracking
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('dispensed_by')->nullable();
            
            // Timestamps
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->timestamps();
            
            // Rejection tracking
            $table->text('rejection_reason')->nullable();
            
            // Foreign keys - will be added in separate migration
            // $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            // $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('cascade');
            // $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('dispensed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['status', 'facility_id']);
            $table->index(['priority', 'status']);
            $table->index(['requested_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_stock_requests');
    }
};
