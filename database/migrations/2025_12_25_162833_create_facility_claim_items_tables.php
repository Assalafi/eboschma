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
        // Consultations claimed
        Schema::create('facility_claim_consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_claim_id');
            $table->foreign('facility_claim_id')->references('id')->on('facility_claims')->onDelete('cascade');
            $table->char('consultation_id', 36)->nullable();
            $table->foreign('consultation_id')->references('id')->on('clinical_consultations')->onDelete('set null');
            $table->string('diagnosis_code')->nullable();
            $table->text('diagnosis_description')->nullable();
            $table->text('consultation_notes')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
        });

        // Prescriptions/Medications claimed
        Schema::create('facility_claim_medications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_claim_id');
            $table->foreign('facility_claim_id')->references('id')->on('facility_claims')->onDelete('cascade');
            $table->char('prescription_item_id', 36)->nullable();
            $table->foreign('prescription_item_id')->references('id')->on('prescription_items')->onDelete('set null');
            $table->string('drug_name');
            $table->string('dosage')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('days')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Laboratory/Service Orders claimed
        Schema::create('facility_claim_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_claim_id');
            $table->foreign('facility_claim_id')->references('id')->on('facility_claims')->onDelete('cascade');
            $table->unsignedBigInteger('service_order_item_id')->nullable();
            $table->foreign('service_order_item_id')->references('id')->on('service_order_items')->onDelete('set null');
            $table->string('service_type')->nullable(); // lab, imaging, procedure
            $table->string('service_name');
            $table->text('service_description')->nullable();
            $table->integer('frequency')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Diagnoses (non-priced but important for claims)
        Schema::create('facility_claim_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_claim_id');
            $table->foreign('facility_claim_id')->references('id')->on('facility_claims')->onDelete('cascade');
            $table->unsignedBigInteger('diagnosis_id')->nullable();
            $table->foreign('diagnosis_id')->references('id')->on('clinical_diagnoses')->onDelete('set null');
            $table->string('icd_code')->nullable();
            $table->string('diagnosis_type')->default('primary'); // primary, secondary
            $table->text('diagnosis_description');
            $table->timestamps();
        });

        // Encounter Actions (non-priced activities)
        Schema::create('facility_claim_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_claim_id');
            $table->foreign('facility_claim_id')->references('id')->on('facility_claims')->onDelete('cascade');
            $table->char('encounter_action_id', 36)->nullable();
            $table->foreign('encounter_action_id')->references('id')->on('encounter_actions')->onDelete('set null');
            $table->string('activity_type')->nullable(); // vitals, assessment, procedure
            $table->text('activity_description');
            $table->timestamp('performed_at')->nullable();
            $table->timestamps();
        });

        // Documents/Attachments
        Schema::create('facility_claim_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_claim_id');
            $table->foreign('facility_claim_id')->references('id')->on('facility_claims')->onDelete('cascade');
            $table->string('document_type'); // prescription, lab_result, medical_report, receipt
            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_claim_documents');
        Schema::dropIfExists('facility_claim_activities');
        Schema::dropIfExists('facility_claim_diagnoses');
        Schema::dropIfExists('facility_claim_services');
        Schema::dropIfExists('facility_claim_medications');
        Schema::dropIfExists('facility_claim_consultations');
    }
};
