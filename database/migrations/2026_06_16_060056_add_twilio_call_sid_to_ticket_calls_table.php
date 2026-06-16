<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replaces zoho_call_id with twilio_call_sid in ticket_calls table.
     */
    public function up(): void
    {
        Schema::table('ticket_calls', function (Blueprint $table) {
            // Add Twilio SID column
            $table->string('twilio_call_sid')->nullable()->after('ticket_id');
        });

        // Copy existing zoho_call_id values into twilio_call_sid if any
        DB::table('ticket_calls')->whereNotNull('zoho_call_id')->get()->each(function ($row) {
            DB::table('ticket_calls')->where('id', $row->id)->update([
                'twilio_call_sid' => $row->zoho_call_id,
            ]);
        });

        // Drop the old zoho_call_id column
        Schema::table('ticket_calls', function (Blueprint $table) {
            $table->dropColumn('zoho_call_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_calls', function (Blueprint $table) {
            $table->string('zoho_call_id')->nullable()->unique()->after('ticket_id');
        });

        Schema::table('ticket_calls', function (Blueprint $table) {
            $table->dropColumn('twilio_call_sid');
        });
    }
};
