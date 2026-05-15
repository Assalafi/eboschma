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
        Schema::create('beneficiary_logins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 150);
            $table->unsignedBigInteger('civil_servant_id');
            $table->unsignedBigInteger('program_id')->default('1');
            $table->string('status', 20)->default('Active');
            $table->timestamps();

            // Foreign keys
            $table->foreign('civil_servant_id')->references('id')->on('civil_servants')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');

            // Indexes
            $table->index('email');
            $table->index('civil_servant_id');
            $table->index('program_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_logins');
    }
};
