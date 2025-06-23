<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Email Account') }} - {{ $emailAccount->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('email-accounts.update', $emailAccount) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $emailAccount->name) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $emailAccount->email) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Account Status -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Account Status</h3>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $emailAccount->is_active) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Active Account
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Inactive accounts will not fetch emails or create tickets.</p>
                        </div>

                        @if($emailAccount->type === 'imap')
                        <!-- IMAP Configuration -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">IMAP Configuration</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="imap_host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IMAP Host</label>
                                    <input type="text" name="imap_host" id="imap_host" value="{{ old('imap_host', $emailAccount->imap_host) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('imap_host')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="imap_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IMAP Port</label>
                                    <input type="number" name="imap_port" id="imap_port" value="{{ old('imap_port', $emailAccount->imap_port) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('imap_port')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="imap_encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Encryption</label>
                                    <select name="imap_encryption" id="imap_encryption" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="ssl" {{ old('imap_encryption', $emailAccount->imap_encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="tls" {{ old('imap_encryption', $emailAccount->imap_encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="none" {{ old('imap_encryption', $emailAccount->imap_encryption) === 'none' ? 'selected' : '' }}>None</option>
                                    </select>
                                    @error('imap_encryption')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label for="imap_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                                    <input type="text" name="imap_username" id="imap_username" value="{{ old('imap_username', $emailAccount->imap_username) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('imap_username')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="imap_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                    <input type="password" name="imap_password" id="imap_password" placeholder="Leave blank to keep current password" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('imap_password')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- OAuth Account Info -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">OAuth Account Information</h3>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Connected via OAuth</h4>
                                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                            This account is connected via {{ ucfirst($emailAccount->provider) }} OAuth. To update credentials, you'll need to reconnect the account.
                                        </p>
                                        <div class="mt-3">
                                            <button type="button" onclick="reconnectOAuth()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded transition-colors">
                                                Reconnect Account
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Advanced Settings -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Advanced Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="sync_frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sync Frequency (minutes)</label>
                                    <input type="number" name="sync_frequency" id="sync_frequency" value="{{ old('sync_frequency', $emailAccount->sync_frequency ?? 15) }}" min="1" max="1440" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">How often to check for new emails (minimum 1 minute)</p>
                                    @error('sync_frequency')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_emails_per_sync" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Emails per Sync</label>
                                    <input type="number" name="max_emails_per_sync" id="max_emails_per_sync" value="{{ old('max_emails_per_sync', $emailAccount->max_emails_per_sync ?? 50) }}" min="1" max="500" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maximum number of emails to process per sync</p>
                                    @error('max_emails_per_sync')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('email-accounts.show', $emailAccount) }}" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
                                Update Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- OAuth Modal -->
    <div id="oauth-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="oauth-modal-title">
                        Reconnect Account
                    </h3>
                    <button onclick="closeOAuthModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400" id="oauth-modal-description">
                        You will be redirected to reauthorize access to your account. Please complete the authorization and return to this page.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button onclick="closeOAuthModal()" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                        Cancel
                    </button>
                    <button id="oauth-proceed-button" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
                        Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let oauthRedirectUrl = null;

        function reconnectOAuth() {
            if (confirm('This will disconnect and reconnect your OAuth account. Continue?')) {
                const provider = '{{ $emailAccount->provider }}';
                let url;
                let title;
                let description;
                
                if (provider === 'google') {
                    url = '/email-accounts/google/redirect';
                    title = 'Reconnect Gmail Account';
                    description = 'You will be redirected to Google to reauthorize access to your Gmail account. Please complete the authorization and return to this page.';
                } else if (provider === 'google-tasks') {
                    url = '/email-accounts/google-tasks/redirect';
                    title = 'Reconnect Google Tasks Account';
                    description = 'You will be redirected to Google to reauthorize access to your Google Tasks. Please complete the authorization and return to this page.';
                } else {
                    url = '/email-accounts/microsoft/redirect';
                    title = 'Reconnect Outlook Account';
                    description = 'You will be redirected to Microsoft to reauthorize access to your Outlook account. Please complete the authorization and return to this page.';
                }
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.redirect_url) {
                        oauthRedirectUrl = data.redirect_url;
                        showOAuthModal(title, description);
                    } else {
                        throw new Error('No redirect URL received');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error reconnecting OAuth account: ' + error.message);
                });
            }
        }

        function showOAuthModal(title, description) {
            document.getElementById('oauth-modal-title').textContent = title;
            document.getElementById('oauth-modal-description').textContent = description;
            document.getElementById('oauth-modal').classList.remove('hidden');
            
            // Set up proceed button
            document.getElementById('oauth-proceed-button').onclick = () => {
                if (oauthRedirectUrl) {
                    // Open in new window/tab
                    const popup = window.open(oauthRedirectUrl, 'oauth_popup', 'width=600,height=700,scrollbars=yes,resizable=yes');
                    
                    // Check if popup was blocked
                    if (!popup) {
                        alert('Popup blocked! Please allow popups for this site and try again, or click the link below to open manually.');
                        // Fallback: show the URL
                        document.getElementById('oauth-modal-description').innerHTML = `
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Popup was blocked. Please click the link below to open manually:</p>
                            <a href="${oauthRedirectUrl}" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                Open Authorization Page
                            </a>
                        `;
                    } else {
                        // Use a more robust approach - reload the current window after a delay
                        // This avoids COOP issues with popup.closed
                        setTimeout(() => {
                            closeOAuthModal();
                            // Reload the page after a reasonable delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        }, 3000);
                    }
                }
            };
        }

        function closeOAuthModal() {
            document.getElementById('oauth-modal').classList.add('hidden');
            oauthRedirectUrl = null;
        }

        // Close modal when clicking outside
        document.getElementById('oauth-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOAuthModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeOAuthModal();
            }
        });
    </script>
</x-app-layout> 