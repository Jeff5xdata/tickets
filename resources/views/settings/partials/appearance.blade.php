<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Appearance Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Theme -->
            <div>
                <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Theme</label>
                <select name="theme" id="theme" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach($themes as $value => $label)
                        <option value="{{ $value }}" {{ $preferences->theme === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose your preferred color theme</p>
            </div>

            <!-- Language -->
            <div>
                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
                <select name="language" id="language" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach($languages as $value => $label)
                        <option value="{{ $value }}" {{ $preferences->language === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select your preferred language</p>
            </div>

            <!-- Timezone -->
            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                <select name="timezone" id="timezone" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach($timezones as $value => $label)
                        <option value="{{ $value }}" {{ $preferences->timezone === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Set your local timezone</p>
            </div>

            <!-- Date Format -->
            <div>
                <label for="date_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Format</label>
                <select name="date_format" id="date_format" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="Y-m-d" {{ $preferences->date_format === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2024-01-15)</option>
                    <option value="m/d/Y" {{ $preferences->date_format === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (01/15/2024)</option>
                    <option value="d/m/Y" {{ $preferences->date_format === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (15/01/2024)</option>
                    <option value="M j, Y" {{ $preferences->date_format === 'M j, Y' ? 'selected' : '' }}>Jan 15, 2024</option>
                    <option value="j M Y" {{ $preferences->date_format === 'j M Y' ? 'selected' : '' }}>15 Jan 2024</option>
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose how dates are displayed</p>
            </div>

            <!-- Time Format -->
            <div>
                <label for="time_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time Format</label>
                <select name="time_format" id="time_format" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="H:i" {{ $preferences->time_format === 'H:i' ? 'selected' : '' }}>24-hour (14:30)</option>
                    <option value="g:i A" {{ $preferences->time_format === 'g:i A' ? 'selected' : '' }}>12-hour (2:30 PM)</option>
                    <option value="g:i a" {{ $preferences->time_format === 'g:i a' ? 'selected' : '' }}>12-hour (2:30 pm)</option>
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose how times are displayed</p>
            </div>
        </div>
    </div>
</div> 