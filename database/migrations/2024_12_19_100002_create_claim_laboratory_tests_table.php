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
        Schema::create('claim_laboratory_tests', function (Blueprint $table) {
            $table->id();
            $table->char('claim_id', 36);
            $table->string('test_name');
            $table->decimal('cost', 10, 2);
            $table->integer('frequency')->default(1);
            $table->decimal('claimed_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Note: Foreign key constraint skipped due to UUID compatibility issues
            // Referential integrity maintained at application level
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_laboratory_tests');
    }
};
