<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Email Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Account Type Selection -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Choose Account Type</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Gmail Email OAuth -->
                            <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 cursor-pointer transition-colors" onclick="selectAccountType('gmail')">
                                <div class="flex items-center mb-3">
                                    <svg class="h-4 w-4 text-red-500 mr-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-.904.732-1.636 1.636-1.636h.819L12 10.183l9.545-6.362h.819c.904 0 1.636.732 1.636 1.636z"/>
                                    </svg>
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white ml-4">Gmail Email</h4>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Connect your Gmail account for email and calendar access using OAuth.</p>
                            </div>

                            <!-- Google Tasks OAuth -->
                            <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 cursor-pointer transition-colors" onclick="selectAccountType('google-tasks')">
                                <div class="flex items-center mb-3">
                                    <svg class="h-4 w-4 text-green-500 mr-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white ml-4">Google Tasks</h4>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Connect your Google account for Tasks and Calendar integration using OAuth.</p>
                            </div>

                            <!-- Outlook OAuth -->
                            <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 cursor-pointer transition-colors" onclick="selectAccountType('outlook')">
                                <div class="flex items-center mb-3">
                                    <svg class="h-4 w-4 text-blue-500 mr-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.5 1h-15c-.83 0-1.5.67-1.5 1.5v19c0 .83.67 1.5 1.5 1.5h15c.83 0 1.5-.67 1.5-1.5v-19c0-.83-.67-1.5-1.5-1.5zm-15 1h15v19h-15v-19z"/>
                                    </svg>
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white ml-4">Outlook</h4>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Connect your Outlook account using OAuth for email, tasks, and calendar integration.</p>
                            </div>

                            <!-- IMAP Manual -->
                            <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 cursor-pointer transition-colors" onclick="selectAccountType('imap')">
                                <div class="flex items-center mb-3">
                                    <svg class="h-4 w-4 text-gray-500 mr-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white ml-4">IMAP</h4>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Manual IMAP/SMTP configuration for any email provider.</p>
                            </div>
                        </div>
                    </div>

                    <!-- OAuth Setup (Gmail/Outlook) -->
                    <div id="oauth-setup" class="hidden mb-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">OAuth Setup</h3>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Secure OAuth Connection</h4>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                        Click the button below to securely connect your account. You'll be redirected to authorize access to your emails, tasks, and calendar.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button id="oauth-button" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
                                    Connect Account
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- IMAP Setup Form -->
                    <div id="imap-setup" class="hidden">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">IMAP Configuration</h3>
                        <form id="imap-form" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Name</label>
                                    <input type="text" name="name" id="name" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                    <input type="email" name="email" id="email" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="imap_host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IMAP Host</label>
                                    <input type="text" name="imap_host" id="imap_host" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="imap_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">IMAP Port</label>
                                    <input type="number" name="imap_port" id="imap_port" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="imap_encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Encryption</label>
                                    <select name="imap_encryption" id="imap_encryption" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="ssl">SSL</option>
                                        <option value="tls">TLS</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="imap_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                                    <input type="text" name="imap_username" id="imap_username" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="imap_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                    <input type="password" name="imap_password" id="imap_password" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('email-accounts.index') }}" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
                                    Create Account
                                </button>
                            </div>
                        </form>
                    </div>
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
                        Connect Account
                    </h3>
                    <button onclick="closeOAuthModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400" id="oauth-modal-description">
                        You will be redirected to authorize access to your account. Please complete the authorization and return to this page.
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
        let selectedType = null;
        let oauthRedirectUrl = null;

        function selectAccountType(type) {
            selectedType = type;
            
            // Reset all selections
            document.querySelectorAll('[onclick^="selectAccountType"]').forEach(el => {
                el.classList.remove('border-blue-500', 'dark:border-blue-400');
                el.classList.add('border-gray-200', 'dark:border-gray-700');
            });
            
            // Highlight selected
            event.currentTarget.classList.remove('border-gray-200', 'dark:border-gray-700');
            event.currentTarget.classList.add('border-blue-500', 'dark:border-blue-400');
            
            // Show appropriate setup
            document.getElementById('oauth-setup').classList.add('hidden');
            document.getElementById('imap-setup').classList.add('hidden');
            
            if (type === 'gmail' || type === 'outlook' || type === 'google-tasks') {
                document.getElementById('oauth-setup').classList.remove('hidden');
                const button = document.getElementById('oauth-button');
                if (type === 'gmail') {
                    button.textContent = 'Connect Gmail Email Account';
                } else if (type === 'google-tasks') {
                    button.textContent = 'Connect Google Tasks Account';
                } else {
                    button.textContent = `Connect ${type.charAt(0).toUpperCase() + type.slice(1)} Account`;
                }
                button.onclick = () => connectOAuth(type);
            } else if (type === 'imap') {
                document.getElementById('imap-setup').classList.remove('hidden');
            }
        }

        function connectOAuth(type) {
            let url;
            let title;
            let description;
            
            if (type === 'gmail') {
                url = '/email-accounts/google/redirect';
                title = 'Connect Gmail Email Account';
                description = 'You will be redirected to Google to authorize access to your Gmail account. Please complete the authorization and return to this page.';
            } else if (type === 'google-tasks') {
                url = '/email-accounts/google-tasks/redirect';
                title = 'Connect Google Tasks Account';
                description = 'You will be redirected to Google to authorize access to your Google Tasks. Please complete the authorization and return to this page.';
            } else {
                url = '/email-accounts/microsoft/redirect';
                title = 'Connect Outlook Account';
                description = 'You will be redirected to Microsoft to authorize access to your Outlook account. Please complete the authorization and return to this page.';
            }
            
            // Show loading state
            const button = document.getElementById('oauth-button');
            const originalText = button.textContent;
            button.textContent = 'Connecting...';
            button.disabled = true;
            
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
                alert('Error connecting OAuth account: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                button.textContent = originalText;
                button.disabled = false;
            });
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
                        // Use a more robust approach - redirect the current window after a delay
                        // This avoids COOP issues with popup.closed
                        setTimeout(() => {
                            closeOAuthModal();
                            // Redirect to email accounts page after a reasonable delay
                            setTimeout(() => {
                                window.location.href = '/email-accounts';
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

        document.getElementById('imap-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('type', 'imap');
            
            fetch('/email-accounts', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    window.location.href = '/email-accounts';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating email account');
            });
        });
    </script>
</x-app-layout> 