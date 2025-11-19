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
        Schema::create('civil_servants', function (Blueprint $table) {
            $table->id();
            $table->string('dp_no')->unique();
            $table->string('nin')->nullable();
            $table->string('bvn')->nullable();
            $table->string('fullname');
            $table->date('dob');
            $table->string('state')->nullable();
            $table->string('lga')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->string('mda'); // Ministry/Department/Agency
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('civil_servants');
    }
};
