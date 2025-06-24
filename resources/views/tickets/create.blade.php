<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Ticket') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- From Account -->
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">From Account</label>
                            <div class="space-y-2">
                                @forelse($emailAccounts as $account)
                                    <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors account-option">
                                        <input type="radio" name="from_account" value="{{ $account->id }}" class="mr-3 text-blue-600 focus:ring-blue-500" {{ old('from_account') == $account->id ? 'checked' : ($loop->first ? 'checked' : '') }} required>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $account->name }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $account->email }}</div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($account->type === 'gmail') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                        @elseif($account->type === 'outlook') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                        @elseif($account->type === 'google-tasks') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                                        @endif">
                                                        @if($account->type === 'gmail')
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                                            </svg>
                                                            Gmail
                                                        @elseif($account->type === 'outlook')
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Outlook
                                                        @elseif($account->type === 'google-tasks')
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Google Tasks
                                                        @else
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            IMAP
                                                        @endif
                                                    </span>
                                                    @if($account->provider)
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($account->provider) }}</span>
                                                    @endif
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                        @if($account->is_active) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                        @endif">
                                                        <div class="w-1.5 h-1.5 mr-1 rounded-full
                                                            @if($account->is_active) bg-green-400
                                                            @else bg-red-400
                                                            @endif"></div>
                                                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="p-4 text-center text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-md">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p>No active email accounts found</p>
                                        <p class="text-sm">You need an active Gmail, Outlook, or IMAP account to send emails</p>
                                        <a href="{{ route('email-accounts.create') }}" class="inline-block mt-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            Add Email Account
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                            @error('from_account')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- To -->
                        <div class="mb-4">
                            <label for="to" class="block font-medium text-sm text-gray-700 dark:text-gray-300">To</label>
                            <input type="email" id="to" name="to" list="recipient-emails" class="block mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm" required value="{{ old('to') }}">
                            <datalist id="recipient-emails">
                                @foreach($recipientEmails as $email)
                                    <option value="{{ $email }}">
                                @endforeach
                            </datalist>
                        </div>

                        <!-- CC -->
                        <div class="mb-4">
                            <label for="cc" class="block font-medium text-sm text-gray-700 dark:text-gray-300">CC</label>
                            <input type="text" id="cc" name="cc" class="block mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm" value="{{ old('cc') }}" placeholder="Comma-separated emails">
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <label for="subject" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Subject</label>
                            <input type="text" id="subject" name="subject" class="block mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm" required value="{{ old('subject') }}">
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <label for="message" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Message</label>
                            
                            <!-- AI Rewrite Button -->
                            <div class="mb-2">
                                @if($aiEnabled)
                                    <button type="button" id="ai-rewrite-btn" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        Rewrite with AI
                                    </button>
                                @else
                                    <button type="button" disabled class="bg-gray-400 cursor-not-allowed text-white font-bold py-2 px-4 rounded inline-flex items-center" title="AI rewriting is not available. Please configure your Gemini API key.">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        Rewrite with AI (Not Available)
                                    </button>
                                @endif
                            </div>
                            
                            <textarea id="message" name="message" rows="10" class="block mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm" required>{{ old('message') }}</textarea>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-4">
                            <label for="attachments" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Attachments</label>
                            <input type="file" id="attachments" name="attachments[]" multiple class="block mt-1 w-full text-gray-900 dark:text-gray-100">
                        </div>
                        
                        @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Rewrite Modal -->
    <div id="ai-rewrite-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden z-50">
        <div class="mt-4 mr-4 ml-4 px-4 py-4 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 p-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        AI Rewritten Message
                    </h3>
                    <button type="button" id="close-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Original Message:</label>
                    <div id="original-message" class="p-4 bg-gray-100 dark:bg-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-300 max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-600"></div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">AI Rewritten Message:</label>
                    <div id="rewritten-message" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-md text-sm text-gray-700 dark:text-gray-300 max-h-64 overflow-y-auto"></div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" id="reject-rewrite" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                        Reject
                    </button>
                    <button type="button" id="accept-rewrite" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors">
                        Use This Version
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-500"></div>
            <p class="text-center mt-4 text-white font-medium">Rewriting with AI...</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const aiRewriteBtn = document.getElementById('ai-rewrite-btn');
            const modal = document.getElementById('ai-rewrite-modal');
            const closeModal = document.getElementById('close-modal');
            const acceptRewrite = document.getElementById('accept-rewrite');
            const rejectRewrite = document.getElementById('reject-rewrite');
            const loadingSpinner = document.getElementById('loading-spinner');
            const originalMessageDiv = document.getElementById('original-message');
            const rewrittenMessageDiv = document.getElementById('rewritten-message');
            const messageTextarea = document.getElementById('message');
            const subjectInput = document.getElementById('subject');
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');

            // Add CSS for selected account styling
            const style = document.createElement('style');
            style.textContent = `
                .account-option input[type="radio"]:checked + div {
                    background-color: rgb(239 246 255);
                    border-color: rgb(59 130 246);
                }
                .dark .account-option input[type="radio"]:checked + div {
                    background-color: rgb(30 58 138);
                    border-color: rgb(59 130 246);
                }
                .account-option input[type="radio"]:checked ~ .account-option {
                    background-color: rgb(239 246 255);
                    border-color: rgb(59 130 246);
                }
                .dark .account-option input[type="radio"]:checked ~ .account-option {
                    background-color: rgb(30 58 138);
                    border-color: rgb(59 130 246);
                }
            `;
            document.head.appendChild(style);

            // Handle email account selection
            const accountRadios = document.querySelectorAll('input[name="from_account"]');
            accountRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Update visual selection
                    document.querySelectorAll('.account-option').forEach(option => {
                        option.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500');
                    });
                    this.closest('.account-option').classList.add('ring-2', 'ring-blue-500', 'border-blue-500');
                });
            });

            // Set initial selection
            const initialSelected = document.querySelector('input[name="from_account"]:checked');
            if (initialSelected) {
                initialSelected.dispatchEvent(new Event('change'));
            }

            // Only initialize AI functionality if the button exists and is enabled
            if (aiRewriteBtn && !aiRewriteBtn.disabled) {
                // Show loading spinner
                function showLoading() {
                    loadingSpinner.classList.remove('hidden');
                }

                // Hide loading spinner
                function hideLoading() {
                    loadingSpinner.classList.add('hidden');
                }

                // Show modal
                function showModal() {
                    modal.classList.remove('hidden');
                }

                // Hide modal
                function hideModal() {
                    modal.classList.add('hidden');
                }

                // AI Rewrite button click
                aiRewriteBtn.addEventListener('click', function() {
                    const message = messageTextarea.value.trim();
                    const subject = subjectInput.value.trim();

                    if (!message) {
                        alert('Please enter a message to rewrite.');
                        return;
                    }

                    if (!subject) {
                        alert('Please enter a subject for context.');
                        return;
                    }

                    // Disable button during processing
                    aiRewriteBtn.disabled = true;
                    aiRewriteBtn.textContent = 'Processing...';
                    aiRewriteBtn.classList.add('opacity-50', 'cursor-not-allowed');

                    showLoading();

                    // Store original message
                    originalMessageDiv.textContent = message;

                    // Make API call to rewrite message
                    fetch('{{ route("tickets.rewrite-message") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            message: message,
                            subject: subject
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        hideLoading();
                        // Re-enable button
                        aiRewriteBtn.disabled = false;
                        aiRewriteBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>Rewrite with AI';
                        aiRewriteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        
                        if (data.rewritten_content) {
                            rewrittenMessageDiv.textContent = data.rewritten_content;
                            showModal();
                        } else {
                            alert('Failed to rewrite message: ' + (data.message || 'No content returned'));
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        // Re-enable button
                        aiRewriteBtn.disabled = false;
                        aiRewriteBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>Rewrite with AI';
                        aiRewriteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        
                        console.error('Error:', error);
                        if (error.message.includes('500')) {
                            alert('AI service is currently unavailable. Please try again later or check your API configuration.');
                        } else if (error.message.includes('overloaded') || error.message.includes('503')) {
                            alert('AI service is currently overloaded. Please try again in a few minutes.');
                        } else {
                            alert('Failed to rewrite message. Please try again.');
                        }
                    });
                });

                // Close modal
                closeModal.addEventListener('click', hideModal);
                rejectRewrite.addEventListener('click', hideModal);

                // Accept rewritten message
                acceptRewrite.addEventListener('click', function() {
                    const rewrittenContent = rewrittenMessageDiv.textContent;
                    messageTextarea.value = rewrittenContent;
                    hideModal();
                });

                // Close modal when clicking outside
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        hideModal();
                    }
                });

                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        hideModal();
                    }
                });
            }
        });
    </script>
</x-app-layout> 