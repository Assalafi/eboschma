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
            if (!Schema::hasColumn('service_referrals', 'approval_status')) {
                $table->string('approval_status')->default('pending')->after('status');
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_referrals', function (Blueprint $table) {
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'rejected_by',
                'rejected_at',
                'rejection_reason'
            ]);
        });
    }
};
