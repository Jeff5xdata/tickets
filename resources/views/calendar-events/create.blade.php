<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Calendar Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('calendar-events.store') }}" class="space-y-6">
                        @csrf

                        <!-- Email Account Selection -->
                        <div>
                            <label for="email_account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Calendar Account
                            </label>
                            <select name="email_account_id" id="email_account_id" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select a calendar account</option>
                                @foreach($emailAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('email_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ ucfirst($account->provider) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('email_account_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Calendar Selection (will be populated via AJAX) -->
                        <div id="calendar-selection" class="hidden">
                            <label for="calendar_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Calendar
                            </label>
                            <select name="calendar_id" id="calendar_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Loading calendars...</option>
                            </select>
                            @error('calendar_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event Title
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Location
                            </label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                                <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time') }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @error('start_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    End Time
                                </label>
                                <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time') }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @error('end_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- All Day Event -->
                        <div class="flex items-center">
                            <input type="checkbox" name="all_day" id="all_day" value="1" {{ old('all_day') ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="all_day" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                All day event
                            </label>
                        </div>

                        <!-- Attendees -->
                        <div>
                            <label for="attendees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Attendees (one email per line)
                            </label>
                            <textarea name="attendees" id="attendees" rows="3" placeholder="attendee1@example.com&#10;attendee2@example.com" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('attendees') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter one email address per line</p>
                            @error('attendees')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('calendar-events.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('email_account_id').addEventListener('change', function() {
            const accountId = this.value;
            const calendarSelection = document.getElementById('calendar-selection');
            const calendarSelect = document.getElementById('calendar_id');
            
            if (accountId) {
                calendarSelection.classList.remove('hidden');
                calendarSelect.innerHTML = '<option value="">Loading calendars...</option>';
                
                fetch(`/calendar-events/calendars?email_account_id=${accountId}`)
                    .then(response => response.json())
                    .then(data => {
                        calendarSelect.innerHTML = '<option value="">Select a calendar</option>';
                        data.forEach(calendar => {
                            const option = document.createElement('option');
                            option.value = calendar.id;
                            option.textContent = calendar.name;
                            if (calendar.primary) {
                                option.textContent += ' (Primary)';
                            }
                            calendarSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading calendars:', error);
                        calendarSelect.innerHTML = '<option value="">Error loading calendars</option>';
                    });
            } else {
                calendarSelection.classList.add('hidden');
            }
        });

        // Set default start time to current time
        const now = new Date();
        const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.getElementById('start_time').value = localDateTime;
        
        // Set default end time to 1 hour later
        const oneHourLater = new Date(now.getTime() + 60 * 60 * 1000);
        const endDateTime = new Date(oneHourLater.getTime() - oneHourLater.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.getElementById('end_time').value = endDateTime;

        // Handle all day event toggle
        document.getElementById('all_day').addEventListener('change', function() {
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            
            if (this.checked) {
                startTime.type = 'date';
                endTime.type = 'date';
            } else {
                startTime.type = 'datetime-local';
                endTime.type = 'datetime-local';
            }
        });
    </script>
</x-app-layout> 