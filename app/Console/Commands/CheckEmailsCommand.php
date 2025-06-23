<?php

namespace App\Console\Commands;

use App\Services\EmailCheckingService;
use Illuminate\Console\Command;

class CheckEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:check {--account= : Check specific email account by ID} {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for new emails from all active email accounts';

    protected EmailCheckingService $emailCheckingService;

    public function __construct(EmailCheckingService $emailCheckingService)
    {
        parent::__construct();
        $this->emailCheckingService = $emailCheckingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting email check...');

        try {
            $results = $this->emailCheckingService->checkAllAccounts();

            $this->info("Email check completed!");
            $this->line("📊 Summary:");
            $this->line("  • Total accounts: {$results['total_accounts']}");
            $this->line("  • Successful checks: {$results['successful_checks']}");
            $this->line("  • Failed checks: {$results['failed_checks']}");
            $this->line("  • Total emails fetched: {$results['total_emails_fetched']}");

            if ($this->option('detailed') && !empty($results['account_results'])) {
                $this->line("\n📧 Account Details:");
                foreach ($results['account_results'] as $accountResult) {
                    $status = $accountResult['success'] ? '✅' : '❌';
                    $this->line("  {$status} {$accountResult['email']}: {$accountResult['emails_fetched']} emails");
                    
                    if (!$accountResult['success']) {
                        $this->error("    Error: {$accountResult['error']}");
                    }
                }
            }

            if (!empty($results['errors'])) {
                $this->warn("\n⚠️  Errors encountered:");
                foreach ($results['errors'] as $error) {
                    $this->line("  • {$error}");
                }
            }

            return $results['failed_checks'] > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error("Fatal error during email check: " . $e->getMessage());
            return 1;
        }
    }
} 