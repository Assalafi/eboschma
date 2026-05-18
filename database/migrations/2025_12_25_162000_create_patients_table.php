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
        if (!Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('file_number')->unique()->nullable();
                $table->string('enrollee_number')->unique()->nullable();
                $table->string('enrollee_type')->nullable(); // beneficiary, spouse, child
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
