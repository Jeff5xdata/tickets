<?php

namespace App\Services;

use App\Models\EmailAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EmailCheckingService
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Check emails for all active accounts
     */
    public function checkAllAccounts(): array
    {
        $results = [
            'total_accounts' => 0,
            'successful_checks' => 0,
            'failed_checks' => 0,
            'total_emails_fetched' => 0,
            'errors' => [],
            'account_results' => []
        ];

        try {
            $emailAccounts = EmailAccount::where('is_active', true)->get();
            $results['total_accounts'] = $emailAccounts->count();

            if ($emailAccounts->isEmpty()) {
                Log::info('No active email accounts found for checking');
                return $results;
            }

            foreach ($emailAccounts as $emailAccount) {
                $accountResult = $this->checkAccount($emailAccount);
                $results['account_results'][] = $accountResult;

                if ($accountResult['success']) {
                    $results['successful_checks']++;
                    $results['total_emails_fetched'] += $accountResult['emails_fetched'];
                } else {
                    $results['failed_checks']++;
                    $results['errors'][] = $accountResult['error'];
                }
            }

            // Log summary
            Log::info('Email check completed', [
                'total_accounts' => $results['total_accounts'],
                'successful_checks' => $results['successful_checks'],
                'failed_checks' => $results['failed_checks'],
                'total_emails_fetched' => $results['total_emails_fetched']
            ]);

            // Cache the last check time
            Cache::put('last_email_check', now(), now()->addDay());

        } catch (\Exception $e) {
            Log::error('Fatal error in email checking service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $results['errors'][] = 'Fatal error: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Check emails for a specific account
     */
    public function checkAccount(EmailAccount $emailAccount): array
    {
        $result = [
            'account_id' => $emailAccount->id,
            'email' => $emailAccount->email,
            'success' => false,
            'emails_fetched' => 0,
            'error' => null,
            'check_time' => now()
        ];

        try {
            // Skip Google Tasks accounts for email checking
            if ($emailAccount->type === 'google-tasks') {
                $result['success'] = true;
                $result['emails_fetched'] = 0;
                $result['error'] = 'Google Tasks accounts do not have emails to fetch';
                
                Log::info("Skipped email check for Google Tasks account: {$emailAccount->email}");
                return $result;
            }

            // The EmailService->fetchEmails method now handles its own token refresh logic.
            // All redundant refresh logic is removed from this service.
            $emailsFetched = $this->emailService->fetchEmails($emailAccount);
            
            $result['success'] = true;
            $result['emails_fetched'] = $emailsFetched;

            Log::info("Email check successful for {$emailAccount->email}", [
                'account_id' => $emailAccount->id,
                'emails_fetched' => $emailsFetched
            ]);

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            
            Log::error("Email check failed for {$emailAccount->email}", [
                'account_id' => $emailAccount->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $result;
    }

    /**
     * Get the last check time
     */
    public function getLastCheckTime(): ?string
    {
        return Cache::get('last_email_check');
    }

    /**
     * Get checking statistics
     */
    public function getStatistics(): array
    {
        $lastCheck = $this->getLastCheckTime();
        
        return [
            'last_check_time' => $lastCheck,
            'total_active_accounts' => EmailAccount::where('is_active', true)->count(),
            'total_accounts' => EmailAccount::count(),
            'is_running' => Cache::has('email_check_running')
        ];
    }
} 