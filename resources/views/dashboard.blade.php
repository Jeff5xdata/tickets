<x-app-layout>
    @php
        $userPreferences = auth()->user()->getPreferences();
    @endphp
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
        </div>
    </x-slot>

    <!-- Refresh Overlay -->
    <div id="refreshOverlay" class="fixed inset-0 bg-gray-300 bg-opacity-50 z-50 transition-opacity duration-1500 ease-in-out opacity-0 pointer-events-none"></div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="flex flex-col md:flex-row gap-6 mb-8">
                <!-- Total Tickets -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tickets</div>
                                @php
                                    $totalTicketsQuery = auth()->user()->tickets();
                                    
                                    // Filter out closed tickets if user preference is set to false
                                    if (!$userPreferences->show_closed_tickets) {
                                        $totalTicketsQuery->where('status', '!=', 'closed');
                                    }
                                @endphp
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalTicketsQuery->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Tickets -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">New Tickets</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->tickets()->where('status', 'new')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Accounts -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Tasks</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->googleTasks()->where('completed', false)->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Tasks -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Accounts</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->emailAccounts()->where('is_active', true)->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Events -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming Events</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->calendarEvents()->upcoming()->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <a href="{{ route('tickets.create') }}" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-6 w-6 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Create Ticket</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Create a new ticket</div>
                            </div>
                        </a>

                        <a href="{{ route('tickets.index') }}" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">View Tickets</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Manage email tickets</div>
                            </div>
                        </a>

                        <a href="{{ route('google-tasks.index') }}" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-6 w-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">View Tasks</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Manage tasks</div>
                            </div>
                        </a>

                        <a href="{{ route('calendar-events.create') }}" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-6 w-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Create Event</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Add calendar event</div>
                            </div>
                        </a>

                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Tickets -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Tickets</h3>
                            <a href="{{ route('tickets.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all</a>
                        </div>
                        <div class="space-y-4">
                            @php
                                $recentTicketsQuery = auth()->user()->tickets()->latest();
                                
                                // Filter out closed tickets if user preference is set to false
                                if (!$userPreferences->show_closed_tickets) {
                                    $recentTicketsQuery->where('status', '!=', 'closed');
                                }
                                
                                $recentTickets = $recentTicketsQuery->take(5)->get();
                            @endphp
                            
                            @forelse($recentTickets as $ticket)
                                <a href="{{ route('tickets.show', $ticket) }}" class="block">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $ticket->subject }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $ticket->from_email }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($ticket->status === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @elseif($ticket->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->received_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No tickets yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Recent Tasks -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Tasks</h3>
                            <a href="{{ route('google-tasks.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all</a>
                        </div>
                        <div class="space-y-4">
                            @forelse(auth()->user()->googleTasks()->latest()->take(5)->get() as $task)
                            <a href="{{ route('google-tasks.show', $task) }}" class="block">
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate @if($task->completed) line-through @endif">{{ $task->title }}</p>
                                            @if($task->due_date)
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Due: {{ $task->due_date->format('M j, Y') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($task->priority === 'high') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </div>
                            </a>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No tasks yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Upcoming Events</h3>
                            <a href="{{ route('calendar-events.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all</a>
                        </div>
                        <div class="space-y-4">
                            @forelse(auth()->user()->calendarEvents()->upcoming()->orderBy('start_time')->take(5)->get() as $event)
                            <a href="{{ route('calendar-events.show', $event) }}" class="block">
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $event->title }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event->formatted_start_time }}</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($event->provider === 'google') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif($event->provider === 'microsoft') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                            {{ ucfirst($event->provider) }}
                                        </span>
                                        @if($event->all_day)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                All Day
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No upcoming events</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTask(taskId, completed) {
            fetch(`/google-tasks/${taskId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ completed: completed })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Optionally show a success message
                    console.log(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Background refresh function
        function backgroundRefresh() {
            fetch(window.location.href, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Show-Closed-Tickets': '{{ $userPreferences->show_closed_tickets ? "true" : "false" }}'
                }
            })
            .then(response => response.text())
            .then(html => {
                // Create a temporary div to parse the new HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Update statistics cards
                const newStats = tempDiv.querySelectorAll('[class*="bg-white"] [class*="text-2xl"]');
                const currentStats = document.querySelectorAll('[class*="bg-white"] [class*="text-2xl"]');
                
                newStats.forEach((newStat, index) => {
                    if (currentStats[index] && newStat.textContent !== currentStats[index].textContent) {
                        currentStats[index].textContent = newStat.textContent;
                        // Add a subtle animation to indicate update
                        currentStats[index].classList.add('animate-pulse');
                        setTimeout(() => {
                            currentStats[index].classList.remove('animate-pulse');
                        }, 1000);
                    }
                });
                
                // Update recent tickets section
                const newTicketsSection = tempDiv.querySelector('.space-y-4');
                const currentTicketsSection = document.querySelector('.space-y-4');
                if (newTicketsSection && currentTicketsSection) {
                    currentTicketsSection.innerHTML = newTicketsSection.innerHTML;
                }
            })
            .catch(error => {
                console.error('Background refresh error:', error);
            });
        }

        // Set up background refresh every minute
        setInterval(backgroundRefresh, 60000); // 60,000 ms = 1 minute
    </script>
</x-app-layout>
