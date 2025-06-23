<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 dark:bg-gray-900">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Prevent aggressive caching for dynamic pages -->
        @if(request()->is('tickets*') || request()->is('dashboard*'))
            <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Expires" content="0">
        @endif

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
    <body class="font-sans antialiased h-full">
        <div class="min-h-full bg-gray-50 dark:bg-gray-900">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- PWA Install Button -->
        <div id="install-button" class="fixed bottom-4 right-4 z-50" style="display: none;">
            <button onclick="window.pwaManager.promptInstall()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span>Install App</span>
            </button>
        </div>

        <!-- PWA Status Component (Development Only) -->
        @if(config('app.debug'))
            <x-pwa-status />
        @endif

        <!-- Dark Mode Toggle Script -->
        <script>
            // Check for saved dark mode preference or default to light mode
            function initializeDarkMode() {
                const isDark = localStorage.getItem('darkMode') === 'true' || 
                    (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
                
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                
                console.log('Dark mode initialized:', isDark ? 'dark' : 'light');
            }

            // Dark mode toggle function
            function toggleDarkMode() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', isDark);
                console.log('Dark mode toggled to:', isDark ? 'dark' : 'light');
            }

            // Initialize dark mode when DOM is loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeDarkMode);
            } else {
                initializeDarkMode();
            }

            // Make function globally available
            window.toggleDarkMode = toggleDarkMode;
            
            // Add event listener for system preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('darkMode')) {
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        </script>

        <!-- PWA Script -->
        <script src="/pwa.js"></script>
        @livewireScripts
    </body>
</html>
