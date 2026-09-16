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
        if (!Schema::hasTable('beneficiary_facility_changes')) {
            Schema::create('beneficiary_facility_changes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('beneficiary_id')->index();
                $table->string('boschma_no')->nullable()->index();
                $table->unsignedBigInteger('old_facility_id')->nullable();
                $table->unsignedBigInteger('new_facility_id')->nullable();
                $table->unsignedBigInteger('old_alt_facility_id')->nullable();
                $table->unsignedBigInteger('new_alt_facility_id')->nullable();
                $table->char('changed_by', 36)->nullable()->index();
                $table->string('changed_via')->nullable(); // mobile, admin, import, system
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['old_facility_id', 'new_facility_id'], 'bfc_facilities_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_facility_changes');
    }
};
