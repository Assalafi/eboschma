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
        // service_categories table
        if (!Schema::hasTable('service_categories')) {
            Schema::create('service_categories', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->timestamps();
            });
        }

        // service_types table
        if (!Schema::hasTable('service_types')) {
            Schema::create('service_types', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('service_category_id', 36);
                $table->string('name');
                $table->timestamps();

                $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('cascade');
            });
        }

        // service_items table
        if (!Schema::hasTable('service_items')) {
            Schema::create('service_items', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('service_type_id', 36);
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('type');
                $table->decimal('price', 10, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_items');
        Schema::dropIfExists('service_types');
        Schema::dropIfExists('service_categories');
    }
};
