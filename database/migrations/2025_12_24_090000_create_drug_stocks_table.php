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
        if (!Schema::hasTable('drug_stocks')) {
            Schema::create('drug_stocks', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('drug_id', 36);
                $table->unsignedBigInteger('facility_id');
                $table->string('batch_number');
                $table->date('expiry_date');
                $table->integer('quantity_received');
                $table->integer('quantity_remaining');
                $table->decimal('unit_cost', 10, 2)->default(0.00);
                $table->string('supplier')->nullable();
                $table->text('notes')->nullable();
                $table->char('stocked_by', 36)->nullable();
                $table->timestamp('stocked_at')->nullable();
                $table->timestamps();

                $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('cascade');
                $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
                $table->foreign('stocked_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_stocks');
    }
};
