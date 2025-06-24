<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Task Settings</h3>
        
        <!-- Google Tasks Sync -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Google Tasks Sync</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="google_tasks_sync" id="google_tasks_sync" value="1" {{ $preferences->google_tasks_sync ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="google_tasks_sync" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Google Tasks Sync</label>
                </div>
                <div>
                    <label for="default_task_list" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Task List</label>
                    <input type="text" name="default_task_list" id="default_task_list" value="{{ $preferences->default_task_list }}" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="My Tasks">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default Google Tasks list to use</p>
                </div>
            </div>
        </div>

        <!-- Auto Save Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Auto Save Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="auto_save_tasks" id="auto_save_tasks" value="1" {{ $preferences->auto_save_tasks ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="auto_save_tasks" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Auto Save</label>
                </div>
                <div>
                    <label for="task_auto_save_interval" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Auto Save Interval (seconds)</label>
                    <input type="number" name="task_auto_save_interval" id="task_auto_save_interval" value="{{ $preferences->task_auto_save_interval }}" min="1" max="60" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">How often to auto-save task changes</p>
                </div>
            </div>
        </div>

        <!-- Display Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Display Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="show_completed_tasks" id="show_completed_tasks" value="1" {{ $preferences->show_completed_tasks ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="show_completed_tasks" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Show Completed Tasks</label>
                </div>
                <div>
                    <label for="task_sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Task Sort Order</label>
                    <select name="task_sort" id="task_sort" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($taskSorts as $value => $label)
                            <option value="{{ $value }}" {{ $preferences->task_sort == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default sorting order for task lists</p>
                </div>
            </div>
        </div>
    </div>
</div> 