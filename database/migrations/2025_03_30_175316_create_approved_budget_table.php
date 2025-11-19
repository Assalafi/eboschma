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
        Schema::create('approved_budget', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30);
            $table->double('amount')->default(0);
            $table->string('sector', 30)->default('state');
            $table->string('session', 15);
            $table->string('user', 50)->default('system')->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approved_budget');
    }
};
