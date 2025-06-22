<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $googleTask->title }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('google-tasks.edit', $googleTask) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Task
                </a>
                <a href="{{ route('google-tasks.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Tasks
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Task Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <input type="checkbox" 
                                       {{ $googleTask->completed ? 'checked' : '' }}
                                       onchange="toggleTaskStatus('{{ $googleTask->id }}', this.checked)"
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            </div>
                            <div class="ml-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white {{ $googleTask->completed ? 'line-through text-gray-500' : '' }}">
                                    {{ $googleTask->title }}
                                </h3>
                                <div class="flex items-center mt-2 space-x-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $googleTask->completed ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                        {{ $googleTask->completed ? 'Completed' : 'Pending' }}
                                    </span>
                                    @if($googleTask->priority)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            @if($googleTask->priority === 'high') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif($googleTask->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                            {{ ucfirst($googleTask->priority) }} Priority
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            @if($googleTask->list_name)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Task List</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $googleTask->list_name }}</p>
                                </div>
                            @endif
                            
                            @if($googleTask->due_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white {{ $googleTask->due_date < now() && !$googleTask->completed ? 'text-red-600 dark:text-red-400' : '' }}">
                                        {{ $googleTask->due_date->format('F j, Y \a\t g:i A') }}
                                        @if($googleTask->due_date < now() && !$googleTask->completed)
                                            <span class="ml-2 text-xs">(Overdue)</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                            
                            @if($googleTask->parentTask)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Parent Task</label>
                                    <a href="{{ route('google-tasks.show', $googleTask->parentTask) }}" class="mt-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $googleTask->parentTask->title }}
                                    </a>
                                </div>
                            @endif
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $googleTask->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $googleTask->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                            
                            @if($googleTask->completed_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Completed</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $googleTask->completed_at->format('M d, Y H:i') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($googleTask->notes)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Notes</label>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $googleTask->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Subtasks -->
            @if($googleTask->subtasks->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Subtasks</h3>
                    <div class="space-y-3">
                        @foreach($googleTask->subtasks as $subtask)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       {{ $subtask->completed ? 'checked' : '' }}
                                       onchange="toggleTaskStatus('{{ $subtask->id }}', this.checked)"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <div class="ml-3">
                                    <a href="{{ route('google-tasks.show', $subtask) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 {{ $subtask->completed ? 'line-through text-gray-500' : '' }}">
                                        {{ $subtask->title }}
                                    </a>
                                    @if($subtask->due_date)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Due: {{ $subtask->due_date->format('M d, Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $subtask->completed ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                {{ $subtask->completed ? 'Completed' : 'Pending' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Related Tickets -->
            @if($googleTask->tickets->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Related Tickets</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($googleTask->tickets as $ticket)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($ticket->subject, 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($ticket->status === 'open') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $ticket->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button onclick="createTicketFromTask()" class="bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 text-center py-3 px-4 rounded-lg text-sm font-medium transition-colors">
                            <svg class="h-5 w-5 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Create Ticket
                        </button>
                        <button onclick="duplicateTask()" class="bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-700 dark:text-green-300 text-center py-3 px-4 rounded-lg text-sm font-medium transition-colors">
                            <svg class="h-5 w-5 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Duplicate Task
                        </button>
                        <button onclick="deleteTask()" class="bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-300 text-center py-3 px-4 rounded-lg text-sm font-medium transition-colors">
                            <svg class="h-5 w-5 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Task
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTaskStatus(taskId, completed) {
            fetch(`/google-tasks/${taskId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    completed: completed
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Update the UI
                    const title = document.querySelector('h3');
                    const statusBadge = document.querySelector('.inline-flex');
                    const checkbox = document.querySelector('input[type="checkbox"]');
                    
                    if (completed) {
                        title.classList.add('line-through', 'text-gray-500');
                        statusBadge.textContent = 'Completed';
                        statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                    } else {
                        title.classList.remove('line-through', 'text-gray-500');
                        statusBadge.textContent = 'Pending';
                        statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating task status');
            });
        }

        function createTicketFromTask() {
            if (confirm('Create a ticket from this task?')) {
                fetch(`/google-tasks/{{ $googleTask->id }}/create-ticket`, {
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
                        if (data.ticket_id) {
                            window.location.href = `/tickets/${data.ticket_id}`;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating ticket');
                });
            }
        }

        function duplicateTask() {
            if (confirm('Duplicate this task?')) {
                fetch(`/google-tasks/{{ $googleTask->id }}/duplicate`, {
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
                        if (data.task_id) {
                            window.location.href = `/google-tasks/${data.task_id}`;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error duplicating task');
                });
            }
        }

        function deleteTask() {
            if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
                fetch(`/google-tasks/{{ $googleTask->id }}`, {
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
                        window.location.href = '/google-tasks';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting task');
                });
            }
        }
        
    </script>
</x-app-layout> 