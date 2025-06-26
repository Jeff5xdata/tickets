<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Integration Settings</h3>
        
        <!-- Slack Integration -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Slack Integration</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="slack_integration" id="slack_integration" value="1" {{ $preferences->slack_integration ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="slack_integration" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Slack Integration</label>
                </div>
                <div>
                    <label for="slack_webhook_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slack Webhook URL</label>
                    <input type="url" name="slack_webhook_url" id="slack_webhook_url" value="{{ $preferences->slack_webhook_url }}" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://hooks.slack.com/services/...">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Webhook URL for sending notifications to Slack</p>
                </div>
            </div>
        </div>

        <!-- Webhook Integration -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Webhook Integration</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="webhook_notifications" id="webhook_notifications" value="1" {{ $preferences->webhook_notifications ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="webhook_notifications" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Webhook Notifications</label>
                </div>
                <div>
                    <label for="webhook_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Webhook URL</label>
                    <input type="url" name="webhook_url" id="webhook_url" value="{{ $preferences->webhook_url }}" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://your-webhook-endpoint.com/notifications">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">URL to send webhook notifications to</p>
                </div>
            </div>
        </div>

        <!-- Integration Information -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Integration Information</h4>
            <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Integration Setup Required</h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>To use integrations, you need to configure the webhook URLs and ensure your external services are properly set up to receive notifications.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 