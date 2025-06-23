<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Ticket') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('tickets.show', $ticket) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Ticket
                </a>
                <a href="{{ route('tickets.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    All Tickets
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Subject -->
                            <div class="md:col-span-2">
                                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                                <input type="text" id="subject" name="subject" value="{{ old('subject', $ticket->subject) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- From Email -->
                            <div>
                                <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Email</label>
                                <input type="email" id="from_email" name="from_email" value="{{ old('from_email', $ticket->from_email) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                @error('from_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- From Name -->
                            <div>
                                <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Name</label>
                                <input type="text" id="from_name" name="from_name" value="{{ old('from_name', $ticket->from_name) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('from_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select id="status" name="status" 
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="new" {{ $ticket->status === 'new' ? 'selected' : '' }}>New</option>
                                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="waiting" {{ $ticket->status === 'waiting' ? 'selected' : '' }}>Waiting</option>
                                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                <select id="priority" name="priority" 
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Received At -->
                            <div>
                                <label for="received_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Received At</label>
                                <input type="datetime-local" id="received_at" name="received_at" 
                                       value="{{ old('received_at', $ticket->received_at ? $ticket->received_at->format('Y-m-d\TH:i') : '') }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('received_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email Account -->
                            <div>
                                <label for="email_account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Account</label>
                                <select id="email_account_id" name="email_account_id" 
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($emailAccounts as $account)
                                        <option value="{{ $account->id }}" {{ $ticket->email_account_id === $account->id ? 'selected' : '' }}>
                                            {{ $account->email }} ({{ $account->type }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('email_account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Original Content -->
                            <div class="md:col-span-2">
                                <label for="original_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Original Content (Plain Text)</label>
                                <textarea id="original_content" name="original_content" rows="8" 
                                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('original_content', $ticket->original_content) }}</textarea>
                                @error('original_content')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- HTML Content -->
                            <div class="md:col-span-2">
                                <label for="html_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">HTML Content</label>
                                <textarea id="html_content" name="html_content" rows="8" 
                                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ old('html_content', $ticket->html_content) }}</textarea>
                                @error('html_content')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- To Email -->
                            <div>
                                <label for="to_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Email</label>
                                <input type="email" id="to_email" name="to_email" value="{{ old('to_email', $ticket->to_email) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('to_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- To Emails (JSON) -->
                            <div>
                                <label for="to_emails" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Emails (JSON)</label>
                                <input type="text" id="to_emails" name="to_emails" 
                                       value="{{ old('to_emails', is_array($ticket->to_emails) ? json_encode($ticket->to_emails) : $ticket->to_emails) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder='["email1@example.com", "email2@example.com"]'>
                                @error('to_emails')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- CC Emails (JSON) -->
                            <div>
                                <label for="cc_emails" class="block text-sm font-medium text-gray-700 dark:text-gray-300">CC Emails (JSON)</label>
                                <input type="text" id="cc_emails" name="cc_emails" 
                                       value="{{ old('cc_emails', is_array($ticket->cc_emails) ? json_encode($ticket->cc_emails) : $ticket->cc_emails) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder='["email1@example.com", "email2@example.com"]'>
                                @error('cc_emails')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- BCC Emails (JSON) -->
                            <div>
                                <label for="bcc_emails" class="block text-sm font-medium text-gray-700 dark:text-gray-300">BCC Emails (JSON)</label>
                                <input type="text" id="bcc_emails" name="bcc_emails" 
                                       value="{{ old('bcc_emails', is_array($ticket->bcc_emails) ? json_encode($ticket->bcc_emails) : $ticket->bcc_emails) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder='["email1@example.com", "email2@example.com"]'>
                                @error('bcc_emails')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Message ID -->
                            <div>
                                <label for="message_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message ID</label>
                                <input type="text" id="message_id" name="message_id" value="{{ old('message_id', $ticket->message_id) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('message_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Thread ID -->
                            <div>
                                <label for="thread_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thread ID</label>
                                <input type="text" id="thread_id" name="thread_id" value="{{ old('thread_id', $ticket->thread_id) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('thread_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Attachments (JSON) -->
                            <div class="md:col-span-2">
                                <label for="attachment_metadata" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attachments (JSON)</label>
                                <input type="text" id="attachment_metadata" name="attachment_metadata" 
                                       value="{{ old('attachment_metadata', is_array($ticket->attachment_metadata) ? json_encode($ticket->attachment_metadata) : $ticket->attachment_metadata) }}" 
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder='["file1.pdf", "file2.jpg"]'>
                                @error('attachment_metadata')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <a href="{{ route('tickets.show', $ticket) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 