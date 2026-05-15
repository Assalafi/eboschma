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
        Schema::create('facility_has_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id'); // Regular ID for facilities
            $table->char('service_id', 36); // UUID for services
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['facility_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_has_services');
    }
};
