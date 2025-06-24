<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Email Settings</h3>
        
        <!-- Notification Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Notification Preferences</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="email_notifications" id="email_notifications" value="1" {{ $preferences->email_notifications ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="email_notifications" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Email Notifications</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="push_notifications" id="push_notifications" value="1" {{ $preferences->push_notifications ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="push_notifications" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Push Notifications</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="desktop_notifications" id="desktop_notifications" value="1" {{ $preferences->desktop_notifications ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="desktop_notifications" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Desktop Notifications</label>
                </div>
            </div>
        </div>

        <!-- Email Check Frequency -->
        <div class="mb-6">
            <label for="email_check_frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Check Frequency (minutes)</label>
            <input type="number" name="email_check_frequency" id="email_check_frequency" value="{{ $preferences->email_check_frequency }}" min="1" max="1440" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">How often to check for new emails (minimum 1 minute)</p>
        </div>

        <!-- Auto Reply Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Auto Reply Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="auto_reply_enabled" id="auto_reply_enabled" value="1" {{ $preferences->auto_reply_enabled ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="auto_reply_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Auto Reply</label>
                </div>
                <div>
                    <label for="auto_reply_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Auto Reply Message</label>
                    <textarea name="auto_reply_message" id="auto_reply_message" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Thank you for your email. I will get back to you as soon as possible.">{{ $preferences->auto_reply_message }}</textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Message to send as automatic reply</p>
                </div>
            </div>
        </div>

        <!-- Email Behavior -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Email Behavior</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="include_original_in_replies" id="include_original_in_replies" value="1" {{ $preferences->include_original_in_replies ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="include_original_in_replies" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Include Original Email in Replies</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="mark_as_read_on_open" id="mark_as_read_on_open" value="1" {{ $preferences->mark_as_read_on_open ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="mark_as_read_on_open" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Mark as Read When Opening Ticket</label>
                </div>
            </div>
        </div>
    </div>
</div> 