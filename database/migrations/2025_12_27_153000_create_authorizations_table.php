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
        Schema::create('authorizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('authorization_code')->unique();
            $table->uuid('patient_id');
            $table->uuid('encounter_id');
            $table->uuid('service_referral_id')->nullable();
            $table->char('approved_by', 36)->nullable();
            $table->datetime('expires_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('encounter_id')->references('id')->on('encounters')->onDelete('cascade');
            $table->foreign('service_referral_id')->references('id')->on('service_referrals')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('authorization_code');
            $table->index('patient_id');
            $table->index('encounter_id');
            $table->index('service_referral_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorizations');
    }
};
