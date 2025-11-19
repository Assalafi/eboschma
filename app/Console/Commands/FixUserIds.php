<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixUserIds extends Command
{
    protected $signature = 'app:fix-user-ids';
    protected $description = 'Fix empty IDs in users table';

    public function handle()
    {
        $this->info('Checking for users with empty IDs...');
        
        // First, let's see what we're working with
        $users = User::all();
        $this->info("Total users: " . $users->count());
        
        $emptyIds = User::whereNull('id')->orWhere('id', '')->count();
        $emptyUuids = User::whereNull('uuid')->orWhere('uuid', '')->count();
        
        $this->info("Users with empty ID: " . $emptyIds);
        $this->info("Users with empty UUID: " . $emptyUuids);
        
        // Display schema info
        $columns = DB::select('SHOW COLUMNS FROM users');
        $this->info("User table schema:");
        foreach ($columns as $column) {
            $this->line("- {$column->Field}: {$column->Type} (Null: {$column->Null}, Key: {$column->Key}, Default: " . ($column->Default ?: 'NULL') . ")");
        }
        
        // Fix empty IDs if any
        if ($emptyIds > 0) {
            $this->info("Fixing empty IDs...");
            $users = User::whereNull('id')->orWhere('id', '')->get();
            foreach ($users as $user) {
                $user->id = $user->id ?: $user->getKey();
                $user->saveQuietly();
                $this->line("Fixed user {$user->name} ({$user->email})");
            }
        }
        
        // Fix empty UUIDs
        if ($emptyUuids > 0) {
            $this->info("Fixing empty UUIDs...");
            $users = User::whereNull('uuid')->orWhere('uuid', '')->get();
            foreach ($users as $user) {
                $user->uuid = (string) Str::uuid();
                $user->saveQuietly();
                $this->line("Fixed UUID for user {$user->name} ({$user->email}): {$user->uuid}");
            }
        }
        
        $this->info("Done!");
    }
}
