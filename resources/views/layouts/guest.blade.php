<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#6366f1">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Laravel') }}">
        <meta name="msapplication-TileColor" content="#6366f1">
        <meta name="msapplication-tap-highlight" content="no">

        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.json">

        <!-- Apple Touch Icons -->
        <link rel="apple-touch-icon" href="/images/icon-152x152.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/images/icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/icon-180x180.png">
        <link rel="apple-touch-icon" sizes="167x167" href="/images/icon-167x167.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        <!-- CSRF Token Refresh Script -->
        <script>
            // Refresh CSRF token on page load to prevent expired token issues
            document.addEventListener('DOMContentLoaded', function() {
                // Check if we're on a login page
                if (window.location.pathname === '/login') {
                    // Fetch a fresh CSRF token
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
                            // Update Livewire's CSRF token
                            if (window.Livewire) {
                                window.Livewire.csrfToken = data.token;
                            }
                        }
                    })
                    .catch(error => {
                        console.log('CSRF token refresh failed:', error);
                    });
                }
            });
        </script>

        <!-- PWA Script -->
        <script src="/pwa.js"></script>
        @livewireScripts
    </body>
</html>
