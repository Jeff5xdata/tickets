<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Tickets') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('tickets.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Create Ticket
                </a>
                <button onclick="fetchEmails()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Fetch Emails
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('tickets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Waiting</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                            <select name="priority" id="priority" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="all" {{ request('priority') === 'all' ? 'selected' : '' }}>All Priorities</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="email_account" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Account</label>
                            <select name="email_account" id="email_account" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="all" {{ request('email_account') === 'all' ? 'selected' : '' }}>All Accounts</option>
                                @foreach($emailAccounts as $account)
                                    <option value="{{ $account->id }}" {{ request('email_account') == $account->id ? 'selected' : '' }}>{{ $account->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search tickets..." class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="md:col-span-4 flex justify-end space-x-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                            @if($tickets->total() > 0)
                                <button type="button" onclick="deleteFilteredTickets({{ $tickets->total() }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Delete Filtered ({{ $tickets->total() }})
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tickets List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($tickets->count() > 0)
                        <div class="overflow-x-auto">
                            <!-- Summary Row -->
                            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-6">
                                        <span class="text-gray-700 dark:text-gray-300">
                                            <strong>Total Tickets:</strong> {{ $tickets->total() }}
                                        </span>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            <strong>New:</strong> {{ $tickets->where('status', 'new')->count() }}
                                        </span>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            <strong>In Progress:</strong> {{ $tickets->where('status', 'in_progress')->count() }}
                                        </span>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            <strong>Resolved:</strong> {{ $tickets->where('status', 'resolved')->count() }}
                                        </span>
                                    </div>
                                    <div class="text-gray-700 dark:text-gray-300">
                                        <strong>Showing:</strong> {{ $tickets->firstItem() ?? 0 }} - {{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }}
                                    </div>
                                </div>
                            </div>
                            
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-2/5">Subject & From</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/8">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/8">Priority</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">Received</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr>
                                        <td colspan="5">
                                        </td>
                                    </tr>
                                    @foreach($tickets as $ticket)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white break-words" title="{{ $ticket->subject }}">
                                                        {{ $ticket->subject }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400 break-words" title="{{ $ticket->from_name ?: $ticket->from_email }}">
                                                        from: {{ $ticket->from_name ?: $ticket->from_email }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400 truncate mt-1" title="{{ Str::limit($ticket->original_content, 100) }}">
                                                        {{ Str::limit($ticket->original_content, 80) }}
                                                    </div>
                                                    @if(!empty($ticket->attachments))
                                                        <div class="mt-1 flex items-center text-xs text-gray-400 dark:text-gray-500">
                                                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                            </svg>
                                                            {{ count($ticket->attachments) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($ticket->status === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                    @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                    @elseif($ticket->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($ticket->priority === 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                    @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                    @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 dark:text-white">
                                                    {{ $ticket->received_at->format('M j') }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $ticket->received_at->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('tickets.show', $ticket) }}" 
                                                       class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                        View
                                                    </a>
                                                    <button onclick="deleteTicket({{ $ticket->id }}, '{{ addslashes($ticket->subject) }}')" 
                                                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $tickets->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tickets</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by connecting an email account and fetching emails.</p>
                            <div class="mt-6">
                                <a href="{{ route('email-accounts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Add Email Account
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function fetchEmails() {
            // Show loading state
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Fetching...';
            button.disabled = true;
            
            // Make AJAX call to fetch emails
            fetch('{{ route("emails.fetch-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                let alertMessage = '';

                if (data.failed_accounts && data.failed_accounts.length > 0) {
                    alertMessage += `The following email accounts require re-authentication:\n- ${data.failed_accounts.join('\n- ')}\n\n`;
                    alertMessage += 'They have been deactivated. Please reconnect them on the Email Accounts page.\n\n';
                }

                if (data.email_count > 0) {
                    alertMessage += `✅ Successfully processed ${data.email_count} email(s).`;
                } else if (data.failed_accounts && data.failed_accounts.length > 0) {
                    // Message already handled above
                } 
                else {
                    alertMessage += `ℹ️ ${data.message}`;
                }
                
                alert(alertMessage);
                
                // Reload the page to show changes
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching emails. Please try again.');
            })
            .finally(() => {
                // Restore button state
                button.textContent = originalText;
                button.disabled = false;
            });
        }
    </script>
    <script>
        function deleteTicket(ticketId, ticketSubject) {
            if (confirm(`Are you sure you want to delete the ticket "${ticketSubject}"?\n\nThis action cannot be undone.`)) {
                // Show loading state
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Deleting...';
                button.disabled = true;
                
                // Make AJAX call to delete ticket
                fetch(`/tickets/${ticketId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(`✅ ${data.message}`);
                    }
                    // Reload the page to reflect changes
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting ticket. Please try again.');
                })
                .finally(() => {
                    // Restore button state
                    button.textContent = originalText;
                    button.disabled = false;
                });
            }
        }

        function deleteFilteredTickets(count) {
            if (confirm(`Are you sure you want to delete ${count} ticket(s) matching the current filters?\n\nThis action cannot be undone.`)) {
                // Show loading state
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Deleting...';
                button.disabled = true;

                // Get filter data from the form
                const form = document.querySelector('form');
                const formData = new FormData(form);
                const params = new URLSearchParams(formData).toString();

                // Make AJAX call to delete filtered tickets
                fetch(`/tickets/delete-filtered?${params}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(`✅ ${data.message}`);
                    }
                    // Reload the page to reflect changes
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting filtered tickets. Please try again.');
                })
                .finally(() => {
                    // Restore button state
                    button.textContent = originalText;
                    button.disabled = false;
                });
            }
        }
    </script>
    <script>
        setTimeout(function() {
            window.location.reload();
        }, 60000); // 60,000 ms = 1 minute
    </script>
</x-app-layout> 