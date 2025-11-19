<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a uuid column to users table
        Schema::table('users', function (Blueprint $table) {
            // Add UUID column if it doesn't exist
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            }
        });
        
        // Generate UUIDs for all existing user records
        $users = User::whereNull('uuid')->get();
        foreach ($users as $user) {
            $user->uuid = (string) Str::uuid();
            $user->saveQuietly(); // Save without firing events
        }
        
        // Update sessions table to accept UUID values
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('user_id', 36)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
