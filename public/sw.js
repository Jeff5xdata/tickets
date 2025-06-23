const CACHE_NAME = "ticket-system-v4";
const urlsToCache = [
    "/",
    "/offline",
    "/build/assets/app-DJg-UrUL.css",
    "/build/assets/app-DNxiirP_.js",
    "/build/manifest.json",
    "/images/icon-192x192.png",
    "/images/icon-512x512.png",
];

// Install event - cache resources
self.addEventListener("install", (event) => {
    console.log("Service Worker installing...");
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("Opened cache");
            // Cache resources one by one to handle failures gracefully
            return Promise.allSettled(
                urlsToCache.map((url) =>
                    cache.add(url).catch((error) => {
                        console.warn("Failed to cache:", url, error);
                        return null;
                    })
                )
            ).then((results) => {
                const successful = results.filter(
                    (r) => r.status === "fulfilled"
                ).length;
                const failed = results.filter(
                    (r) => r.status === "rejected"
                ).length;
                console.log(
                    `Cache installation complete: ${successful} successful, ${failed} failed`
                );
            });
        })
    );
    // Skip waiting to activate immediately
    self.skipWaiting();
});

// Fetch event - serve from cache when offline
self.addEventListener("fetch", (event) => {
    // Only handle GET requests for caching
    if (event.request.method !== "GET") {
        return;
    }

    // Skip caching for certain request types and dynamic pages
    if (
        event.request.url.includes("/api/") ||
        event.request.url.includes("/oauth/") ||
        event.request.url.includes("/auth/") ||
        event.request.url.includes("/email-accounts/google/redirect") ||
        event.request.url.includes("/email-accounts/microsoft/redirect") ||
        event.request.url.includes("/email-accounts/google-tasks/redirect") ||
        event.request.url.includes(
            "/email-accounts/google-calendar/redirect"
        ) ||
        event.request.url.includes(
            "/email-accounts/microsoft-calendar/redirect"
        ) ||
        event.request.url.includes("/tickets") ||
        event.request.url.includes("/dashboard")
    ) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((response) => {
            // Return cached version or fetch from network
            if (response) {
                return response;
            }

            return fetch(event.request)
                .then((response) => {
                    // Check if we received a valid response
                    if (
                        !response ||
                        response.status !== 200 ||
                        response.type !== "basic"
                    ) {
                        return response;
                    }

                    // Clone the response
                    const responseToCache = response.clone();

                    // Double-check: Only cache GET requests and ensure it's a valid request
                    if (
                        event.request.method === "GET" &&
                        event.request.url.startsWith(self.location.origin) &&
                        !event.request.url.includes("/api/") &&
                        !event.request.url.includes("/oauth/") &&
                        !event.request.url.includes("/auth/")
                    ) {
                        caches
                            .open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            })
                            .catch((error) => {
                                console.warn(
                                    "Failed to cache response:",
                                    error
                                );
                            });
                    }

                    return response;
                })
                .catch(() => {
                    // If offline and trying to fetch a page, return offline page
                    if (event.request.mode === "navigate") {
                        return caches.match("/offline");
                    }

                    // For other requests, return a default response
                    return new Response("Offline content not available", {
                        status: 503,
                        statusText: "Service Unavailable",
                        headers: new Headers({
                            "Content-Type": "text/plain",
                        }),
                    });
                });
        })
    );
});

// Activate event - clean up old caches
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log("Deleting old cache:", cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    // Claim all clients immediately
    event.waitUntil(self.clients.claim());
});

// Listen for messages from the main thread
self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

// Background sync for offline actions
self.addEventListener("sync", (event) => {
    if (event.tag === "background-sync") {
        event.waitUntil(doBackgroundSync());
    }
});

// Push notification handling
self.addEventListener("push", (event) => {
    let payload = {
        title: "Ticket System",
        body: "New notification from Ticket System",
        icon: "/images/icon-192x192.png",
        badge: "/images/icon-72x72.png",
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1,
        },
        actions: [
            {
                action: "explore",
                title: "View",
                icon: "/images/icon-96x96.png",
            },
            {
                action: "close",
                title: "Close",
                icon: "/images/icon-96x96.png",
            },
        ],
    };

    // Try to parse the payload if it exists
    if (event.data) {
        try {
            const data = event.data.json();
            payload = {
                ...payload,
                ...data,
            };
        } catch (error) {
            console.warn("Failed to parse push payload:", error);
            payload.body = event.data.text() || payload.body;
        }
    }

    event.waitUntil(self.registration.showNotification(payload.title, payload));
});

// Notification click handling
self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    if (event.action === "explore") {
        event.waitUntil(
            clients.openWindow("/dashboard").catch((error) => {
                console.error("Failed to open window:", error);
            })
        );
    } else if (event.action === "close") {
        // Just close the notification
        return;
    } else {
        // Default action - open the app
        event.waitUntil(
            clients.openWindow("/dashboard").catch((error) => {
                console.error("Failed to open window:", error);
            })
        );
    }
});

// Background sync function
function doBackgroundSync() {
    // Implement background sync logic here
    console.log("Background sync triggered");
    return Promise.resolve();
}
