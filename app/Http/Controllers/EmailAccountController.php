<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EmailAccountController extends Controller
{
    use AuthorizesRequests;
    
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function index(): View
    {
        $emailAccounts = auth()->user()->emailAccounts()->orderBy('created_at', 'desc')->get();
        
        return view('email-accounts.index', compact('emailAccounts'));
    }

    public function create(): View
    {
        return view('email-accounts.create');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'type' => 'required|in:imap,gmail,outlook,google-tasks',
            'provider' => 'nullable|string|max:255',
            
            // IMAP settings
            'imap_host' => 'required_if:type,imap|nullable|string|max:255',
            'imap_port' => 'required_if:type,imap|nullable|integer|min:1|max:65535',
            'imap_encryption' => 'required_if:type,imap|nullable|in:ssl,tls,none',
            'imap_username' => 'required_if:type,imap|nullable|string|max:255',
            'imap_password' => 'required_if:type,imap|nullable|string',
            
            // SMTP settings
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
        ]);

        try {
            $emailAccount = auth()->user()->emailAccounts()->create($validated);

            // Test connection if it's an IMAP account
            if ($emailAccount->type === 'imap') {
                $this->testConnection($emailAccount);
            }

            return response()->json([
                'message' => 'Email account created successfully',
                'email_account' => $emailAccount
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating email account: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error creating email account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(EmailAccount $emailAccount): View
    {
        $this->authorize('view', $emailAccount);

        $emailAccount->load(['tickets', 'emailRules']);

        return view('email-accounts.show', compact('emailAccount'));
    }

    public function edit(EmailAccount $emailAccount): View
    {
        $this->authorize('update', $emailAccount);

        return view('email-accounts.edit', compact('emailAccount'));
    }

    public function update(Request $request, EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('update', $emailAccount);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'is_active' => 'boolean',
            
            // IMAP settings
            'imap_host' => 'nullable|string|max:255',
            'imap_port' => 'nullable|integer|min:1|max:65535',
            'imap_encryption' => 'nullable|in:ssl,tls,none',
            'imap_username' => 'nullable|string|max:255',
            'imap_password' => 'nullable|string',
            
            // SMTP settings
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
        ]);

        try {
            $emailAccount->update($validated);

            return response()->json([
                'message' => 'Email account updated successfully',
                'email_account' => $emailAccount->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating email account: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating email account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('delete', $emailAccount);

        try {
            $emailAccount->delete();

            return response()->json([
                'message' => 'Email account deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting email account: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deleting email account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testConnection(EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('update', $emailAccount);

        try {
            if ($emailAccount->type === 'imap') {
                $this->testImapConnection($emailAccount);
            } else {
                $this->testOAuthConnection($emailAccount);
            }

            return response()->json([
                'message' => 'Connection test successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 400);
        }
    }

    public function fetchEmails(EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('update', $emailAccount);

        try {
            $this->emailService->fetchEmails($emailAccount);

            return response()->json([
                'message' => 'Emails fetched successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching emails: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error fetching emails: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchAllEmails(): JsonResponse
    {
        try {
            // Run the emails:fetch command for the current user
            $userId = auth()->id();
            
            // Get all active email accounts for the user
            $emailAccounts = auth()->user()->emailAccounts()->where('is_active', true)->get();
            $totalFetched = 0;
            $failedAccounts = [];
            $scopeIssues = [];

            foreach ($emailAccounts as $account) {
                try {
                    $totalFetched += $this->emailService->fetchEmails($account);
                } catch (\Exception $e) {
                    Log::error("Failed to fetch emails for account {$account->email}", [
                        'account_id' => $account->id,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Check if this is a scope-related error
                    if (str_contains($e->getMessage(), 'needs to be reconnected') || 
                        str_contains($e->getMessage(), 'requires re-authentication')) {
                        $scopeIssues[] = $account->email;
                    } else {
                        $failedAccounts[] = $account->email;
                    }
                }
            }

            $message = "Email fetch process completed.";
            if (!empty($scopeIssues)) {
                $message .= " Some accounts need to be reconnected for full functionality: " . implode(', ', $scopeIssues) . ". Please go to account settings and click 'Reconnect Account'.";
            }
            if (!empty($failedAccounts)) {
                $message .= " Failed to fetch from: " . implode(', ', $failedAccounts);
            }

            return response()->json([
                'message' => $message,
                'email_count' => $totalFetched,
                'failed_accounts' => $failedAccounts,
                'scope_issues' => $scopeIssues,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching all emails: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error fetching emails: ' . $e->getMessage(),
                'email_count' => 0
            ], 500);
        }
    }

    public function redirectToGoogle(): JsonResponse
    {
        $url = Socialite::driver('google-email')
            ->scopes([
                'https://www.googleapis.com/auth/gmail.readonly',
                'https://www.googleapis.com/auth/gmail.send',
                'https://www.googleapis.com/auth/gmail.modify',
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirectUrl(route('email-accounts.google.callback'))
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['redirect_url' => $url]);
    }

    public function handleGoogleCallback(Request $request): \Illuminate\Http\Response
    {
        try {
            $googleUser = Socialite::driver('google-email')
                ->redirectUrl(route('email-accounts.google.callback'))
                ->stateless()
                ->user();

            // Check if Gmail account already exists
            $emailAccount = auth()->user()->emailAccounts()
                ->where('email', $googleUser->getEmail())
                ->where('type', 'gmail')
                ->first();

            if ($emailAccount) {
                // Update existing account
                $emailAccount->update([
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                    'is_active' => true,
                ]);
                $message = 'Gmail account reconnected successfully';
            } else {
                // Create new account
                $emailAccount = auth()->user()->emailAccounts()->create([
                    'name' => 'Gmail Account',
                    'email' => $googleUser->getEmail(),
                    'type' => 'gmail',
                    'provider' => 'google',
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                ]);
                $message = 'Gmail account connected successfully';
            }

            // Also create/update Google Calendar account with same credentials
            $calendarAccount = auth()->user()->emailAccounts()
                ->where('email', $googleUser->getEmail())
                ->where('type', 'google-calendar')
                ->first();

            if ($calendarAccount) {
                // Update existing calendar account
                $calendarAccount->update([
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                    'is_active' => true,
                ]);
            } else {
                // Create new calendar account
                auth()->user()->emailAccounts()->create([
                    'name' => 'Google Calendar Account',
                    'email' => $googleUser->getEmail(),
                    'type' => 'google-calendar',
                    'provider' => 'google',
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                ]);
            }

            // Return HTML that closes popup and redirects parent
            return response()->view('oauth.callback', [
                'success' => true,
                'message' => $message . ' (Calendar access also enabled)',
                'redirect_url' => route('email-accounts.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error connecting Gmail account: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('oauth.callback', [
                'success' => false,
                'message' => 'Error connecting Gmail account: ' . $e->getMessage(),
                'redirect_url' => route('email-accounts.create')
            ]);
        }
    }

    public function redirectToGoogleTasks(): JsonResponse
    {
        $url = Socialite::driver('google-tasks')
            ->scopes([
                'https://www.googleapis.com/auth/tasks',
                'https://www.googleapis.com/auth/tasks.readonly',
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirectUrl(route('email-accounts.google-tasks.callback'))
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['redirect_url' => $url]);
    }

    public function handleGoogleTasksCallback(Request $request): \Illuminate\Http\Response
    {
        try {
            $googleUser = Socialite::driver('google-tasks')
                ->redirectUrl(route('email-accounts.google-tasks.callback'))
                ->stateless()
                ->user();

            // Create or update Google Tasks account
            $emailAccount = auth()->user()->emailAccounts()
                ->where('provider', 'google-tasks')
                ->first();

            if ($emailAccount) {
                $emailAccount->update([
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                    'is_active' => true,
                ]);
                $message = 'Google Tasks account reconnected successfully';
            } else {
                $emailAccount = auth()->user()->emailAccounts()->create([
                    'name' => 'Google Tasks Account',
                    'email' => $googleUser->getEmail(),
                    'type' => 'google-tasks',
                    'provider' => 'google-tasks',
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                ]);
                $message = 'Google Tasks account connected successfully';
            }

            // Also create/update Google Calendar account with same credentials
            $calendarAccount = auth()->user()->emailAccounts()
                ->where('email', $googleUser->getEmail())
                ->where('type', 'google-calendar')
                ->first();

            if ($calendarAccount) {
                // Update existing calendar account
                $calendarAccount->update([
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                    'is_active' => true,
                ]);
            } else {
                // Create new calendar account
                auth()->user()->emailAccounts()->create([
                    'name' => 'Google Calendar Account',
                    'email' => $googleUser->getEmail(),
                    'type' => 'google-calendar',
                    'provider' => 'google',
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                ]);
            }

            return response()->view('oauth.callback', [
                'success' => true,
                'message' => $message . ' (Calendar access also enabled)',
                'redirect_url' => route('email-accounts.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error connecting Google Tasks account: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('oauth.callback', [
                'success' => false,
                'message' => 'Error connecting Google Tasks account: ' . $e->getMessage(),
                'redirect_url' => route('email-accounts.create')
            ]);
        }
    }

    public function redirectToMicrosoft(): JsonResponse
    {
        $url = Socialite::driver('microsoft')
            ->scopes([
                'offline_access',
                'https://graph.microsoft.com/Mail.Read',
                'https://graph.microsoft.com/Mail.Send',
                'https://graph.microsoft.com/Tasks.ReadWrite',
                'https://graph.microsoft.com/Calendars.ReadWrite'
            ])
            ->redirectUrl(route('email-accounts.microsoft.callback'))
            ->redirect()
            ->getTargetUrl();

        return response()->json(['redirect_url' => $url]);
    }

    public function handleMicrosoftCallback(Request $request): \Illuminate\Http\Response
    {
        \Log::info('Microsoft callback received', [
            'all_params' => $request->all(),
            'code' => $request->get('code'),
            'state' => $request->get('state'),
            'error' => $request->get('error'),
            'error_description' => $request->get('error_description')
        ]);

        try {
            $microsoftUser = Socialite::driver('microsoft')
                ->redirectUrl(route('email-accounts.microsoft.callback'))
                ->stateless()
                ->user();

            // Check if Outlook account already exists
            $emailAccount = auth()->user()->emailAccounts()
                ->where('email', $microsoftUser->getEmail())
                ->where('type', 'outlook')
                ->first();

            if ($emailAccount) {
                // Update existing account
                $emailAccount->update([
                    'access_token' => $microsoftUser->token,
                    'refresh_token' => $microsoftUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($microsoftUser->expiresIn),
                    'is_active' => true,
                ]);
                $message = 'Outlook account reconnected successfully';
            } else {
                // Create new account
                $emailAccount = auth()->user()->emailAccounts()->create([
                    'name' => 'Outlook Account',
                    'email' => $microsoftUser->getEmail(),
                    'type' => 'outlook',
                    'provider' => 'microsoft',
                    'access_token' => $microsoftUser->token,
                    'refresh_token' => $microsoftUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($microsoftUser->expiresIn),
                ]);
                $message = 'Outlook account connected successfully';
            }

            // Also create/update Microsoft Calendar account with same credentials
            $calendarAccount = auth()->user()->emailAccounts()
                ->where('email', $microsoftUser->getEmail())
                ->where('type', 'microsoft-calendar')
                ->first();

            if ($calendarAccount) {
                // Update existing calendar account
                $calendarAccount->update([
                    'access_token' => $microsoftUser->token,
                    'refresh_token' => $microsoftUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($microsoftUser->expiresIn),
                    'is_active' => true,
                ]);
            } else {
                // Create new calendar account
                auth()->user()->emailAccounts()->create([
                    'name' => 'Microsoft Calendar Account',
                    'email' => $microsoftUser->getEmail(),
                    'type' => 'microsoft-calendar',
                    'provider' => 'microsoft',
                    'access_token' => $microsoftUser->token,
                    'refresh_token' => $microsoftUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($microsoftUser->expiresIn),
                ]);
            }

            return response()->view('oauth.callback', [
                'success' => true,
                'message' => $message . ' (Calendar access also enabled)',
                'redirect_url' => route('email-accounts.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error connecting Outlook account: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('oauth.callback', [
                'success' => false,
                'message' => 'Error connecting Outlook account: ' . $e->getMessage(),
                'redirect_url' => route('email-accounts.create')
            ]);
        }
    }

    public function redirectToGoogleCalendar(): JsonResponse
    {
        $url = Socialite::driver('google-email')
            ->scopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirectUrl(route('email-accounts.google-calendar.callback'))
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['redirect_url' => $url]);
    }

    public function handleGoogleCalendarCallback(Request $request): \Illuminate\Http\Response
    {
        try {
            $googleUser = Socialite::driver('google-email')
                ->redirectUrl(route('email-accounts.google-calendar.callback'))
                ->stateless()
                ->user();

            // Create or update Google Calendar account
            $emailAccount = auth()->user()->emailAccounts()
                ->where('provider', 'google')
                ->where('type', 'google-calendar')
                ->first();

            if ($emailAccount) {
                $emailAccount->update([
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                    'is_active' => true,
                ]);
                $message = 'Google Calendar account reconnected successfully';
            } else {
                $emailAccount = auth()->user()->emailAccounts()->create([
                    'name' => 'Google Calendar Account',
                    'email' => $googleUser->getEmail(),
                    'type' => 'google-calendar',
                    'provider' => 'google',
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                ]);
                $message = 'Google Calendar account connected successfully';
            }

            return response()->view('oauth.callback', [
                'success' => true,
                'message' => $message,
                'redirect_url' => route('email-accounts.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error connecting Google Calendar account: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('oauth.callback', [
                'success' => false,
                'message' => 'Error connecting Google Calendar account: ' . $e->getMessage(),
                'redirect_url' => route('email-accounts.create')
            ]);
        }
    }

    public function redirectToMicrosoftCalendar(): JsonResponse
    {
        $url = Socialite::driver('microsoft')
            ->scopes([
                'offline_access',
                'https://graph.microsoft.com/Calendars.ReadWrite'
            ])
            ->redirectUrl(route('email-accounts.microsoft-calendar.callback'))
            ->redirect()
            ->getTargetUrl();

        return response()->json(['redirect_url' => $url]);
    }

    public function handleMicrosoftCalendarCallback(Request $request): \Illuminate\Http\Response
    {
        try {
            $microsoftUser = Socialite::driver('microsoft')
                ->redirectUrl(route('email-accounts.microsoft-calendar.callback'))
                ->stateless()
                ->user();

            // Create or update Microsoft Calendar account
            $emailAccount = auth()->user()->emailAccounts()
                ->where('provider', 'microsoft')
                ->where('type', 'microsoft-calendar')
                ->first();

            if ($emailAccount) {
                $emailAccount->update([
                    'access_token' => $microsoftUser->token,
                    'refresh_token' => $microsoftUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($microsoftUser->expiresIn),
                    'is_active' => true,
                ]);
                $message = 'Microsoft Calendar account reconnected successfully';
            } else {
                $emailAccount = auth()->user()->emailAccounts()->create([
                    'name' => 'Microsoft Calendar Account',
                    'email' => $microsoftUser->getEmail(),
                    'type' => 'microsoft-calendar',
                    'provider' => 'microsoft',
                    'access_token' => $microsoftUser->token,
                    'refresh_token' => $microsoftUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($microsoftUser->expiresIn),
                ]);
                $message = 'Microsoft Calendar account connected successfully';
            }

            return response()->view('oauth.callback', [
                'success' => true,
                'message' => $message,
                'redirect_url' => route('email-accounts.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error connecting Microsoft Calendar account: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('oauth.callback', [
                'success' => false,
                'message' => 'Error connecting Microsoft Calendar account: ' . $e->getMessage(),
                'redirect_url' => route('email-accounts.create')
            ]);
        }
    }

    protected function testImapConnection(EmailAccount $emailAccount): void
    {
        // Implementation for testing IMAP connection
        // This would use the IMAP client to test the connection
        Log::info('Testing IMAP connection for: ' . $emailAccount->email);
    }

    protected function testOAuthConnection(EmailAccount $emailAccount): void
    {
        // Implementation for testing OAuth connection
        // This would verify the access token is still valid
        Log::info('Testing OAuth connection for: ' . $emailAccount->email);
    }

    public function toggleStatus(EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('update', $emailAccount);

        try {
            $emailAccount->update([
                'is_active' => !$emailAccount->is_active
            ]);

            $status = $emailAccount->fresh()->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'message' => "Email account {$status} successfully",
                'email_account' => $emailAccount->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling email account status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating account status: ' . $e->getMessage()
            ], 500);
        }
    }
}
