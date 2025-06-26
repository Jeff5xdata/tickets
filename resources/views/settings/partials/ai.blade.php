<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">AI Settings</h3>
        
        <!-- AI Features -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">AI Features</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="ai_rewriting_enabled" id="ai_rewriting_enabled" value="1" {{ $preferences->ai_rewriting_enabled ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="ai_rewriting_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable AI Email Rewriting</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="ai_summarization_enabled" id="ai_summarization_enabled" value="1" {{ $preferences->ai_summarization_enabled ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="ai_summarization_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable AI Email Summarization</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="ai_response_generation" id="ai_response_generation" value="1" {{ $preferences->ai_response_generation ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="ai_response_generation" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable AI Response Generation</label>
                </div>
            </div>
        </div>

        <!-- AI Tone Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">AI Tone Settings</h4>
            <div>
                <label for="ai_tone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default AI Tone</label>
                <select name="ai_tone" id="ai_tone" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach($aiTones as $value => $label)
                        <option value="{{ $value }}" {{ $preferences->ai_tone === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default tone for AI-generated content</p>
            </div>
        </div>

        <!-- AI Usage Information -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">AI Usage Information</h4>
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">AI Features Powered by Google Gemini</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>AI features use Google's Gemini API to provide intelligent email assistance. Make sure you have configured your Gemini API key in the environment settings.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 