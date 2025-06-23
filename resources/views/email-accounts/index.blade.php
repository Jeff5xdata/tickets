<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Email Accounts') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('email-accounts.signatures.index') }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Manage Signatures
                </a>
                <a href="{{ route('email-accounts.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add Email Account
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($emailAccounts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($emailAccounts as $account)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            @if($account->type === 'gmail')
                                                <svg class="h-8 w-8 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-.904.732-1.636 1.636-1.636h.819L12 10.183l9.545-6.362h.819c.904 0 1.636.732 1.636 1.636z"/>
                                                </svg>
                                            @elseif($account->type === 'outlook')
                                                <svg class="h-8 w-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M23.5 1h-15c-.83 0-1.5.67-1.5 1.5v19c0 .83.67 1.5 1.5 1.5h15c.83 0 1.5-.67 1.5-1.5v-19c0-.83-.67-1.5-1.5-1.5zm-15 1h15v19h-15v-19z"/>
                                                </svg>
                                            @else
                                                <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $account->name }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $account->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="toggleStatus({{ $account->id }})" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors {{ $account->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-800' }}" title="Click to {{ $account->is_active ? 'deactivate' : 'activate' }} account">
                                            <div class="w-1.5 h-1.5 mr-1 rounded-full {{ $account->is_active ? 'bg-green-400' : 'bg-red-400' }}"></div>
                                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Type:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ ucfirst($account->type) }}</span>
                                    </div>
                                    @if($account->provider)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Provider:</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ ucfirst($account->provider) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Tickets:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ $account->tickets()->count() }}</span>
                                    </div>
                                </div>

                                <div class="flex space-x-2">
                                    <a href="{{ route('email-accounts.show', $account) }}" class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-center py-2 px-4 rounded text-sm font-medium transition-colors">
                                        View
                                    </a>
                                    <a href="{{ route('email-accounts.edit', $account) }}" class="flex-1 bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 text-center py-2 px-4 rounded text-sm font-medium transition-colors">
                                        Edit
                                    </a>
                                    <button onclick="deleteAccount({{ $account->id }})" class="flex-1 bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-300 text-center py-2 px-4 rounded text-sm font-medium transition-colors">
                                        Delete
                                    </button>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                                    <button onclick="toggleStatus({{ $account->id }})" class="w-full {{ $account->is_active ? 'bg-amber-100 dark:bg-amber-900 hover:bg-amber-200 dark:hover:bg-amber-800 text-amber-700 dark:text-amber-300' : 'bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-700 dark:text-green-300' }} text-center py-2 px-4 rounded text-sm font-medium transition-colors">
                                        {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    
                                    @if($account->is_active)
                                        <button onclick="testConnection({{ $account->id }})" class="w-full bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 text-center py-2 px-4 rounded text-sm font-medium transition-colors">
                                            Test Connection
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No email accounts</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding your first email account.</p>
                    <div class="mt-6">
                        <a href="{{ route('email-accounts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add Email Account
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function deleteAccount(accountId) {
            if (confirm('Are you sure you want to delete this email account? This action cannot be undone.')) {
                fetch(`/email-accounts/${accountId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting email account');
                });
            }
        }

        function toggleStatus(accountId) {
            const action = event.target.textContent.trim();
            const confirmMessage = action === 'Activate' 
                ? 'Are you sure you want to activate this email account?' 
                : 'Are you sure you want to deactivate this email account?';
            
            if (confirm(confirmMessage)) {
                fetch(`/email-accounts/${accountId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating account status');
                });
            }
        }

        function testConnection(accountId) {
            fetch(`/email-accounts/${accountId}/test-connection`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error testing connection');
            });
        }
    </script>
</x-app-layout> 