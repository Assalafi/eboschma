<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncStaffWithUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-staff-with-users {--password=password} {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing staff records with user records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to sync staff with users...');
        
        $dryRun = $this->option('dry-run');
        $defaultPassword = $this->option('password');
        
        if ($dryRun) {
            $this->warn('Running in dry-run mode. No changes will be made.');
        }
        
        $staff = Staff::all();
        $this->info('Found ' . $staff->count() . ' staff records.');
        
        $created = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($staff as $staffMember) {
            // Check if a user with this email already exists
            $user = User::where('email', $staffMember->email)->first();
            
            if ($user) {
                $this->line("User already exists for staff member: {$staffMember->fullname} ({$staffMember->email})");
                $skipped++;
                continue;
            }
            
            $this->line("Creating user for staff member: {$staffMember->fullname} ({$staffMember->email})");
            
            if (!$dryRun) {
                try {
                    // Create user record
                    $user = User::create([
                        'name' => $staffMember->fullname,
                        'email' => $staffMember->email,
                        'password' => Hash::make($defaultPassword)
                    ]);
                    
                    // Sync roles
                    $roleIds = $staffMember->roles()->pluck('id')->toArray();
                    $user->syncRoles($roleIds);
                    
                    // Log user creation
                    ActivityLog::log(
                        'create',
                        'user',
                        $user->id,
                        $user->name,
                        ['action' => 'Created user account for existing staff member through migration script']
                    );
                    
                    $created++;
                    $this->info("✓ User created successfully with ID: {$user->id}");
                    $this->info("✓ Assigned " . count($roleIds) . " roles to the user");
                } catch (\Exception $e) {
                    $this->error("✗ Failed to create user for staff member: {$staffMember->fullname}");
                    $this->error("  Error: {$e->getMessage()}");
                    $errors++;
                }
            }
        }
        
        $this->newLine();
        $this->info('Staff-User sync completed!');
        $this->info("Summary:");
        $this->info("- Created: $created");
        $this->info("- Skipped (already exists): $skipped");
        $this->info("- Errors: $errors");
        
        if ($created > 0 && !$dryRun) {
            $this->warn("NOTE: All created users have the default password: '$defaultPassword'");
            $this->warn("Users should change their passwords after first login.");
        }
        
        return Command::SUCCESS;
    }
}
