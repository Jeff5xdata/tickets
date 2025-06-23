<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Calendar Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('calendar-events.update', $calendarEvent) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event Title
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', $calendarEvent->title) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $calendarEvent->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Location
                            </label>
                            <input type="text" name="location" id="location" value="{{ old('location', $calendarEvent->location) }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date and Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Start Time
                                </label>
                                <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time', $calendarEvent->start_time->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @error('start_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    End Time
                                </label>
                                <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time', $calendarEvent->end_time->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @error('end_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- All Day Event -->
                        <div class="flex items-center">
                            <input type="checkbox" name="all_day" id="all_day" value="1" {{ old('all_day', $calendarEvent->all_day) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="all_day" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                All day event
                            </label>
                        </div>

                        <!-- Attendees -->
                        <div>
                            <label for="attendees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Attendees (one email per line)
                            </label>
                            <textarea name="attendees" id="attendees" rows="3" placeholder="attendee1@example.com&#10;attendee2@example.com" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('attendees', $calendarEvent->attendees ? implode("\n", $calendarEvent->attendees) : '') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter one email address per line</p>
                            @error('attendees')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Provider Info -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Event Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Provider:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ ucfirst($calendarEvent->provider) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ ucfirst($calendarEvent->status) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Calendar:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $calendarEvent->emailAccount->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">External ID:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-mono">{{ $calendarEvent->external_id ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('calendar-events.show', $calendarEvent) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle all day event toggle
        document.getElementById('all_day').addEventListener('change', function() {
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            
            if (this.checked) {
                startTime.type = 'date';
                endTime.type = 'date';
                
                // Convert datetime-local values to date values
                if (startTime.value) {
                    startTime.value = startTime.value.split('T')[0];
                }
                if (endTime.value) {
                    endTime.value = endTime.value.split('T')[0];
                }
            } else {
                startTime.type = 'datetime-local';
                endTime.type = 'datetime-local';
                
                // Convert date values back to datetime-local values
                if (startTime.value) {
                    startTime.value = startTime.value + 'T00:00';
                }
                if (endTime.value) {
                    endTime.value = endTime.value + 'T00:00';
                }
            }
        });
    </script>
</x-app-layout> 