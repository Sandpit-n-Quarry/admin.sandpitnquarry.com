<?php

namespace App\Console\Commands;

use App\Services\MfaService;
use Illuminate\Console\Command;

class CleanupExpiredMfaCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mfa:cleanup
                            {--days= : Number of days after which to delete expired codes}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired MFA verification codes from the database';

    /**
     * Execute the console command.
     */
    public function handle(MfaService $mfaService)
    {
        $this->info('Starting MFA code cleanup...');

        // Check if we should ask for confirmation
        if (!$this->option('force')) {
            if (!$this->confirm('This will permanently delete expired MFA codes. Continue?')) {
                $this->info('Cleanup cancelled.');
                return Command::SUCCESS;
            }
        }

        // Perform cleanup
        $deleted = $mfaService->cleanupExpiredCodes();

        if ($deleted > 0) {
            $this->info("Successfully deleted {$deleted} expired MFA code(s).");
        } else {
            $this->info('No expired codes to clean up.');
        }

        return Command::SUCCESS;
    }
}
