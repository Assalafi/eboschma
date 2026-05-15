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
        Schema::create('facility_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number')->unique();
            $table->char('encounter_id', 36)->nullable();
            $table->foreign('encounter_id')->references('id')->on('encounters')->onDelete('cascade');
            $table->unsignedBigInteger('facility_id');
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->char('patient_id', 36)->nullable();
            $table->string('enrollee_number')->nullable();
            $table->string('enrollee_type')->nullable(); // beneficiary, spouse, child
            $table->string('file_number')->nullable();
            
            // Patient info
            $table->string('patient_name')->nullable();
            $table->string('boschma_no')->nullable();
            $table->string('nin')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            
            // Claim details
            $table->enum('claim_type', ['outpatient', 'inpatient', 'emergency', 'referral'])->default('outpatient');
            $table->date('service_date')->nullable();
            $table->date('admission_date')->nullable();
            $table->date('discharge_date')->nullable();
            $table->integer('length_of_stay')->nullable();
            
            // Financial
            $table->decimal('consultation_amount', 10, 2)->default(0);
            $table->decimal('pharmacy_amount', 10, 2)->default(0);
            $table->decimal('laboratory_amount', 10, 2)->default(0);
            $table->decimal('services_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            
            // Status tracking
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'paid'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Approval workflow
            $table->char('submitted_by', 36)->nullable();
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();
            $table->char('reviewed_by', 36)->nullable();
            $table->foreign('reviewed_by')->references('id')->on('staff')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->char('approved_by', 36)->nullable();
            $table->foreign('approved_by')->references('id')->on('staff')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Payment
            $table->string('payment_reference')->nullable();
            $table->date('payment_date')->nullable();
            $table->char('paid_by', 36)->nullable();
            $table->foreign('paid_by')->references('id')->on('staff')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('encounter_id');
            $table->index('facility_id');
            $table->index('enrollee_number');
            $table->index('status');
            $table->index('service_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_claims');
    }
};
