<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Privacy Settings</h3>
        
        <!-- Analytics Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Analytics Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="share_analytics" id="share_analytics" value="1" {{ $preferences->share_analytics ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="share_analytics" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Share Analytics Data</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Help improve the application by sharing anonymous usage data</p>
            </div>
        </div>

        <!-- Cookie Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Cookie Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="allow_cookies" id="allow_cookies" value="1" {{ $preferences->allow_cookies ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="allow_cookies" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Allow Cookies</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Allow the application to store cookies for better functionality</p>
            </div>
        </div>

        <!-- Online Status -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Online Status</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="show_online_status" id="show_online_status" value="1" {{ $preferences->show_online_status ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="show_online_status" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Show Online Status</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Display your online status to other users</p>
            </div>
        </div>

        <!-- Privacy Information -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Privacy Information</h4>
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Your Privacy Matters</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>We respect your privacy. These settings control how your data is used and shared. You can change these settings at any time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 