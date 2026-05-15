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
        Schema::table('facility_claims', function (Blueprint $table) {
            // Change verifier_id from unsignedBigInteger to string (UUID)
            $table->string('verifier_id')->nullable()->change();
            
            // Change approver_id from unsignedBigInteger to string (UUID)
            $table->string('approver_id')->nullable()->change();
            
            // Change es_id from unsignedBigInteger to string (UUID)
            $table->string('es_id')->nullable()->change();
            
            // Change finance_id from unsignedBigInteger to string (UUID)
            $table->string('finance_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_claims', function (Blueprint $table) {
            // Revert back to unsignedBigInteger
            $table->unsignedBigInteger('verifier_id')->nullable()->change();
            $table->unsignedBigInteger('approver_id')->nullable()->change();
            $table->unsignedBigInteger('es_id')->nullable()->change();
            $table->unsignedBigInteger('finance_id')->nullable()->change();
        });
    }
};
