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
        Schema::table('service_referrals', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'rejected_by']);
        });

        Schema::table('service_referrals', function (Blueprint $table) {
            $table->uuid('approved_by')->nullable()->after('approval_status');
            $table->uuid('rejected_by')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_referrals', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'rejected_by']);
        });

        Schema::table('service_referrals', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
        });
    }
};
