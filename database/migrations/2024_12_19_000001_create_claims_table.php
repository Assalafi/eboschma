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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('authorization_code', 50)->unique();
            $table->string('beneficiary_name');
            $table->string('boschma_id', 50);
            $table->string('nin', 11)->nullable();
            $table->string('phone_number', 20)->nullable();
            
            // Claim details
            $table->enum('claim_type', ['medical', 'pharmacy', 'hospitalization', 'diagnostic', 'emergency']);
            $table->string('healthcare_provider');
            $table->enum('provider_type', ['hospital', 'clinic', 'pharmacy', 'laboratory', 'diagnostic_center']);
            $table->date('service_date');
            $table->decimal('claim_amount', 12, 2);
            $table->text('diagnosis')->nullable();
            $table->text('treatment_description')->nullable();
            $table->text('additional_notes')->nullable();
            
            // Status and workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->enum('ro_status', ['', 'approved', 'rejected'])->default('');
            $table->timestamp('ro_review_date')->nullable();
            $table->unsignedBigInteger('ro_reviewer_id')->nullable();
            $table->text('ro_notes')->nullable();
            
            $table->enum('e5_status', ['', 'approved', 'rejected'])->default('');
            $table->timestamp('e5_approval_date')->nullable();
            $table->unsignedBigInteger('e5_reviewer_id')->nullable();
            $table->text('e5_notes')->nullable();
            
            // Rejection and payment details
            $table->text('rejection_reason')->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('paid_amount', 12, 2)->nullable();
            $table->string('payment_method', 50)->nullable();
            
            // Supporting documents
            $table->string('medical_report')->nullable();
            $table->string('prescription')->nullable();
            $table->string('receipt')->nullable();
            
            // Audit trail - using staff table for authentication
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('authorization_code');
            $table->index('beneficiary_name');
            $table->index('boschma_id');
            $table->index('nin');
            $table->index('status');
            $table->index('claim_type');
            $table->index('service_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
