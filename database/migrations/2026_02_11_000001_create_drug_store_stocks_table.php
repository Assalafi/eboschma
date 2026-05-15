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
        Schema::create('drug_store_stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Drug reference
            $table->char('drug_id', 36);

            // Batch tracking
            $table->string('batch_number', 100);
            $table->date('expiry_date');

            // Quantities
            $table->integer('quantity_received');
            $table->integer('quantity_remaining');
            $table->integer('quantity_dispensed')->default(0);

            // Cost
            $table->decimal('unit_cost', 10, 2)->default(0);

            // Supplier info
            $table->string('supplier', 255)->nullable();
            $table->text('notes')->nullable();

            // Status
            $table->enum('status', ['active', 'depleted', 'expired'])->default('active');

            // Tracking
            $table->char('stocked_by', 36)->nullable();
            $table->timestamp('stocked_at')->useCurrent();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['drug_id', 'status']);
            $table->index(['expiry_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_store_stocks');
    }
};
