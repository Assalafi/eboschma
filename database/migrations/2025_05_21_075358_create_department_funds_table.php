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
        Schema::create('department_funds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->decimal('amount', 15, 2);
            $table->string('session');
            $table->string('sector');
            $table->timestamps();
            
            // Foreign key
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['department_id', 'session', 'sector']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_funds');
    }
};
