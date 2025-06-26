<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Display Settings</h3>
        
        <!-- Layout Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Layout Settings</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="dashboard_layout" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dashboard Layout</label>
                    <select name="dashboard_layout" id="dashboard_layout" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($dashboardLayouts as $value => $label)
                            <option value="{{ $value }}" {{ $preferences->dashboard_layout === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose how your dashboard is organized</p>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="compact_mode" id="compact_mode" value="1" {{ $preferences->compact_mode ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="compact_mode" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Compact Mode</label>
                </div>
            </div>
        </div>

        <!-- Navigation Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Navigation Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="show_sidebar" id="show_sidebar" value="1" {{ $preferences->show_sidebar ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="show_sidebar" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Show Sidebar</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="show_breadcrumbs" id="show_breadcrumbs" value="1" {{ $preferences->show_breadcrumbs ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="show_breadcrumbs" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Show Breadcrumbs</label>
                </div>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Activity Feed</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="show_activity_feed" id="show_activity_feed" value="1" {{ $preferences->show_activity_feed ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="show_activity_feed" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Show Activity Feed</label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Display recent activity and updates on the dashboard</p>
            </div>
        </div>
    </div>
</div> 