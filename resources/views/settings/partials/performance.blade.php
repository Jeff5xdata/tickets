<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Performance Settings</h3>
        
        <!-- Caching Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Caching Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="enable_caching" id="enable_caching" value="1" {{ $preferences->enable_caching ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="enable_caching" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Caching</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Cache frequently accessed data to improve performance</p>
            </div>
        </div>

        <!-- Loading Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Loading Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="lazy_loading" id="lazy_loading" value="1" {{ $preferences->lazy_loading ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="lazy_loading" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Lazy Loading</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Load content only when needed to improve initial page load</p>
                
                <div class="flex items-center">
                    <input type="checkbox" name="preload_images" id="preload_images" value="1" {{ $preferences->preload_images ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="preload_images" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Preload Images</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Preload images for faster display (may use more bandwidth)</p>
            </div>
        </div>

        <!-- Performance Information -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Performance Information</h4>
            <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Performance Optimization</h3>
                        <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                            <p>These settings help optimize the application performance. Enable caching and lazy loading for better user experience.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 