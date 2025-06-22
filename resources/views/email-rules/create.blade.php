<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Email Rule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('email-rules.store') }}" class="space-y-6">
                        @csrf

                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rule Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email_account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Account</label>
                                    <select name="email_account_id" id="email_account_id" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select an email account</option>
                                        @foreach($emailAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('email_account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('email_account_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Rule Action -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Rule Action</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 cursor-pointer transition-colors" onclick="selectAction('create_ticket')">
                                    <div class="flex items-center mb-3">
                                        <svg class="h-6 w-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <h4 class="text-md font-medium text-gray-900 dark:text-white">Create Ticket</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Automatically create a support ticket from matching emails.</p>
                                </div>

                                <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 cursor-pointer transition-colors" onclick="selectAction('auto_respond')">
                                    <div class="flex items-center mb-3">
                                        <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        <h4 class="text-md font-medium text-gray-900 dark:text-white">Auto Respond</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Send an automatic response to matching emails.</p>
                                </div>

                                <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 cursor-pointer transition-colors" onclick="selectAction('forward')">
                                    <div class="flex items-center mb-3">
                                        <svg class="h-6 w-6 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                        </svg>
                                        <h4 class="text-md font-medium text-gray-900 dark:text-white">Forward</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Forward matching emails to another address.</p>
                                </div>
                            </div>
                            <input type="hidden" name="action" id="action" value="{{ old('action') }}" required>
                            @error('action')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Configuration -->
                        <div id="action-config" class="hidden">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Action Configuration</h3>
                            
                            <!-- Auto Respond Configuration -->
                            <div id="auto-respond-config" class="hidden">
                                <div class="space-y-4">
                                    <div>
                                        <label for="response_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Response Subject</label>
                                        <input type="text" name="response_subject" id="response_subject" value="{{ old('response_subject') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="response_body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Response Body</label>
                                        <textarea name="response_body" id="response_body" rows="6" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('response_body') }}</textarea>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You can use variables like {{name}}, {{email}}, {{subject}} in your response.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Forward Configuration -->
                            <div id="forward-config" class="hidden">
                                <div>
                                    <label for="forward_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Forward To</label>
                                    <input type="email" name="forward_to" id="forward_to" value="{{ old('forward_to') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Email address to forward matching emails to.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Conditions -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Conditions</h3>
                            <div id="conditions-container" class="space-y-4">
                                <!-- Conditions will be added here dynamically -->
                            </div>
                            <button type="button" onclick="addCondition()" class="mt-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                                Add Condition
                            </button>
                        </div>

                        <!-- Rule Status -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Rule Status</h3>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Active Rule
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Inactive rules will not be processed.</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('email-rules.index') }}" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
                                Create Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedAction = null;
        let conditionCount = 0;

        function selectAction(action) {
            selectedAction = action;
            document.getElementById('action').value = action;
            
            // Reset all selections
            document.querySelectorAll('[onclick^="selectAction"]').forEach(el => {
                el.classList.remove('border-blue-500', 'dark:border-blue-400');
                el.classList.add('border-gray-200', 'dark:border-gray-700');
            });
            
            // Highlight selected
            event.currentTarget.classList.remove('border-gray-200', 'dark:border-gray-700');
            event.currentTarget.classList.add('border-blue-500', 'dark:border-blue-400');
            
            // Show action configuration
            document.getElementById('action-config').classList.remove('hidden');
            document.getElementById('auto-respond-config').classList.add('hidden');
            document.getElementById('forward-config').classList.add('hidden');
            
            if (action === 'auto_respond') {
                document.getElementById('auto-respond-config').classList.remove('hidden');
            } else if (action === 'forward') {
                document.getElementById('forward-config').classList.remove('hidden');
            }
        }

        function addCondition() {
            const container = document.getElementById('conditions-container');
            const conditionDiv = document.createElement('div');
            conditionDiv.className = 'grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg';
            conditionDiv.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Field</label>
                    <select name="conditions[${conditionCount}][field]" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="from">From</option>
                        <option value="to">To</option>
                        <option value="subject">Subject</option>
                        <option value="body">Body</option>
                        <option value="has_attachment">Has Attachment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Operator</label>
                    <select name="conditions[${conditionCount}][operator]" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="contains">Contains</option>
                        <option value="equals">Equals</option>
                        <option value="starts_with">Starts With</option>
                        <option value="ends_with">Ends With</option>
                        <option value="regex">Regex</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                    <input type="text" name="conditions[${conditionCount}][value]" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="removeCondition(this)" class="bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-300 font-medium py-2 px-4 rounded transition-colors">
                        Remove
                    </button>
                </div>
            `;
            container.appendChild(conditionDiv);
            conditionCount++;
        }

        function removeCondition(button) {
            button.closest('div').remove();
        }

        // Add initial condition
        document.addEventListener('DOMContentLoaded', function() {
            addCondition();
        });
    </script>
</x-app-layout> 