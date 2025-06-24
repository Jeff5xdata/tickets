<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Calendar Settings</h3>
        
        <!-- Calendar Sync Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Sync Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="calendar_sync_enabled" id="calendar_sync_enabled" value="1" {{ $preferences->calendar_sync_enabled ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="calendar_sync_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Calendar Sync</label>
                </div>
                <div>
                    <label for="calendar_sync_frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sync Frequency (minutes)</label>
                    <input type="number" name="calendar_sync_frequency" id="calendar_sync_frequency" value="{{ $preferences->calendar_sync_frequency }}" min="1" max="1440" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">How often to sync with external calendars</p>
                </div>
            </div>
        </div>

        <!-- Display Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Display Settings</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="default_calendar_view" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Calendar View</label>
                    <select name="default_calendar_view" id="default_calendar_view" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($calendarViews as $value => $label)
                            <option value="{{ $value }}" {{ $preferences->default_calendar_view === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default view when opening calendar</p>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="show_calendar_events" id="show_calendar_events" value="1" {{ $preferences->show_calendar_events ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="show_calendar_events" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Show Calendar Events</label>
                </div>
            </div>
        </div>

        <!-- Automation Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Automation Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="auto_create_calendar_events" id="auto_create_calendar_events" value="1" {{ $preferences->auto_create_calendar_events ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="auto_create_calendar_events" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Automatically Create Calendar Events from Tickets</label>
                </div>
            </div>
        </div>
    </div>
</div> 