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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('boschma_no')->unique();
            $table->string('surname');
            $table->string('middle_name')->nullable();
            $table->string('first_name')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->string('lga')->nullable();
            $table->string('state')->nullable();
            $table->string('nationality')->default('Nigerian');
            $table->enum('marital_status', ['Single', 'Married', 'Widow', 'Divorce', 'Others'])->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('religion')->nullable();
            $table->text('contact_address')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->enum('id_type', ['Driver License', 'NIMC', 'Voters Card', 'International Passport', 'Others', 'ID NO'])->nullable();
            $table->string('id_no')->nullable();
            $table->string('place_of_work')->nullable();
            $table->date('date_of_employment')->nullable();
            $table->date('date_of_retirement')->nullable();
            $table->enum('category', ['Civil Service', 'Organized Private Sector', 'Others'])->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
