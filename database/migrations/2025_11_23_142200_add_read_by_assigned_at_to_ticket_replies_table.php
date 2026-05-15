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
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->timestamp('read_by_assigned_at')->nullable()->after('is_internal');
            $table->index('read_by_assigned_at'); // Add index for better performance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropIndex(['read_by_assigned_at']);
            $table->dropColumn('read_by_assigned_at');
        });
    }
};
