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
            // Verifier Stage
            $table->enum('verifier_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->text('verifier_notes')->nullable()->after('verifier_status');
            $table->timestamp('verifier_updated_at')->nullable()->after('verifier_notes');
            $table->unsignedBigInteger('verifier_id')->nullable()->after('verifier_updated_at');
            
            // Approver Stage
            $table->enum('approver_status', ['pending', 'approved', 'rejected'])->default('pending')->after('verifier_id');
            $table->text('approver_notes')->nullable()->after('approver_status');
            $table->timestamp('approver_updated_at')->nullable()->after('approver_notes');
            $table->unsignedBigInteger('approver_id')->nullable()->after('approver_updated_at');
            
            // Executive Secretary Stage
            $table->enum('es_status', ['pending', 'approved', 'rejected'])->default('pending')->after('approver_id');
            $table->text('es_notes')->nullable()->after('es_status');
            $table->timestamp('es_updated_at')->nullable()->after('es_notes');
            $table->unsignedBigInteger('es_id')->nullable()->after('es_updated_at');
            
            // Finance Stage
            $table->enum('finance_status', ['pending', 'paid', 'rejected'])->default('pending')->after('es_id');
            $table->text('finance_notes')->nullable()->after('finance_status');
            $table->timestamp('finance_updated_at')->nullable()->after('finance_notes');
            $table->unsignedBigInteger('finance_id')->nullable()->after('finance_updated_at');
            
            // Indexes for better performance
            $table->index('verifier_status');
            $table->index('approver_status');
            $table->index('es_status');
            $table->index('finance_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_claims', function (Blueprint $table) {
            // Drop verifier columns
            $table->dropIndex(['verifier_status']);
            $table->dropColumn(['verifier_status', 'verifier_notes', 'verifier_updated_at', 'verifier_id']);
            
            // Drop approver columns
            $table->dropIndex(['approver_status']);
            $table->dropColumn(['approver_status', 'approver_notes', 'approver_updated_at', 'approver_id']);
            
            // Drop ES columns
            $table->dropIndex(['es_status']);
            $table->dropColumn(['es_status', 'es_notes', 'es_updated_at', 'es_id']);
            
            // Drop finance columns
            $table->dropIndex(['finance_status']);
            $table->dropColumn(['finance_status', 'finance_notes', 'finance_updated_at', 'finance_id']);
        });
    }
};
