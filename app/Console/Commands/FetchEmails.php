<?php

namespace App\Console\Commands;

use App\Models\EmailAccount;
use App\Services\EmailService;
use Illuminate\Console\Command;

class FetchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch {--account= : Specific email account ID} {--user= : Specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch emails from connected email accounts';

    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $accountId = $this->option('account');
        $userId = $this->option('user');

        $query = EmailAccount::where('is_active', true);

        if ($accountId) {
            $query->where('id', $accountId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $accounts = $query->with('user')->get();

        if ($accounts->isEmpty()) {
            $this->error('No active email accounts found.');
            return 1;
        }

        $this->info("Found {$accounts->count()} active email account(s).");

        $bar = $this->output->createProgressBar($accounts->count());
        $bar->start();

        foreach ($accounts as $account) {
            try {
                $this->emailService->fetchEmails($account);
                $this->line("\n✓ Fetched emails from {$account->email} ({$account->user->name})");
            } catch (\Exception $e) {
                $this->line("\n✗ Error fetching emails from {$account->email}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Email fetching completed!');

        return 0;
    }
}
