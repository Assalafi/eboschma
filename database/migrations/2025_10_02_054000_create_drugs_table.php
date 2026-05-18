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
        if (!Schema::hasTable('drugs')) {
            Schema::create('drugs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('dosage_form');
                $table->string('strength');
                $table->string('unit');
                $table->decimal('unit_price', 10, 2);
                $table->unsignedBigInteger('facility_id')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
                $table->index(['name', 'facility_id']);
                $table->index(['dosage_form']);
                $table->index(['status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
