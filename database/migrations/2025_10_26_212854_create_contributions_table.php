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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->string('dp_no')->index();
            $table->decimal('amount', 10, 2); // Salary
            $table->decimal('contributed', 10, 2); // 3.5% of amount
            $table->integer('month'); // 1-12
            $table->integer('year'); // e.g., 2025
            $table->boolean('status')->default(1); // 1=active, 0=inactive
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries for same DP and month/year
            $table->unique(['dp_no', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
