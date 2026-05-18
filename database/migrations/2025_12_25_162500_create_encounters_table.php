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
        if (!Schema::hasTable('encounters')) {
            Schema::create('encounters', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('patient_id', 36);
                $table->unsignedBigInteger('facility_id');
                $table->unsignedBigInteger('program_id')->nullable();
                $table->timestamp('visit_date')->useCurrent();
                $table->string('nature_of_visit')->nullable();
                $table->string('mode_of_entry')->nullable();
                $table->text('reason_for_visit')->nullable();
                $table->char('officer_in_charge_id', 36)->nullable();
                $table->string('status')->default('Registered');
                $table->timestamps();

                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
                $table->foreign('program_id')->references('id')->on('programs')->onDelete('set null');
                $table->foreign('officer_in_charge_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
