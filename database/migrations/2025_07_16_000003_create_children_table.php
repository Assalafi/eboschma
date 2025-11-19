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
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->onDelete('cascade');
            $table->string('boschma_no')->unique(); // Will be primary beneficiary's number + B, C, D, or E
            $table->string('name');
            $table->enum('gender', ['Male', 'Female']);
            $table->integer('age')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('birth_certificate_no')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
