<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Email Signatures') }}
            </h2>
            <a href="{{ route('email-accounts.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Accounts
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($emailAccounts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($emailAccounts as $emailAccount)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <!-- Account Info -->
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                        {{ $emailAccount->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $emailAccount->email }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        {{ ucfirst($emailAccount->type) }} Account
                                    </p>
                                </div>

                                <!-- Signature Status -->
                                <div class="mb-4">
                                    @if($emailAccount->hasSignature())
                                        <div class="flex items-center mb-3">
                                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-green-600 dark:text-green-400">Signature Configured</span>
                                        </div>
                                        
                                        <!-- Signature Preview -->
                                        <div class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-700 mb-4">
                                            @if($emailAccount->signature_text)
                                                <div class="text-sm text-gray-900 dark:text-white mb-2">
                                                    {!! Str::limit(strip_tags($emailAccount->signature_text), 100) !!}
                                                    @if(strlen(strip_tags($emailAccount->signature_text)) > 100)
                                                        <span class="text-gray-500">...</span>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            @if($emailAccount->signature_image_path)
                                                <div class="flex items-center">
                                                    <svg class="h-4 w-4 text-blue-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="text-xs text-blue-600 dark:text-blue-400">
                                                        {{ basename($emailAccount->signature_image_path) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex items-center mb-3">
                                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">No Signature</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Stats -->
                                <div class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex justify-between">
                                        <span>Total Tickets: {{ $emailAccount->tickets->count() }}</span>
                                        <span>Open: {{ $emailAccount->tickets->where('status', 'open')->count() }}</span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    @if($emailAccount->hasSignature())
                                        <a href="{{ route('email-accounts.signature.edit', $emailAccount) }}" 
                                           class="flex-1 bg-blue-500 hover:bg-blue-700 text-white text-center text-sm font-bold py-2 px-3 rounded">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('email-accounts.signature.destroy', $emailAccount) }}" 
                                              class="flex-1" 
                                              onsubmit="return confirm('Are you sure you want to delete this signature?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="w-full bg-red-500 hover:bg-red-700 text-white text-sm font-bold py-2 px-3 rounded">
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('email-accounts.signature.edit', $emailAccount) }}" 
                                           class="w-full bg-green-500 hover:bg-green-700 text-white text-center text-sm font-bold py-2 px-3 rounded">
                                            Add Signature
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No email accounts found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            You need to create email accounts before you can manage signatures.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('email-accounts.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Create Email Account
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 