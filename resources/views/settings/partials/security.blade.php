<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Security Settings</h3>
        
        <!-- Two-Factor Authentication -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Two-Factor Authentication</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="two_factor_enabled" id="two_factor_enabled" value="1" {{ $preferences->two_factor_enabled ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="two_factor_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Two-Factor Authentication</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add an extra layer of security to your account</p>
            </div>
        </div>

        <!-- Session Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Session Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="session_timeout_enabled" id="session_timeout_enabled" value="1" {{ $preferences->session_timeout_enabled ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="session_timeout_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Session Timeout</label>
                </div>
                <div>
                    <label for="session_timeout_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Session Timeout (minutes)</label>
                    <input type="number" name="session_timeout_minutes" id="session_timeout_minutes" value="{{ $preferences->session_timeout_minutes }}" min="5" max="1440" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Automatically log out after inactivity</p>
                </div>
            </div>
        </div>

        <!-- Login Notifications -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Login Notifications</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="login_notifications" id="login_notifications" value="1" {{ $preferences->login_notifications ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="login_notifications" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Notify on New Login</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receive notifications when your account is accessed from a new device</p>
            </div>
        </div>
    </div>
</div> 