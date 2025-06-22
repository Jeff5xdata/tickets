<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Google Tasks') }}
            </h2>
            <div class="flex space-x-2">
                <button onclick="syncTasks()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Sync Tasks
                </button>
                <a href="{{ route('google-tasks.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add Task
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Filters and Search -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select id="status_filter" onchange="filterTasks()" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Statuses</option>
                                <option value="needsAction">Needs Action</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div>
                            <label for="list_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Task List</label>
                            <select id="list_filter" onchange="filterTasks()" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Lists</option>
                                @foreach($taskLists as $list)
                                    <option value="{{ $list->list_id }}">{{ $list->list_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                            <input type="text" id="search" placeholder="Search tasks..." onkeyup="filterTasks()" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex items-end">
                            <button onclick="clearFilters()" class="w-full bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if($googleTasks->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($googleTasks as $task)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg task-card" 
                             data-status="{{ $task->completed ? 'completed' : 'needsAction' }}" 
                             data-list="{{ $task->list_id }}"
                             data-title="{{ strtolower($task->title) }}">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-start">
                                        <!-- <div class="flex-shrink-0 mt-1">
                                            <input type="checkbox" 
                                                   {{ $task->completed ? 'checked' : '' }}
                                                   onchange="toggleTaskStatus('{{ $task->id }}', this.checked)"
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        </div> -->
                                        <div class="ml-3 flex-1">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white {{ $task->completed ? 'line-through text-gray-500' : '' }}">
                                                {{ $task->title }}
                                            </h3>
                                            @if($task->notes)
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                                    {{ Str::limit($task->notes, 100) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $task->completed ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                            {{ $task->completed ? 'Completed' : 'Pending' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    @if($task->list_name)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">List:</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ $task->list_name }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($task->due_date)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Due:</span>
                                            <span class="text-gray-900 dark:text-white font-medium {{ $task->due_date < now() && !$task->completed ? 'text-red-600 dark:text-red-400' : '' }}">
                                                {{ $task->due_date->format('M d, Y') }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if($task->priority)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Priority:</span>
                                            <span class="text-gray-900 dark:text-white font-medium">
                                                @if($task->priority === 'high')
                                                    <span class="text-red-600 dark:text-red-400">High</span>
                                                @elseif($task->priority === 'medium')
                                                    <span class="text-yellow-600 dark:text-yellow-400">Medium</span>
                                                @else
                                                    <span class="text-green-600 dark:text-green-400">Low</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex space-x-2">
                                    <a href="{{ route('google-tasks.show', $task) }}" class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-center py-2 px-4 rounded text-sm font-medium transition-colors">
                                        View
                                    </a>
                                    <a href="{{ route('google-tasks.edit', $task) }}" class="flex-1 bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 text-center py-2 px-4 rounded text-sm font-medium transition-colors">
                                        Edit
                                    </a>
                                    <button onclick="deleteTask('{{ $task->id }}')" class="flex-1 bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-300 text-center py-2 px-4 rounded text-sm font-medium transition-colors">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tasks found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first task or syncing from Google.</p>
                    <div class="mt-6 flex justify-center space-x-3">
                        <button onclick="syncTasks()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Sync from Google
                        </button>
                        <a href="{{ route('google-tasks.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add Task
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function syncTasks() {
            if (confirm('This will sync tasks from your Google account. Continue?')) {
                fetch('/google-tasks/sync', {
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
                    alert('Error syncing tasks');
                });
            }
        }

        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
                fetch(`/google-tasks/${taskId}`, {
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
                    alert('Error deleting task');
                });
            }
        }

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
                    // Update the UI without reloading
                    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                    if (taskCard) {
                        const title = taskCard.querySelector('h3');
                        const statusBadge = taskCard.querySelector('.inline-flex');
                        
                        if (completed) {
                            title.classList.add('line-through', 'text-gray-500');
                            statusBadge.textContent = 'Completed';
                            statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                        } else {
                            title.classList.remove('line-through', 'text-gray-500');
                            statusBadge.textContent = 'Pending';
                            statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating task status');
            });
        }

        function filterTasks() {
            const statusFilter = document.getElementById('status_filter').value;
            const listFilter = document.getElementById('list_filter').value;
            const searchFilter = document.getElementById('search').value.toLowerCase();
            
            const taskCards = document.querySelectorAll('.task-card');
            
            taskCards.forEach(card => {
                const status = card.getAttribute('data-status');
                const list = card.getAttribute('data-list');
                const title = card.getAttribute('data-title');
                
                let show = true;
                
                if (statusFilter && status !== statusFilter) {
                    show = false;
                }
                
                if (listFilter && list !== listFilter) {
                    show = false;
                }
                
                if (searchFilter && !title.includes(searchFilter)) {
                    show = false;
                }
                
                card.style.display = show ? 'block' : 'none';
            });
        }

        function clearFilters() {
            document.getElementById('status_filter').value = '';
            document.getElementById('list_filter').value = '';
            document.getElementById('search').value = '';
            filterTasks();
        }
    </script>
</x-app-layout> 