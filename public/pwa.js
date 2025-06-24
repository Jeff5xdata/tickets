// PWA Registration and Management
class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isOnline = navigator.onLine;
        this.updateAvailable = false;
        this.init();
    }

    init() {
        this.registerServiceWorker();
        this.setupInstallPrompt();
        this.setupOnlineOfflineHandlers();
        this.setupBeforeInstallPrompt();
        this.setupUpdateHandling();
    }

    // Register service worker
    async registerServiceWorker() {
        if ("serviceWorker" in navigator) {
            try {
                const registration = await navigator.serviceWorker.register(
                    "/sw.js",
                    {
                        scope: "/",
                        updateViaCache: "none", // Force update
                    }
                );
                console.log(
                    "Service Worker registered successfully:",
                    registration
                );

                // Force update if there's an existing service worker
                if (navigator.serviceWorker.controller) {
                    registration.update();
                }

                // Check for updates every minute (max age = 1 minute)
                setInterval(() => {
                    registration.update();
                }, 60 * 1000); // 1 minute

                // Check for updates
                registration.addEventListener("updatefound", () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener("statechange", () => {
                        if (
                            newWorker.state === "installed" &&
                            navigator.serviceWorker.controller
                        ) {
                            this.updateAvailable = true;
                            // Only show update notification for static pages
                            if (
                                !window.location.pathname.includes(
                                    "/tickets"
                                ) &&
                                !window.location.pathname.includes("/dashboard")
                            ) {
                                this.showUpdateNotification();
                            }
                        }
                    });
                });

                // Handle service worker errors
                registration.addEventListener("error", (error) => {
                    console.error("Service Worker registration error:", error);
                });

                // Handle controller change (new service worker activated)
                navigator.serviceWorker.addEventListener(
                    "controllerchange",
                    () => {
                        console.log("New service worker activated");
                        this.updateAvailable = false;
                        // Only reload for static pages to avoid disrupting user work
                        if (
                            !window.location.pathname.includes("/tickets") &&
                            !window.location.pathname.includes("/dashboard")
                        ) {
                            window.location.reload();
                        }
                    }
                );
            } catch (error) {
                console.error("Service Worker registration failed:", error);
                // Don't show error to user, just log it
            }
        } else {
            console.log("Service Worker not supported in this browser");
        }
    }

    // Setup update handling
    setupUpdateHandling() {
        // Listen for messages from service worker
        navigator.serviceWorker.addEventListener("message", (event) => {
            if (event.data && event.data.type === "RELOAD_PAGE") {
                window.location.reload();
            }
        });
    }

    // Handle install prompt
    setupInstallPrompt() {
        const installButton = document.getElementById("install-button");
        const navInstallButton = document.getElementById("pwa-install-button");
        const navInstallButtonMobile = document.getElementById(
            "pwa-install-button-mobile"
        );

        if (installButton) {
            installButton.addEventListener("click", () => {
                this.promptInstall();
            });
        }

        if (navInstallButton) {
            navInstallButton.addEventListener("click", () => {
                this.promptInstall();
            });
        }

        if (navInstallButtonMobile) {
            navInstallButtonMobile.addEventListener("click", () => {
                this.promptInstall();
            });
        }
    }

    // Setup beforeinstallprompt event
    setupBeforeInstallPrompt() {
        window.addEventListener("beforeinstallprompt", (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButtons();
        });

        window.addEventListener("appinstalled", () => {
            console.log("PWA was installed");
            this.hideInstallButtons();
            this.deferredPrompt = null;
        });
    }

    // Show install buttons
    showInstallButtons() {
        const installButton = document.getElementById("install-button");
        const navInstallButton = document.getElementById("pwa-install-button");
        const navInstallButtonMobile = document.getElementById(
            "pwa-install-button-mobile"
        );

        if (installButton) {
            installButton.style.display = "block";
        }
        if (navInstallButton) {
            navInstallButton.style.display = "block";
        }
        if (navInstallButtonMobile) {
            navInstallButtonMobile.style.display = "block";
        }
    }

    // Hide install buttons
    hideInstallButtons() {
        const installButton = document.getElementById("install-button");
        const navInstallButton = document.getElementById("pwa-install-button");
        const navInstallButtonMobile = document.getElementById(
            "pwa-install-button-mobile"
        );

        if (installButton) {
            installButton.style.display = "none";
        }
        if (navInstallButton) {
            navInstallButton.style.display = "none";
        }
        if (navInstallButtonMobile) {
            navInstallButtonMobile.style.display = "none";
        }
    }

    // Prompt for installation
    async promptInstall() {
        if (this.deferredPrompt) {
            try {
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                this.deferredPrompt = null;
                this.hideInstallButtons();
            } catch (error) {
                console.error("Install prompt failed:", error);
            }
        }
    }

    // Setup online/offline handlers
    setupOnlineOfflineHandlers() {
        window.addEventListener("online", () => {
            this.isOnline = true;
            this.showOnlineStatus();
        });

        window.addEventListener("offline", () => {
            this.isOnline = false;
            this.showOfflineStatus();
        });
    }

    // Show online status
    showOnlineStatus() {
        this.showNotification("You are back online!", "success");
    }

    // Show offline status
    showOfflineStatus() {
        this.showNotification(
            "You are currently offline. Some features may be limited.",
            "warning"
        );
    }

    // Show update notification
    showUpdateNotification() {
        const notification = document.createElement("div");
        notification.className =
            "fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 bg-blue-500 text-white max-w-sm";
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="font-medium">Update Available</p>
                    <p class="text-sm opacity-90">A new version is available</p>
                </div>
                <button id="reload-btn" class="ml-4 px-3 py-1 bg-white text-blue-500 rounded text-sm font-medium hover:bg-gray-100 transition-colors">
                    Reload
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Add reload button functionality
        const reloadBtn = notification.querySelector("#reload-btn");
        reloadBtn.addEventListener("click", () => {
            this.reloadForUpdate();
        });

        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 10000);
    }

    // Reload for update
    reloadForUpdate() {
        if (navigator.serviceWorker.controller) {
            // Send message to service worker to skip waiting
            navigator.serviceWorker.controller.postMessage({
                type: "SKIP_WAITING",
            });
        }
        // Reload the page
        window.location.reload();
    }

    // Show notification
    showNotification(message, type = "info") {
        const notification = document.createElement("div");
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === "success"
                ? "bg-green-500 text-white"
                : type === "warning"
                ? "bg-yellow-500 text-white"
                : "bg-blue-500 text-white"
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Request notification permission
    async requestNotificationPermission() {
        if ("Notification" in window) {
            try {
                const permission = await Notification.requestPermission();
                if (permission === "granted") {
                    await this.subscribeToPushNotifications();
                }
                return permission === "granted";
            } catch (error) {
                console.error("Notification permission request failed:", error);
                return false;
            }
        }
        return false;
    }

    // Subscribe to push notifications
    async subscribeToPushNotifications() {
        if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
            console.log("Push notifications not supported");
            return false;
        }

        try {
            const registration = await navigator.serviceWorker.ready;

            // Check if already subscribed
            const existingSubscription =
                await registration.pushManager.getSubscription();
            if (existingSubscription) {
                console.log("Already subscribed to push notifications");
                return true;
            }

            // Get VAPID public key from server
            const response = await fetch("/api/vapid-public-key");
            if (!response.ok) {
                throw new Error("Failed to get VAPID public key");
            }
            const vapidPublicKey = await response.text();

            // Convert VAPID key to Uint8Array
            const vapidKey = this.urlBase64ToUint8Array(vapidPublicKey);

            // Subscribe to push notifications
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: vapidKey,
            });

            // Send subscription to server
            await this.saveSubscription(subscription);

            console.log("Successfully subscribed to push notifications");
            return true;
        } catch (error) {
            console.error("Failed to subscribe to push notifications:", error);
            return false;
        }
    }

    // Save subscription to server
    async saveSubscription(subscription) {
        const response = await fetch("/push-subscriptions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                p256dh_key: this.arrayBufferToBase64(
                    subscription.getKey("p256dh")
                ),
                auth_token: this.arrayBufferToBase64(
                    subscription.getKey("auth")
                ),
            }),
        });

        if (!response.ok) {
            throw new Error("Failed to save subscription");
        }

        return response.json();
    }

    // Unsubscribe from push notifications
    async unsubscribeFromPushNotifications() {
        if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
            return false;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription =
                await registration.pushManager.getSubscription();

            if (subscription) {
                await subscription.unsubscribe();

                // Remove from server
                await fetch("/push-subscriptions", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content"),
                    },
                    body: JSON.stringify({
                        endpoint: subscription.endpoint,
                    }),
                });

                console.log(
                    "Successfully unsubscribed from push notifications"
                );
            }

            return true;
        } catch (error) {
            console.error(
                "Failed to unsubscribe from push notifications:",
                error
            );
            return false;
        }
    }

    // Convert URL base64 to Uint8Array
    urlBase64ToUint8Array(base64String) {
        const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, "+")
            .replace(/_/g, "/");

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Convert ArrayBuffer to base64
    arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = "";
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    // Send notification
    sendNotification(title, options = {}) {
        if ("Notification" in window && Notification.permission === "granted") {
            try {
                new Notification(title, {
                    icon: "/images/icon-192x192.png",
                    badge: "/images/icon-72x72.png",
                    ...options,
                });
            } catch (error) {
                console.error("Failed to send notification:", error);
            }
        }
    }

    // Check if app is installed
    isAppInstalled() {
        return (
            window.matchMedia("(display-mode: standalone)").matches ||
            window.navigator.standalone === true
        );
    }

    // Get app installation status
    getInstallationStatus() {
        return {
            isInstalled: this.isAppInstalled(),
            isOnline: this.isOnline,
            hasServiceWorker: "serviceWorker" in navigator,
            hasNotifications: "Notification" in window,
            hasPushManager: "PushManager" in window,
            notificationPermission:
                "Notification" in window ? Notification.permission : "denied",
        };
    }
}

// Initialize PWA Manager when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    if (!window.pwaManager) {
        window.pwaManager = new PWAManager();
    }
});

// Export for use in other scripts
if (typeof module !== "undefined" && module.exports) {
    module.exports = PWAManager;
}
