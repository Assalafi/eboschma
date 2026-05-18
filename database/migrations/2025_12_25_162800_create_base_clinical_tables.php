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
        // clinical_consultations table
        if (!Schema::hasTable('clinical_consultations')) {
            Schema::create('clinical_consultations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->timestamps();
            });
        }

        // prescriptions table
        if (!Schema::hasTable('prescriptions')) {
            Schema::create('prescriptions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->timestamps();
            });
        }

        // prescription_items table
        if (!Schema::hasTable('prescription_items')) {
            Schema::create('prescription_items', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->timestamps();
            });
        }

        // service_orders table
        if (!Schema::hasTable('service_orders')) {
            Schema::create('service_orders', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // service_order_items table
        if (!Schema::hasTable('service_order_items')) {
            Schema::create('service_order_items', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // clinical_diagnoses table
        if (!Schema::hasTable('clinical_diagnoses')) {
            Schema::create('clinical_diagnoses', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // encounter_actions table
        if (!Schema::hasTable('encounter_actions')) {
            Schema::create('encounter_actions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounter_actions');
        Schema::dropIfExists('clinical_diagnoses');
        Schema::dropIfExists('service_order_items');
        Schema::dropIfExists('service_orders');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('clinical_consultations');
    }
};
