<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">

            <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    @if (url()->current() != route('dashboard'))
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" class="dark:text-gray-300 dark:hover:text-white">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @endif
                    
                    <x-nav-link href="{{ route('tickets.index') }}" :active="request()->routeIs('tickets.*')" class="dark:text-gray-300 dark:hover:text-white">
                        {{ __('Tickets') }}
                    </x-nav-link>

                    <x-nav-link href="{{ route('google-tasks.index') }}" :active="request()->routeIs('google-tasks.*')" class="dark:text-gray-300 dark:hover:text-white">
                        {{ __('Tasks') }}
                    </x-nav-link>

                    <x-nav-link href="{{ route('calendar-events.index') }}" :active="request()->routeIs('calendar-events.*')" class="dark:text-gray-300 dark:hover:text-white">
                        {{ __('Calendar') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                    {{-- DROPDOWN FOR EMAIL --}}
                    <div x-data="{ open: false }" class="relative mr-4">
                        <button @click="open = !open" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-white focus:outline-none transition">
                            {{ __('Settings') }}
                            <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg z-50 bg-white dark:bg-gray-800 py-1">
                            <x-dropdown-link href="{{ route('email-rules.index') }}" :active="request()->routeIs('email-rules.*')">
                                {{ __('Email Rules') }}
                            </x-dropdown-link>
                            <x-dropdown-link href="{{ route('email-accounts.signatures.index') }}" :active="request()->routeIs('email-accounts.signatures.*')">
                                {{ __('Email Signatures') }}
                            </x-dropdown-link>
                            <x-dropdown-link href="{{ route('email-accounts.index') }}" :active="request()->routeIs('email-accounts.*')">
                                {{ __('Email Accounts') }}
                            </x-dropdown-link>
                        </div>
                    </div>
                    {{-- END DROPDOWN --}}

                <!-- PWA Install Button -->
                <button id="pwa-install-button" onclick="window.pwaManager.promptInstall()" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors ml-4" style="display: none;" title="Install App">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </button>

                <!-- Dark Mode Toggle -->
                <button onclick="toggleDarkMode()" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors ml-4" id="darkModeToggle">
                    <!-- Moon icon for light mode -->
                    <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <!-- Sun icon for dark mode -->
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>

                <!-- Settings Dropdown -->
                @auth
                <div class="ml-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none transition duration-150 ease-in-out">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link href="{{ route('profile.edit') }}" class="dark:text-gray-300 dark:hover:bg-gray-700">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link class="dark:text-gray-300 dark:hover:bg-gray-700">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>
                </div>
                @else
                <div class="ml-3 flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        {{ __('Login') }}
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        {{ __('Register') }}
                    </a>
                </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <!-- PWA Install Button for Mobile -->
                <button id="pwa-install-button-mobile" onclick="window.pwaManager.promptInstall()" class="p-2 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out mr-2" style="display: none;" title="Install App">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </button>

                <!-- Dark Mode Toggle for Mobile -->
                <button onclick="toggleDarkMode()" class="p-2 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out mr-2" id="darkModeToggleMobile">
                    <!-- Moon icon for light mode -->
                    <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <!-- Sun icon for dark mode -->
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>
                
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" class="dark:text-gray-300 dark:hover:text-white">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link href="{{ route('tickets.index') }}" :active="request()->routeIs('tickets.*')" class="dark:text-gray-300 dark:hover:text-white">
                {{ __('Tickets') }}
            </x-responsive-nav-link>
            
            {{-- Collapsible Email Section --}}
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white focus:outline-none">
                    <span>{{ __('Email') }}</span>
                    <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" class="pl-4">
                    <x-responsive-nav-link href="{{ route('email-accounts.index') }}" :active="request()->routeIs('email-accounts.*')">
                        {{ __('Email Accounts') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('email-rules.index') }}" :active="request()->routeIs('email-rules.*')">
                        {{ __('Email Rules') }}
                    </x-responsive-nav-link>
                </div>
            </div>
            {{-- End Collapsible Email Section --}}
            
            <x-responsive-nav-link href="{{ route('google-tasks.index') }}" :active="request()->routeIs('google-tasks.*')" class="dark:text-gray-300 dark:hover:text-white">
                {{ __('Tasks') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link href="{{ route('calendar-events.index') }}" :active="request()->routeIs('calendar-events.*')" class="dark:text-gray-300 dark:hover:text-white">
                {{ __('Calendar') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        @auth
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.edit') }}" class="dark:text-gray-300 dark:hover:text-white">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link class="dark:text-gray-300 dark:hover:text-white">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
        @else
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('login') }}" class="dark:text-gray-300 dark:hover:text-white">
                    {{ __('Login') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('register') }}" class="dark:text-gray-300 dark:hover:text-white">
                    {{ __('Register') }}
                </x-responsive-nav-link>
            </div>
        </div>
        @endauth
    </div>
</nav>
