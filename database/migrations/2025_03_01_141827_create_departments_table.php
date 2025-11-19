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
        Schema::create('departments', function (Blueprint $table) {
            // use uuid
            $table->uuid('id')->primary();
            //$table->id();
            $table->string('name');
            $table->integer('targeted_lives')->length(10)->default(0)->nullable();
            $table->double('enrollee_amount')->default(0)->nullable();
            $table->double('enrollee_rate')->default(0)->nullable();
            $table->string('image')->default('default.png')->nullable();
            $table->string('user')->default('system')->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
