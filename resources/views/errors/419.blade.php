<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        Page Expired
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Your session has expired. Please refresh the page and try again.
                    </p>
                    <div class="flex flex-col space-y-3">
                        <button onclick="window.location.reload()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Refresh Page
                        </button>
                        <a href="{{ route('login') }}" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline text-center">
                            Go to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Auto-refresh CSRF token and retry
            document.addEventListener('DOMContentLoaded', function() {
                // Try to refresh the CSRF token
                fetch('/csrf-token', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.token) {
                        // Update the meta tag
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                        // Update Livewire's CSRF token if available
                        if (window.Livewire) {
                            window.Livewire.csrfToken = data.token;
                        }
                    }
                })
                .catch(error => {
                    console.log('CSRF token refresh failed:', error);
                });
            });
        </script>
    </body>
</html> 