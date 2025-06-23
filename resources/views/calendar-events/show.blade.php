<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $calendarEvent->title }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('calendar-events.edit', $calendarEvent) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Event
                </a>
                <form action="{{ route('calendar-events.destroy', $calendarEvent) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Delete Event
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Event Header -->
                    <div class="mb-6">
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($calendarEvent->provider === 'google') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @elseif($calendarEvent->provider === 'microsoft') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                {{ ucfirst($calendarEvent->provider) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($calendarEvent->status === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($calendarEvent->status === 'tentative') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                {{ ucfirst($calendarEvent->status) }}
                            </span>
                            @if($calendarEvent->all_day)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    All Day
                                </span>
                            @endif
                        </div>
                        
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ $calendarEvent->title }}
                        </h1>
                        
                        <p class="text-gray-600 dark:text-gray-400">
                            Calendar: {{ $calendarEvent->emailAccount->name }}
                        </p>
                    </div>

                    <!-- Event Details Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Date and Time -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Date & Time</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">Start</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $calendarEvent->formatted_start_time }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">End</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $calendarEvent->formatted_end_time }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">Duration</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $calendarEvent->duration }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
                            @if($calendarEvent->location)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Location</h3>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <div class="text-gray-900 dark:text-white">{{ $calendarEvent->location }}</div>
                                    </div>
                                </div>
                            @endif

                            <!-- Description -->
                            @if($calendarEvent->description)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
                                    <div class="prose dark:prose-invert max-w-none">
                                        {!! nl2br(e($calendarEvent->description)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Attendees -->
                            @if($calendarEvent->attendees && count($calendarEvent->attendees) > 0)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Attendees</h3>
                                    <div class="space-y-2">
                                        @foreach($calendarEvent->attendees as $attendee)
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <div class="text-gray-900 dark:text-white">{{ $attendee }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Recurrence -->
                            @if($calendarEvent->recurrence)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Recurrence</h3>
                                    <div class="space-y-1">
                                        @if(isset($calendarEvent->recurrence['frequency']))
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Frequency:</span> {{ ucfirst($calendarEvent->recurrence['frequency']) }}
                                            </div>
                                        @endif
                                        @if(isset($calendarEvent->recurrence['interval']))
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Interval:</span> {{ $calendarEvent->recurrence['interval'] }}
                                            </div>
                                        @endif
                                        @if(isset($calendarEvent->recurrence['count']))
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Occurrences:</span> {{ $calendarEvent->recurrence['count'] }}
                                            </div>
                                        @endif
                                        @if(isset($calendarEvent->recurrence['until']))
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Until:</span> {{ $calendarEvent->recurrence['until']->format('M j, Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Event Details -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Event Details</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">External ID:</span>
                                        <span class="text-gray-900 dark:text-white font-mono">{{ $calendarEvent->external_id ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Calendar ID:</span>
                                        <span class="text-gray-900 dark:text-white font-mono">{{ $calendarEvent->calendar_id ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Created:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $calendarEvent->created_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Last Updated:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $calendarEvent->updated_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('calendar-events.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Events
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 