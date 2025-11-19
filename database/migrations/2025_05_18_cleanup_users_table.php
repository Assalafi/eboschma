<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Ensure all users have consistent id and uuid values
        $this->syncUserUuids();
        
        // Step 2: We'll skip modifying primary keys for now
        // This will be done carefully through a separate command
    }
    
    /**
     * Ensure all users have consistent id and uuid values
     */
    private function syncUserUuids(): void
    {
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            $updates = [];
            
            // If id is empty but uuid exists
            if (empty($user->id) && !empty($user->uuid)) {
                $updates['id'] = $user->uuid;
                Log::info("User {$user->name}: Copied UUID to ID field");
            }
            // If uuid is empty but id exists
            else if (empty($user->uuid) && !empty($user->id)) {
                $updates['uuid'] = $user->id;
                Log::info("User {$user->name}: Copied ID to UUID field");
            }
            // If both are empty, generate a new UUID for both
            else if (empty($user->uuid) && empty($user->id)) {
                $uuid = (string) Str::uuid();
                $updates['id'] = $uuid;
                $updates['uuid'] = $uuid;
                Log::info("User {$user->name}: Generated new UUID for both fields");
            }
            // If they're different, make them the same (prefer uuid)
            else if ($user->uuid !== $user->id) {
                $updates['id'] = $user->uuid;
                Log::info("User {$user->name}: Synchronized ID with UUID");
            }
            
            // Apply updates if any
            if (!empty($updates)) {
                DB::table('users')->where('old_id', $user->old_id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only ensures data consistency, nothing to reverse
    }
};
