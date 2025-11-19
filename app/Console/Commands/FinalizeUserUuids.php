<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinalizeUserUuids extends Command
{
    protected $signature = 'app:finalize-user-uuids';
    protected $description = 'Finalizes the UUID migration by cleaning up redundant columns';

    public function handle()
    {
        $this->info('Checking user records...');
        
        // Verify all users have proper UUID values
        $users = User::all();
        $issues = 0;
        
        foreach ($users as $user) {
            if (empty($user->id) || empty($user->uuid)) {
                $this->error("User {$user->name} ({$user->email}) has missing UUID data!");
                $issues++;
            } elseif ($user->id !== $user->uuid) {
                $this->warn("User {$user->name} has mismatched UUID values. Synchronizing...");
                $user->id = $user->uuid;
                $user->saveQuietly();
            }
        }
        
        if ($issues > 0) {
            $this->error("Found {$issues} users with UUID issues. Please fix these before proceeding.");
            return 1;
        }
        
        // Safety check for foreign key relationships
        $this->info('Checking for related tables that need to be updated...');
        
        $relatedTables = [
            'password_reset_tokens' => 'user_id', 
            'personal_access_tokens' => 'user_id',
            'model_has_roles' => 'model_id',
            'model_has_permissions' => 'model_id',
        ];
        
        foreach ($relatedTables as $table => $column) {
            if (Schema::hasTable($table)) {
                if (Schema::hasColumn($table, $column)) {
                    $this->warn("Table {$table} has column {$column} that may reference users.old_id");
                    $this->warn("You should manually update these references before removing the old_id column");
                }
            }
        }
        
        if (!$this->confirm('Are you sure you want to proceed with removing the redundant uuid column?')) {
            $this->info('Operation cancelled.');
            return;
        }
        
        $this->info('Removing redundant uuid column...');
        
        // The uuid column is now redundant since we're using id as the UUID
        Schema::table('users', function ($table) {
            $table->dropColumn('uuid');
        });
        
        $this->info('UUID migration completed! User IDs are now fully UUID-based.');
    }
}
