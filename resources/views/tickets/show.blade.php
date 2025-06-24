<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Ticket Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('tickets.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Tickets
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-6">
                <!-- Main Content -->
                <div class="flex-1">
                    <!-- Ticket Header -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2" id="ticket-subject">
                                        {{ $ticket->subject }}
                                    </h1>
                                    <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                        <span><strong>From:</strong> {{ $ticket->from_email }}</span>
                                        <span><strong>Received:</strong> {{ $ticket->received_at->format('M j, Y g:i A') }}</span>
                                        <span><strong>Status:</strong> 
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($ticket->status === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @elseif($ticket->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                        </span>
                                        <span><strong>Priority:</strong> 
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($ticket->priority === 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                                {{ ucfirst($ticket->priority) }}
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="editTicket()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Edit Ticket
                                    </button>
                                    <button id="reply-button" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Reply to Email
                                    </button>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editSubject()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Content -->
                    <div class="bg-white dark:bg-gray-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Email Content</h3>
                                @if($ticket->html_content)
                                <div class="flex space-x-2">

                                </div>
                                @endif
                            </div>
                            <div class="prose dark:prose-invert max-w-none">
                                @if($ticket->html_content)
                                <!-- When HTML content exists, show both formats with toggle -->
                                <div id="ticket-content-plain" class="text-gray-700 dark:text-gray-300 {{ $userPreferences->ticket_display_format === 'plain_text' ? '' : 'hidden' }}" style="white-space: pre-line; word-wrap: break-word;">
                                    {{ html_entity_decode(strip_tags($ticket->original_content), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
                                </div>
                                <div id="ticket-content-html" class="text-gray-700 dark:text-gray-300 {{ $userPreferences->ticket_display_format === 'html' ? '' : 'hidden' }}" style="word-wrap: break-word;">
                                    <div class="email-html-content" style="max-width: 100%; overflow-x: auto;">
                                        <style>
                                            .email-html-content img {
                                                max-width: 100%;
                                                height: auto;
                                            }
                                            .email-html-content * {
                                                max-width: 100% !important;
                                            }
                                            /* Block any remaining cid: URLs */
                                            .email-html-content img[src^="cid:"] {
                                                display: none !important;
                                            }
                                            .email-html-content *[style*="cid:"] {
                                                background-image: none !important;
                                            }
                                        </style>
                                        {!! $ticket->getSafeHtmlContent() !!}
                                    </div>
                                </div>
                                @else
                                <!-- If no HTML content, show plain text without toggle buttons -->
                                <div class="text-gray-700 dark:text-gray-300" style="white-space: pre-line; word-wrap: break-word;">
                                    {{ html_entity_decode(strip_tags($ticket->original_content), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Conversation Thread -->
                    @if($ticket->replies->count() > 0)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Conversation Thread</h3>
                            
                            <!-- Original Message -->
                            <div class="border-l-4 border-blue-500 pl-4 mb-6">
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->from_email }}</span>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">• {{ $ticket->received_at->format('M j, Y g:i A') }}</span>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Original Message</span>
                                    </div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        <div class="font-medium mb-1">Subject: {{ $ticket->subject }}</div>
                                        <div class="whitespace-pre-line">{{ html_entity_decode(strip_tags($ticket->original_content), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Replies -->
                            @foreach($ticket->replies->sortBy('sent_at') as $index => $reply)
                            <div class="ml-8 border-l-2 border-gray-200 dark:border-gray-700 pl-4 mb-4">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">• {{ $reply->sent_at->format('M j, Y g:i A') }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Reply #{{ $index + 1 }}</span>
                                            @if($reply->include_original)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Original Included
                                                </span>
                                            @endif
                                            @if($reply->reply_to_all)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                    Reply to All
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        <div class="font-medium mb-1">To: {{ $reply->to_email }}</div>
                                        @if($reply->cc_emails && count($reply->cc_emails) > 0)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">CC: {{ implode(', ', $reply->cc_emails) }}</div>
                                        @endif
                                        <div class="font-medium mb-1">Subject: {{ $reply->subject }}</div>
                                        <div class="whitespace-pre-line">{{ $reply->message }}</div>
                                        @if($reply->attachments && $reply->attachments->count() > 0)
                                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Attachments:</div>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($reply->attachments as $attachment)
                                                        <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-700 transition-colors" target="_blank">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                            </svg>
                                                            {{ $attachment->original_name }} ({{ $attachment->formatted_size }})
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Attachments -->
                    @if($ticket->attachments->count() > 0)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Attachments</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($ticket->attachments as $attachment)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <!-- File type icon -->
                                        <div class="flex-shrink-0">
                                            @if($attachment->isImage())
                                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            @elseif($attachment->isPdf())
                                                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $attachment->original_name }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $attachment->formatted_size }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <a href="{{ route('attachments.download', $attachment) }}" 
                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                               title="Download {{ $attachment->original_name }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Reply Form (Hidden by default) -->
                    <div id="reply-form" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reply to Email</h3>
                                <div class="flex items-center space-x-2 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Replying to:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $ticket->from_email }}</span>
                                    <span class="text-gray-400">|</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($ticket->status === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($ticket->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </div>
                            </div>
                            <form id="email-reply-form" enctype="multipart/form-data">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label for="reply-to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To:</label>
                                        <input type="email" id="reply-to" name="to" value="{{ $ticket->from_email }}" 
                                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                    </div>
                                    
                                    <div>
                                        <label for="reply-cc" class="block text-sm font-medium text-gray-700 dark:text-gray-300">CC:</label>
                                        <input type="text" id="reply-cc" name="cc" value="{{ is_array($ticket->to_emails) ? implode(', ', $ticket->to_emails) : '' }}" 
                                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="Separate multiple emails with commas">
                                    </div>
                                    
                                    <div>
                                        <label for="reply-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject:</label>
                                        <input type="text" id="reply-subject" name="subject" value="Re: {{ $ticket->subject }}" 
                                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                    </div>
                                    
                                    <div>
                                        <label for="reply-message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message:</label>
                                        
                                        <!-- AI Rewrite Button -->
                                        <div class="mb-2">
                                            @if($aiEnabled)
                                                <button type="button" id="ai-rewrite-reply-btn" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
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
                                        
                                        <textarea id="reply-message" name="message" rows="8" 
                                                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                                                  placeholder="Type your reply message here..."></textarea>
                                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            <span id="char-count">0</span> characters
                                        </div>
                                    </div>
                                    
                                    <!-- Reply Preview -->
                                    <div id="reply-preview" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview:</label>
                                        <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md">
                                            <div id="preview-content" class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300"></div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="reply-attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attachments:</label>
                                        <input type="file" id="reply-attachments" name="attachments[]" multiple 
                                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" id="reply-to-all" name="reply_to_all" checked 
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Reply to All</span>
                                        </label>
                                        
                                        <label class="flex items-center">
                                            <input type="checkbox" id="include-original" name="include_original" checked 
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include Original Message</span>
                                        </label>
                                    </div>
                                    
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" id="clear-draft-btn"
                                                class="bg-gray-400 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                                            Clear Draft
                                        </button>
                                        <button type="button" id="cancel-reply-btn"
                                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                            Cancel
                                        </button>
                                        <button type="submit" 
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            Send Reply
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Side Panel -->
                <div class="w-80">
                    <!-- Related Emails -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Emails from {{ $ticket->from_email }}</h3>
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($relatedTickets as $relatedTicket)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $relatedTicket->subject }}
                                        </h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $relatedTicket->received_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2" style="white-space: pre-wrap;">
                                        {!! html_entity_decode(strip_tags(\Illuminate\Support\Str::limit($relatedTicket->original_content, 100)), ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($relatedTicket->status === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif($relatedTicket->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($relatedTicket->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $relatedTicket->status)) }}
                                        </span>
                                        @if($relatedTicket->id !== $ticket->id)
                                        <a href="{{ route('tickets.show', $relatedTicket) }}" 
                                           class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                            View
                                        </a>
                                        @else
                                        <span class="text-xs text-gray-400">Current</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <button onclick="updateStatus('in_progress')" 
                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    📋 Mark as In Progress
                                </button>
                                <button onclick="updateStatus('resolved')" 
                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    ✅ Mark as Resolved
                                </button>
                                <button onclick="updateStatus('closed')" 
                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    🔒 Close Ticket
                                </button>
                                <button onclick="showNotesModal()" 
                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    📝 Add Note
                                </button>
                                <button onclick="createGoogleTask()" 
                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    📋 Create Google Task
                                </button>
                                <button onclick="deleteTicket()" 
                                        class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors">
                                    🗑️ Delete Ticket
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notes ({{ $ticket->notes->count() }})</h3>
                                <button onclick="showNotesModal()" 
                                        class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                    + Add Note
                                </button>
                            </div>
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @forelse($ticket->notes->sortByDesc('created_at') as $note)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                @if($note->type === 'internal') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($note->type === 'public') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ ucfirst($note->type) }}
                                            </span>
                                            @if($note->is_private)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    Private
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $note->created_at->diffForHumans() }}
                                            </span>
                                            <button onclick="editNote({{ $note->id }})" 
                                                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                                Edit
                                            </button>
                                            <button onclick="deleteNote({{ $note->id }})" 
                                                    class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                        {{ $note->content }}
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        By {{ $note->user->name }}
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No notes yet</p>
                                    <button onclick="showNotesModal()" 
                                            class="mt-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        Add your first note
                                    </button>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Modal -->
    <div id="notes-modal" class="fixed inset-0 p-4 bg-gray-600 bg-opacity-50 overflow-y-auto hidden z-50">
        <div class="relative top-4 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white ml-2" id="notes-modal-title">Add Note</h3>
                    <button onclick="hideNotesModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="notes-form">
                    @csrf
                    <input type="hidden" id="note-id" name="note_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="note-content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-2">Note Content:</label>
                            <textarea id="note-content" name="content" rows="6" 
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                                    placeholder="Enter your note here..." required></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="note-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-2">Type:</label>
                                <select id="note-type" name="type" 
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 ml-2 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="internal">Internal</option>
                                    <option value="public">Public</option>
                                    <option value="system">System</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center mt-6">
                                <label class="flex items-center">
                                    <input type="checkbox" id="note-private" name="is_private" 
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Private Note</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="hideNotesModal()" 
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Save Note
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 mb-4">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Delete Ticket</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Are you sure you want to delete this ticket? This action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-center space-x-3 mt-4">
                    <button onclick="hideDeleteModal()" 
                            class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                        Cancel
                    </button>
                    <button onclick="confirmDeleteTicket()" 
                            class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Rewrite Modal for Reply -->
    <div id="ai-rewrite-reply-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden z-50">
        <div class="mt-4 mr-4 ml-4 px-4 py-4 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 p-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        AI Rewritten Reply
                    </h3>
                    <button type="button" id="close-reply-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Original Reply:</label>
                    <div id="original-reply-message" class="p-4 bg-gray-100 dark:bg-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-300 max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-600"></div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">AI Rewritten Reply:</label>
                    <div id="rewritten-reply-message" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-md text-sm text-gray-700 dark:text-gray-300 max-h-64 overflow-y-auto"></div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" id="reject-reply-rewrite" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                        Reject
                    </button>
                    <button type="button" id="accept-reply-rewrite" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors">
                        Use This Version
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner for Reply AI -->
    <div id="loading-reply-spinner" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-500"></div>
            <p class="text-center mt-4 text-white font-medium">Rewriting reply with AI...</p>
        </div>
    </div>

    <!-- Create Task Modal -->
    <div id="create-task-modal" class="fixed inset-0 p-4 bg-gray-600 bg-opacity-50 overflow-y-auto hidden z-50">
        <div class="relative top-4 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white ml-2" id="create-task-modal-title">Add Task</h3>
                    <button onclick="hideCreateTaskModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="create-task-form" action="{{ route('tickets.create-task', $ticket) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="task-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-2">Title:</label>
                            <input type="text" id="task-title" name="title" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="task-notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-2">Notes:</label>
                            <textarea id="task-notes" name="notes" rows="4" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        
                        <div>
                            <label for="task-internal-note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-2">Internal Note:</label>
                            <textarea id="task-internal-note" name="internal_note" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Internal notes for your reference only"></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-2">Internal notes are for your reference only and won't be sent to Google</p>
                        </div>
                        
                        <div>
                            <label for="task-due-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-2">Due Date:</label>
                            <input type="date" id="task-due-date" name="due_date" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="task-priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-2">Priority (Internal):</label>
                            <select id="task-priority" name="priority" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-2">Priority is for internal organization only</p>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="hideCreateTaskModal()" 
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Task
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Global variables for reply form
        let replyMessageTextarea;
        let replyForm;

        // Global functions for quick actions - must be defined outside DOMContentLoaded
        function updateStatus(status) {
            console.log('updateStatus function called with status:', status);
            console.log('CSRF token:', '{{ csrf_token() }}');
            console.log('Route:', '{{ route("tickets.update", $ticket) }}');
            
            fetch('{{ route("tickets.update", $ticket) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.message) {
                    alert('✅ ' + data.message);
                    window.location.reload();
                } else {
                    console.error('No message in response:', data);
                    alert('Error: No success message received');
                }
            })
            .catch(error => {
                console.error('Error in updateStatus:', error);
                alert('Error updating status. Please try again.');
            });
        }

        function createGoogleTask() {
            console.log('createGoogleTask function called');
            // Show the modal
            const modal = document.getElementById('create-task-modal');
            console.log('Modal element:', modal);
            if (modal) {
                modal.classList.remove('hidden');
                console.log('Modal should now be visible');
                
                // Set default values
                const titleField = document.getElementById('task-title');
                const notesField = document.getElementById('task-notes');
                const dueDateField = document.getElementById('task-due-date');
                
                console.log('Form fields found:', {
                    title: !!titleField,
                    notes: !!notesField,
                    dueDate: !!dueDateField
                });
                
                if (titleField) titleField.value = {!! json_encode($ticket->subject) !!};
                if (notesField) notesField.value = {!! json_encode(\Illuminate\Support\Str::limit($ticket->original_content, 200)) !!};
                if (dueDateField) dueDateField.value = '';
            } else {
                console.error('Create task modal not found!');
                alert('Error: Create task modal not found');
            }
        }

        function deleteTicket() {
            console.log('deleteTicket function called');
            showDeleteModal();
        }

        function showDeleteModal() {
            console.log('showDeleteModal function called');
            const modal = document.getElementById('delete-modal');
            console.log('Delete modal element:', modal);
            if (modal) {
                modal.classList.remove('hidden');
                console.log('Delete modal should now be visible');
            } else {
                console.error('Delete modal not found!');
                alert('Error: Delete modal not found');
            }
        }

        function hideDeleteModal() {
            const modal = document.getElementById('delete-modal');
            modal.classList.add('hidden');
        }

        function confirmDeleteTicket() {
            hideDeleteModal();
            
            fetch('{{ route("tickets.destroy", $ticket) }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    successMessage.textContent = '✅ ' + data.message;
                    document.body.appendChild(successMessage);
                    
                    setTimeout(() => {
                        if (successMessage.parentNode) {
                            successMessage.parentNode.removeChild(successMessage);
                        }
                    }, 2000);
                    
                    // Redirect to tickets index page after successful deletion
                    setTimeout(() => {
                        window.location.href = '{{ route("tickets.index") }}';
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error deleting ticket:', error);
                alert('Error deleting ticket. Please try again.');
            });
        }

        function showNotesModal(noteId = null) {
            console.log('showNotesModal called with noteId:', noteId);
            const modal = document.getElementById('notes-modal');
            const form = document.getElementById('notes-form');
            const title = document.getElementById('notes-modal-title');
            const content = document.getElementById('note-content');
            const type = document.getElementById('note-type');
            const isPrivate = document.getElementById('note-private');
            const noteIdField = document.getElementById('note-id');
            
            console.log('Modal element:', modal);
            console.log('Form element:', form);
            
            if (!modal) {
                console.error('Notes modal not found!');
                return;
            }
            
            // Reset form
            if (form) {
                form.reset();
            }
            if (noteIdField) {
                noteIdField.value = '';
            }
            
            if (noteId) {
                // Edit mode
                if (title) title.textContent = 'Edit Note';
                // Load note data (you would need to implement this)
                loadNoteData(noteId);
            } else {
                // Add mode
                if (title) title.textContent = 'Add Note';
            }
            
            modal.classList.remove('hidden');
            console.log('Modal should now be visible');
            
            if (content) {
                content.focus();
            }
        }

        function hideNotesModal() {
            const modal = document.getElementById('notes-modal');
            modal.classList.add('hidden');
        }

        function hideCreateTaskModal() {
            const modal = document.getElementById('create-task-modal');
            modal.classList.add('hidden');
        }

        // Move showReplyForm to global scope
        function showReplyForm() {
            console.log('=== SHOW REPLY FORM START ===');
            console.log('showReplyForm function called');
            
            const replyFormContainer = document.getElementById('reply-form');
            console.log('Reply form container found:', !!replyFormContainer);
            
            // Try to get the textarea element
            const textareaElement = document.getElementById('reply-message');
            console.log('Textarea element found:', !!textareaElement);
            console.log('Textarea element:', textareaElement);
            
            // Set the global variable
            replyMessageTextarea = textareaElement;
            console.log('Global replyMessageTextarea set:', !!replyMessageTextarea);
            
            if (replyFormContainer) {
                replyFormContainer.classList.remove('hidden');
                console.log('Reply form container hidden class removed');
                
                // Auto-populate with a default reply template
                if (replyMessageTextarea && !replyMessageTextarea.value.trim()) {
                    console.log('Auto-populating default reply template');
                    const defaultReply = `Hi,\n\nThank you for your email. `;
                    replyMessageTextarea.value = defaultReply;
                    replyMessageTextarea.focus();
                    replyMessageTextarea.setSelectionRange(defaultReply.length, defaultReply.length);
                    
                    // Update preview if "Include Original Message" is checked
                    if (typeof updateReplyPreview === 'function') {
                        console.log('Calling updateReplyPreview function');
                        updateReplyPreview();
                    } else {
                        console.warn('updateReplyPreview function not found');
                    }
                } else if (replyMessageTextarea) {
                    console.log('Focusing on existing textarea content');
                    replyMessageTextarea.focus();
                } else {
                    console.error('replyMessageTextarea is null or undefined');
                }
                
                console.log('Reply form should now be visible');
            } else {
                console.error('Reply form container not found');
            }
            console.log('=== SHOW REPLY FORM END ===');
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== DOM CONTENT LOADED START ===');
            console.log('DOM loaded, JavaScript is working');
            
            // Initialize global variables
            replyMessageTextarea = document.getElementById('reply-message');
            console.log('Global replyMessageTextarea initialized:', !!replyMessageTextarea);
            console.log('replyMessageTextarea element:', replyMessageTextarea);
            
            replyForm = document.getElementById('email-reply-form');
            console.log('Global replyForm initialized:', !!replyForm);
            console.log('replyForm element:', replyForm);
            
            const replyButton = document.getElementById('reply-button');
            console.log('Reply button found:', !!replyButton);
            if (replyButton) {
                replyButton.addEventListener('click', showReplyForm);
                console.log('Reply button click event listener added');
            }

            // Add event listener for "Include Original Message" checkbox
            const includeOriginalCheckbox = document.getElementById('include-original');
            console.log('Include original checkbox found:', !!includeOriginalCheckbox);
            if (includeOriginalCheckbox) {
                includeOriginalCheckbox.addEventListener('change', updateReplyPreview);
                console.log('Include original checkbox change event listener added');
            }

            // Add event listener for reply message textarea
            if (replyMessageTextarea) {
                replyMessageTextarea.addEventListener('input', updateReplyPreview);
                replyMessageTextarea.addEventListener('input', saveDraftReply);
                replyMessageTextarea.addEventListener('input', updateCharCount);
                console.log('Reply textarea event listeners added');
                
                // Load draft reply if exists
                loadDraftReply();
                // Update character count
                updateCharCount();
            } else {
                console.error('replyMessageTextarea is null, cannot add event listeners');
            }

            // Add event listener for reply form submission
            if (replyForm) {
                replyForm.addEventListener('submit', handleReplySubmit);
                console.log('Reply form submit event listener added');
            } else {
                console.error('replyForm is null, cannot add submit event listener');
            }

            // Add event listeners for notes form
            const notesForm = document.getElementById('notes-form');
            if (notesForm) {
                notesForm.addEventListener('submit', handleNotesSubmit);
            }

            // Add event listeners for notes modal
            const notesModal = document.getElementById('notes-modal');
            if (notesModal) {
                // Close modal when clicking outside
                notesModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideNotesModal();
                    }
                });
            }

            // Add event listeners for delete modal
            const deleteModal = document.getElementById('delete-modal');
            if (deleteModal) {
                // Close modal when clicking outside
                deleteModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideDeleteModal();
                    }
                });
            }

            // Close modals with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (!notesModal.classList.contains('hidden')) {
                        hideNotesModal();
                    }
                    if (!deleteModal.classList.contains('hidden')) {
                        hideDeleteModal();
                    }
                }
            });

            // Email content view toggle
            const viewPlainTextBtn = document.getElementById('view-plain-text');
            const viewHtmlBtn = document.getElementById('view-html');
            const ticketContentPlain = document.getElementById('ticket-content-plain');
            const ticketContentHtml = document.getElementById('ticket-content-html');

            if (viewPlainTextBtn && viewHtmlBtn) {
                viewPlainTextBtn.addEventListener('click', function() {
                    ticketContentPlain.classList.remove('hidden');
                    ticketContentHtml.classList.add('hidden');
                    viewPlainTextBtn.classList.remove('bg-gray-300', 'text-gray-700');
                    viewPlainTextBtn.classList.add('bg-blue-500', 'text-white');
                    viewHtmlBtn.classList.remove('bg-blue-500', 'text-white');
                    viewHtmlBtn.classList.add('bg-gray-300', 'text-gray-700');
                });

                viewHtmlBtn.addEventListener('click', function() {
                    ticketContentPlain.classList.add('hidden');
                    ticketContentHtml.classList.remove('hidden');
                    viewHtmlBtn.classList.remove('bg-gray-300', 'text-gray-700');
                    viewHtmlBtn.classList.add('bg-blue-500', 'text-white');
                    viewPlainTextBtn.classList.remove('bg-blue-500', 'text-white');
                    viewPlainTextBtn.classList.add('bg-gray-300', 'text-gray-700');
                });
            }

            // AI Rewrite functionality for reply form
            const aiRewriteReplyBtn = document.getElementById('ai-rewrite-reply-btn');
            const replyModal = document.getElementById('ai-rewrite-reply-modal');
            const closeReplyModal = document.getElementById('close-reply-modal');
            const acceptReplyRewrite = document.getElementById('accept-reply-rewrite');
            const rejectReplyRewrite = document.getElementById('reject-reply-rewrite');
            const loadingReplySpinner = document.getElementById('loading-reply-spinner');
            const originalReplyMessageDiv = document.getElementById('original-reply-message');
            const rewrittenReplyMessageDiv = document.getElementById('rewritten-reply-message');

            // Only initialize AI functionality if the button exists and is enabled
            if (aiRewriteReplyBtn && !aiRewriteReplyBtn.disabled) {
                // Show loading spinner
                function showReplyLoading() {
                    loadingReplySpinner.classList.remove('hidden');
                }

                // Hide loading spinner
                function hideReplyLoading() {
                    loadingReplySpinner.classList.add('hidden');
                }

                // Show modal
                function showReplyModal() {
                    replyModal.classList.remove('hidden');
                }

                // Hide modal
                function hideReplyModal() {
                    replyModal.classList.add('hidden');
                }

                // AI Rewrite button click
                aiRewriteReplyBtn.addEventListener('click', function() {
                    const replyMessageTextarea = document.getElementById('reply-message');
                    const message = replyMessageTextarea.value.trim();
                    const ticketSubject = '{{ $ticket->subject }}'; // Use ticket subject as context

                    if (!message) {
                        alert('Please enter a reply message to rewrite.');
                        return;
                    }

                    // Disable button during processing
                    aiRewriteReplyBtn.disabled = true;
                    aiRewriteReplyBtn.textContent = 'Processing...';
                    aiRewriteReplyBtn.classList.add('opacity-50', 'cursor-not-allowed');

                    showReplyLoading();

                    // Store original message
                    originalReplyMessageDiv.textContent = message;

                    // Make API call to rewrite message
                    fetch('{{ route("tickets.rewrite-message") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            message: message,
                            subject: ticketSubject
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { 
                                console.error('Server validation errors:', err);
                                // Handle Laravel validation errors
                                if (err.errors) {
                                    const errorMessages = Object.values(err.errors).flat().join('\n');
                                    throw new Error('Validation errors:\n' + errorMessages);
                                }
                                throw new Error(err.message || 'Validation failed');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        hideReplyLoading();
                        // Re-enable button
                        aiRewriteReplyBtn.disabled = false;
                        aiRewriteReplyBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>Rewrite with AI';
                        aiRewriteReplyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        
                        if (data.rewritten_content) {
                            rewrittenReplyMessageDiv.textContent = data.rewritten_content;
                            showReplyModal();
                        } else {
                            alert('Failed to rewrite message: ' + (data.message || 'No content returned'));
                        }
                    })
                    .catch(error => {
                        hideReplyLoading();
                        // Re-enable button
                        aiRewriteReplyBtn.disabled = false;
                        aiRewriteReplyBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>Rewrite with AI';
                        aiRewriteReplyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        
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
                closeReplyModal.addEventListener('click', hideReplyModal);
                rejectReplyRewrite.addEventListener('click', hideReplyModal);

                // Accept rewritten message
                acceptReplyRewrite.addEventListener('click', function() {
                    const replyMessageTextarea = document.getElementById('reply-message');
                    const rewrittenContent = rewrittenReplyMessageDiv.textContent;
                    replyMessageTextarea.value = rewrittenContent;
                    hideReplyModal();
                    // Update character count after setting the value
                    if (typeof updateCharCount === 'function') {
                        updateCharCount();
                    }
                });

                // Close modal when clicking outside
                replyModal.addEventListener('click', function(e) {
                    if (e.target === replyModal) {
                        hideReplyModal();
                    }
                });

                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !replyModal.classList.contains('hidden')) {
                        hideReplyModal();
                    }
                });
            }

            // Close modal when clicking outside
            const createTaskModal = document.getElementById('create-task-modal');
            if (createTaskModal) {
                createTaskModal.addEventListener('click', function(e) {
                    if (e.target === createTaskModal) {
                        hideCreateTaskModal();
                    }
                });
            }

            // Add event listener for create task form
            const createTaskForm = document.getElementById('create-task-form');
            if (createTaskForm) {
                createTaskForm.addEventListener('submit', handleCreateTaskSubmit);
            }

            // Add event listener for reply form buttons
            const clearDraftBtn = document.getElementById('clear-draft-btn');
            const cancelReplyBtn = document.getElementById('cancel-reply-btn');
            
            console.log('Clear draft button found:', !!clearDraftBtn);
            console.log('Cancel reply button found:', !!cancelReplyBtn);
            
            if (clearDraftBtn) {
                clearDraftBtn.addEventListener('click', clearDraftReply);
                console.log('Clear draft button event listener added');
            }
            
            if (cancelReplyBtn) {
                cancelReplyBtn.addEventListener('click', hideReplyForm);
                console.log('Cancel reply button event listener added');
            }
            
            console.log('=== DOM CONTENT LOADED END ===');
        });

        function hideReplyForm() {
            console.log('hideReplyForm function called');
            const replyFormContainer = document.getElementById('reply-form');
            if (replyFormContainer) {
                replyFormContainer.classList.add('hidden');
                // Clear draft reply when hiding the form
                localStorage.removeItem('draftReply');
            }
        }

        function handleReplySubmit(e) {
            e.preventDefault();
            console.log('=== REPLY FORM SUBMISSION START ===');
            console.log('Reply form submitted');

            const form = e.target;
            const formData = new FormData(form);

            // Log initial form data
            console.log('Initial FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            // Ensure CSRF token is included in FormData
            formData.append('_token', '{{ csrf_token() }}');
            console.log('CSRF token added:', '{{ csrf_token() }}');

            // Properly handle checkbox values - they need to be explicitly set as boolean strings
            const includeOriginal = document.getElementById('include-original').checked;
            const replyToAll = document.getElementById('reply-to-all').checked;
            
            console.log('Checkbox values before processing:');
            console.log('include_original checked:', includeOriginal);
            console.log('reply_to_all checked:', replyToAll);
            
            // Use set() to replace existing values instead of delete/append
            formData.set('include_original', includeOriginal ? '1' : '0');
            formData.set('reply_to_all', replyToAll ? '1' : '0');
            
            console.log('Checkbox values after processing:');
            console.log('include_original in FormData:', formData.get('include_original'));
            console.log('reply_to_all in FormData:', formData.get('reply_to_all'));
            
            // Double-check that the values are correct
            const finalIncludeOriginal = formData.get('include_original');
            const finalReplyToAll = formData.get('reply_to_all');
            
            console.log('Final checkbox values verification:');
            console.log('include_original final value:', finalIncludeOriginal, 'type:', typeof finalIncludeOriginal);
            console.log('reply_to_all final value:', finalReplyToAll, 'type:', typeof finalReplyToAll);
            
            // Validate that the values are correct
            if (finalIncludeOriginal !== '0' && finalIncludeOriginal !== '1') {
                console.error('Invalid include_original value:', finalIncludeOriginal);
                alert('Error: Invalid checkbox value. Please try again.');
                return;
            }
            
            if (finalReplyToAll !== '0' && finalReplyToAll !== '1') {
                console.error('Invalid reply_to_all value:', finalReplyToAll);
                alert('Error: Invalid checkbox value. Please try again.');
                return;
            }

            // Client-side validation
            const message = formData.get('message');
            const to = formData.get('to');
            const replySubject = formData.get('subject');
            
            console.log('Validation values:');
            console.log('message:', message);
            console.log('to:', to);
            console.log('subject:', replySubject);
            
            if (!message || !message.trim()) {
                console.error('Validation failed: Empty message');
                alert('Please enter a reply message.');
                document.getElementById('reply-message').focus();
                return;
            }
            
            if (!to || !to.trim()) {
                console.error('Validation failed: Empty recipient');
                alert('Please enter a recipient email address.');
                document.getElementById('reply-to').focus();
                return;
            }
            
            if (!replySubject || !replySubject.trim()) {
                console.error('Validation failed: Empty subject');
                alert('Please enter a subject.');
                document.getElementById('reply-subject').focus();
                return;
            }

            // Handle "Include Original Message" checkbox
            const originalMessage = document.getElementById('reply-message').value;
            console.log('Original message from textarea:', originalMessage);
            
            if (includeOriginal) {
                const originalContent = `{{ $ticket->original_content }}`;
                console.log('Original ticket content:', originalContent);
                const formattedOriginal = `\n\n--- Original Message ---\nFrom: {{ $ticket->from_email }}\nDate: {{ $ticket->received_at->format('M j, Y g:i A') }}\nSubject: {{ $ticket->subject }}\n\n${originalContent}`;
                console.log('Formatted original content:', formattedOriginal);
                formData.set('message', originalMessage + formattedOriginal);
                console.log('Final message with original included:', formData.get('message'));
            }

            // Final check to ensure message is not empty after processing
            const finalMessage = formData.get('message');
            console.log('Final message length:', finalMessage ? finalMessage.length : 0);
            if (!finalMessage || !finalMessage.trim()) {
                console.error('Validation failed: Empty final message');
                alert('Please enter a reply message.');
                document.getElementById('reply-message').focus();
                return;
            }

            // Handle "Reply to All" checkbox
            if (replyToAll) {
                const toEmails = {!! json_encode($ticket->to_emails ?? []) !!};
                const currentTo = formData.get('to');
                const currentCc = formData.get('cc') || '';
                
                console.log('Reply to All processing:');
                console.log('Original to_emails:', toEmails);
                console.log('Current TO:', currentTo);
                console.log('Current CC:', currentCc);
                
                // Add original sender to CC if not already in TO
                if (currentTo !== '{{ $ticket->from_email }}') {
                    const newCc = currentCc ? `${currentCc}, {{ $ticket->from_email }}` : '{{ $ticket->from_email }}';
                    formData.set('cc', newCc);
                    console.log('Added sender to CC:', newCc);
                }
                
                // Add original recipients to CC
                if (toEmails && toEmails.length > 0) {
                    const recipientsToAdd = toEmails.filter(email => 
                        email !== '{{ $ticket->from_email }}' && 
                        email !== currentTo && 
                        !currentCc.includes(email)
                    );
                    
                    console.log('Recipients to add:', recipientsToAdd);
                    
                    if (recipientsToAdd.length > 0) {
                        const existingCc = formData.get('cc') || '';
                        const newCc = existingCc ? `${existingCc}, ${recipientsToAdd.join(', ')}` : recipientsToAdd.join(', ');
                        formData.set('cc', newCc);
                        console.log('Final CC after adding recipients:', newCc);
                    }
                }
            }

            // Show confirmation dialog
            const toEmail = formData.get('to');
            const ccEmails = formData.get('cc') || '';
            const confirmSubject = formData.get('subject');
            const confirmMessage = formData.get('message');
            
            const recipientCount = 1 + (ccEmails ? ccEmails.split(',').length : 0);
            const messageLength = confirmMessage.length;
            
            console.log('Confirmation dialog data:');
            console.log('To:', toEmail);
            console.log('CC:', ccEmails);
            console.log('Subject:', confirmSubject);
            console.log('Recipient count:', recipientCount);
            console.log('Message length:', messageLength);
            
            let confirmMessageText = `Are you sure you want to send this reply?\n\n`;
            confirmMessageText += `To: ${toEmail}\n`;
            if (ccEmails) {
                confirmMessageText += `CC: ${ccEmails}\n`;
            }
            confirmMessageText += `Subject: ${confirmSubject}\n`;
            confirmMessageText += `Recipients: ${recipientCount}\n`;
            confirmMessageText += `Message length: ${messageLength} characters\n\n`;
            confirmMessageText += `This action cannot be undone.`;
            
            if (!confirm(confirmMessageText)) {
                console.log('User cancelled reply submission');
                return;
            }

            // Log final form data for debugging
            console.log('Final FormData contents before submission:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            // Additional debugging
            console.log('Form validation passed');
            console.log('Message length:', confirmMessage.length);
            console.log('To email:', toEmail);
            console.log('Subject:', confirmSubject);
            console.log('CSRF Token:', '{{ csrf_token() }}');
            console.log('Request URL:', '{{ route("tickets.reply", $ticket) }}');

            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Sending...';
            submitButton.disabled = true;

            console.log('Making fetch request to:', '{{ route("tickets.reply", $ticket) }}');
            
            fetch('{{ route("tickets.reply", $ticket) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    console.error('Response not OK, status:', response.status);
                    return response.json().then(err => { 
                        console.error('Server validation errors:', err);
                        console.error('Error details:', JSON.stringify(err, null, 2));
                        throw err; 
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Reply response success:', data);
                if (data.message) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    successMessage.textContent = '✅ ' + data.message;
                    document.body.appendChild(successMessage);
                    
                    // Remove success message after 3 seconds
                    setTimeout(() => {
                        if (successMessage.parentNode) {
                            successMessage.parentNode.removeChild(successMessage);
                        }
                    }, 3000);
                    
                    hideReplyForm();
                    form.reset();
                    
                    // Clear draft reply
                    localStorage.removeItem('draftReply');
                    
                    // Update ticket status to "In Progress" if it was "New"
                    if ('{{ $ticket->status }}' === 'new') {
                        updateStatus('in_progress');
                    } else {
                        // Reload the page to show updated ticket status
                        window.location.reload();
                    }
                } else {
                    console.error('No message in success response');
                    alert('An unknown error occurred.');
                }
            })
            .catch(error => {
                console.error('=== REPLY FORM SUBMISSION ERROR ===');
                console.error('Error sending reply:', error);
                console.error('Error type:', typeof error);
                console.error('Error constructor:', error.constructor.name);
                console.error('Error stack:', error.stack);
                
                let errorMessage = 'Error sending reply. Please try again.';
                
                if (error.message) {
                    console.error('Error message:', error.message);
                    errorMessage += `\n\nDetails: ${error.message}`;
                }
                
                if (error.errors) {
                    console.error('Validation errors object:', error.errors);
                    console.error('Validation errors keys:', Object.keys(error.errors));
                    const validationErrors = Object.values(error.errors).flat().join('\n');
                    console.error('Flattened validation errors:', validationErrors);
                    errorMessage += `\n\nValidation errors:\n${validationErrors}`;
                }
                
                if (error.response) {
                    console.error('Error response:', error.response);
                }
                
                // Show error message
                const errorToast = document.createElement('div');
                errorToast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md';
                errorToast.innerHTML = `
                    <div class="flex items-center">
                        <span class="mr-2">❌</span>
                        <span>${errorMessage}</span>
                    </div>
                `;
                document.body.appendChild(errorToast);
                
                // Remove error message after 5 seconds
                setTimeout(() => {
                    if (errorToast.parentNode) {
                        errorToast.parentNode.removeChild(errorToast);
                    }
                }, 5000);
            })
            .finally(() => {
                console.log('=== REPLY FORM SUBMISSION END ===');
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        }

        function editTicket() {
            // Make subject editable
            const subjectElement = document.getElementById('ticket-subject');
            const currentSubject = subjectElement.textContent;
            
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentSubject;
            input.className = 'text-2xl font-bold text-gray-900 dark:text-white mb-2 bg-transparent border-b-2 border-blue-500 focus:outline-none focus:border-blue-700';
            
            input.addEventListener('blur', function() {
                saveTicketEdit();
            });
            
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    saveTicketEdit();
                }
            });
            
            subjectElement.parentNode.replaceChild(input, subjectElement);
            input.focus();
        }

        function saveTicketEdit() {
            const input = document.querySelector('input[type="text"]');
            const newSubject = input.value;
            
            fetch('{{ route("tickets.update", $ticket) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    subject: newSubject
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.message) {
                    // Replace input with new subject element
                    const subjectElement = document.createElement('h1');
                    subjectElement.id = 'ticket-subject';
                    subjectElement.className = 'text-2xl font-bold text-gray-900 dark:text-white mb-2';
                    subjectElement.textContent = newSubject;
                    
                    input.parentNode.replaceChild(subjectElement, input);
                    
                    alert('✅ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error updating ticket:', error);
                alert('Error updating ticket: ' + error.message);
            });
        }

        function handleCreateTaskSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            
            // Ensure CSRF token is included in FormData
            formData.append('_token', '{{ csrf_token() }}');

            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Creating...';
            submitButton.disabled = true;
            
            fetch('{{ route("tickets.create-task", $ticket) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    successMessage.textContent = '✅ ' + data.message;
                    document.body.appendChild(successMessage);
                    
                    setTimeout(() => {
                        if (successMessage.parentNode) {
                            successMessage.parentNode.removeChild(successMessage);
                        }
                    }, 3000);
                    
                    hideCreateTaskModal();
                    form.reset();
                    
                    // Reload the page to show updated ticket
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error creating task:', error);
                alert('Error creating task. Please try again.');
            })
            .finally(() => {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        }

        // Global functions for reply form functionality
        function updateReplyPreview() {
            const includeOriginal = document.getElementById('include-original').checked;
            const replyMessage = document.getElementById('reply-message').value;
            const previewContainer = document.getElementById('reply-preview');
            const previewContent = document.getElementById('preview-content');
            
            if (includeOriginal && replyMessage.trim()) {
                const originalContent = `{{ $ticket->original_content }}`;
                const formattedOriginal = `\n\n--- Original Message ---\nFrom: {{ $ticket->from_email }}\nDate: {{ $ticket->received_at->format('M j, Y g:i A') }}\nSubject: {{ $ticket->subject }}\n\n${originalContent}`;
                
                previewContent.textContent = replyMessage + formattedOriginal;
                previewContainer.classList.remove('hidden');
            } else {
                previewContainer.classList.add('hidden');
            }
        }

        function saveDraftReply() {
            const replyMessage = document.getElementById('reply-message').value;
            localStorage.setItem('draftReply', replyMessage);
        }

        function loadDraftReply() {
            const draftReply = localStorage.getItem('draftReply');
            if (draftReply) {
                document.getElementById('reply-message').value = draftReply;
                updateReplyPreview();
            }
        }

        function clearDraftReply() {
            localStorage.removeItem('draftReply');
            document.getElementById('reply-message').value = '';
            updateReplyPreview();
        }

        function updateCharCount() {
            const replyMessage = document.getElementById('reply-message').value;
            const charCount = replyMessage.length;
            document.getElementById('char-count').textContent = charCount;
        }

        function loadNoteData(noteId) {
            // This would typically fetch note data from the server
            // For now, we'll implement a simple version
            console.log('Loading note data for ID:', noteId);
        }

        function editNote(noteId) {
            showNotesModal(noteId);
        }

        function deleteNote(noteId) {
            if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                fetch(`{{ route('tickets.notes.destroy', ['ticket' => $ticket->id, 'note' => 'NOTE_ID']) }}`.replace('NOTE_ID', noteId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                        successMessage.textContent = '✅ ' + data.message;
                        document.body.appendChild(successMessage);
                        
                        setTimeout(() => {
                            if (successMessage.parentNode) {
                                successMessage.parentNode.removeChild(successMessage);
                            }
                        }, 3000);
                        
                        // Reload the page to show updated notes
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error deleting note:', error);
                    alert('Error deleting note. Please try again.');
                });
            }
        }

        // Add event listeners for notes form
        function handleNotesSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const noteId = formData.get('note_id');
            
            // Ensure CSRF token is included in FormData
            formData.append('_token', '{{ csrf_token() }}');

            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Saving...';
            submitButton.disabled = true;
            
            const url = noteId 
                ? `{{ route('tickets.notes.update', ['ticket' => $ticket->id, 'note' => 'NOTE_ID']) }}`.replace('NOTE_ID', noteId)
                : '{{ route("tickets.notes.store", $ticket) }}';
            
            const method = noteId ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    successMessage.textContent = '✅ ' + data.message;
                    document.body.appendChild(successMessage);
                    
                    setTimeout(() => {
                        if (successMessage.parentNode) {
                            successMessage.parentNode.removeChild(successMessage);
                        }
                    }, 3000);
                    
                    hideNotesModal();
                    // Reload the page to show updated notes
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error saving note:', error);
                alert('Error saving note. Please try again.');
            })
            .finally(() => {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        }
    </script>
</x-app-layout> 