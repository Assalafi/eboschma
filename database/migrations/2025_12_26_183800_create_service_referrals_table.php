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
        Schema::create('service_referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('encounter_id');
            $table->unsignedBigInteger('from_facility_id');
            $table->unsignedBigInteger('to_facility_id');
            $table->enum('referral_type', ['service', 'patient'])->default('service');
            $table->uuid('service_item_id')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'accepted', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();

            // Foreign keys
            $table->foreign('encounter_id')->references('id')->on('encounters')->onDelete('cascade');
            $table->foreign('from_facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->foreign('to_facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->foreign('service_item_id')->references('id')->on('service_items')->onDelete('set null');

            // Indexes
            $table->index('encounter_id');
            $table->index('from_facility_id');
            $table->index('to_facility_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_referrals');
    }
};
