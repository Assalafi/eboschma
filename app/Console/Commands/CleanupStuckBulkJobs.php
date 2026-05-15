<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupStuckBulkJobs extends Command
{
    protected $signature = 'bulk-jobs:cleanup-stuck';
    protected $description = 'Clean up stuck bulk ID card jobs that have been processing for too long';

    public function handle()
    {
        // Find jobs stuck in "processing" for more than 2 hours
        $stuckJobs = DB::table('bulk_id_card_jobs')
            ->where('status', 'processing')
            ->where('started_at', '<', now()->subHours(2))
            ->get();

        if ($stuckJobs->isEmpty()) {
            $this->info('No stuck jobs found.');
            return 0;
        }

        $this->info('Found ' . $stuckJobs->count() . ' stuck job(s)');

        foreach ($stuckJobs as $job) {
            $this->warn("Cleaning up stuck job: {$job->job_id}");
            
            DB::table('bulk_id_card_jobs')
                ->where('id', $job->id)
                ->update([
                    'status' => 'failed',
                    'error_message' => 'Job stuck in processing state for more than 2 hours - automatically cleaned up',
                    'updated_at' => now(),
                ]);

            Log::warning('Cleaned up stuck bulk job', [
                'job_id' => $job->job_id,
                'started_at' => $job->started_at,
                'progress' => $job->progress_percentage,
            ]);
        }

        // Also clear any jobs stuck in the queue
        $queuedCount = DB::table('jobs')->where('created_at', '<', now()->subHours(2))->count();
        if ($queuedCount > 0) {
            DB::table('jobs')->where('created_at', '<', now()->subHours(2))->delete();
            $this->warn("Cleared {$queuedCount} old queued job(s)");
        }

        $this->info('Cleanup completed successfully.');
        return 0;
    }
}
