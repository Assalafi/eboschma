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
        Schema::table('claims', function (Blueprint $table) {
            // Change foreign key columns from unsignedBigInteger to string (UUID)
            $table->string('ro_reviewer_id')->nullable()->change();
            $table->string('e5_reviewer_id')->nullable()->change();
            $table->string('created_by')->change();
            $table->string('updated_by')->nullable()->change();
            $table->string('approved_by')->nullable()->change();
            $table->string('rejected_by')->nullable()->change();
            $table->string('paid_by')->nullable()->change();
        });

        Schema::table('claim_histories', function (Blueprint $table) {
            $table->string('user_id')->nullable()->change();
        });

        Schema::table('claim_notes', function (Blueprint $table) {
            $table->string('user_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            // Revert back to unsignedBigInteger
            $table->unsignedBigInteger('ro_reviewer_id')->nullable()->change();
            $table->unsignedBigInteger('e5_reviewer_id')->nullable()->change();
            $table->unsignedBigInteger('created_by')->change();
            $table->unsignedBigInteger('updated_by')->nullable()->change();
            $table->unsignedBigInteger('approved_by')->nullable()->change();
            $table->unsignedBigInteger('rejected_by')->nullable()->change();
            $table->unsignedBigInteger('paid_by')->nullable()->change();
        });

        Schema::table('claim_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('claim_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });
    }
};
