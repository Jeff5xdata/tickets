@php
    $pwaStatus = [
        'isInstalled' => false,
        'isOnline' => true,
        'hasServiceWorker' => false,
        'hasNotifications' => false,
        'hasPushManager' => false,
        'notificationPermission' => 'denied'
    ];
@endphp

<div x-data="{
    isOnline: true,
    notificationPermission: 'Notification' in window ? Notification.permission : 'denied',
    isSubscribed: false,
    isLoading: false,
    isExpanded: false,
    
    // Listen for online/offline events
    init() {
        window.addEventListener('online', () => { this.isOnline = true; });
        window.addEventListener('offline', () => { this.isOnline = false; });
        
        // Check if already subscribed to push notifications
        this.checkPushSubscription();
    },
    
    async checkPushSubscription() {
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                this.isSubscribed = !!subscription;
            } catch (error) {
                console.error('Error checking push subscription:', error);
            }
        }
    },
    
    async togglePushNotifications() {
        this.isLoading = true;
        
        try {
            if (this.isSubscribed) {
                await window.pwaManager.unsubscribeFromPushNotifications();
                this.isSubscribed = false;
            } else {
                const granted = await window.pwaManager.requestNotificationPermission();
                if (granted) {
                    this.isSubscribed = true;
                }
            }
        } catch (error) {
            console.error('Error toggling push notifications:', error);
        } finally {
            this.isLoading = false;
        }
    },
    
    toggleExpanded() {
        this.isExpanded = !this.isExpanded;
    }
}" class="fixed top-4 right-4 z-50">
    <!-- Toggle Button (always visible) -->
    <div class="mb-2">
        <button 
            @click="toggleExpanded()"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            :class="isExpanded ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </button>
    </div>

    <!-- PWA Status Panel (slides in/out) -->
    <div 
        x-show="isExpanded"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform translate-x-full opacity-0"
        x-transition:enter-end="transform translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="transform translate-x-0 opacity-100"
        x-transition:leave-end="transform translate-x-full opacity-0"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 max-w-sm">
        
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PWA Status</h3>
            <button 
                @click="toggleExpanded()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Online Status -->
        <div class="flex items-center mb-3">
            <div class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></div>
            <span class="ml-2 text-xs text-gray-600 dark:text-gray-400" x-text="isOnline ? 'Online' : 'Offline'"></span>
        </div>

        <!-- Notification Permission Button -->
        <div class="space-y-2">
            <div x-show="notificationPermission === 'default'">
                <button 
                    @click="togglePushNotifications()"
                    :disabled="isLoading"
                    class="w-full bg-blue-500 hover:bg-blue-600 disabled:bg-gray-400 text-white text-xs px-3 py-2 rounded-md transition-colors">
                    <span x-show="!isLoading">Enable Notifications</span>
                    <span x-show="isLoading">Loading...</span>
                </button>
            </div>
            
            <div x-show="notificationPermission === 'granted'">
                <button 
                    @click="togglePushNotifications()"
                    :disabled="isLoading"
                    class="w-full text-xs px-3 py-2 rounded-md transition-colors"
                    :class="isSubscribed ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white'">
                    <span x-show="!isLoading" x-text="isSubscribed ? 'Disable Push Notifications' : 'Enable Push Notifications'"></span>
                    <span x-show="isLoading">Loading...</span>
                </button>
            </div>
            
            <div x-show="notificationPermission === 'denied'">
                <div class="text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded">
                    Notifications blocked. Please enable them in your browser settings.
                </div>
            </div>
        </div>

        <!-- Install Button (shown when app can be installed) -->
        <div id="install-button" style="display: none;" class="mt-3">
            <button 
                onclick="window.pwaManager.promptInstall()"
                class="w-full bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-2 rounded-md transition-colors">
                Install App
            </button>
        </div>
    </div>
</div> 