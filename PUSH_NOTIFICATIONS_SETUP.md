# Push Notifications Setup

This document explains how to set up and use push notifications for the Ticket System PWA.

## Overview

The push notification system allows users to receive real-time notifications when new tickets are created, even when the app is not actively open. This is implemented using the Web Push API and VAPID (Voluntary Application Server Identification).

## Features

-   **Real-time notifications**: Receive notifications when new tickets are created
-   **Rich notifications**: Include ticket details, actions, and proper icons
-   **Cross-platform**: Works on desktop and mobile browsers
-   **Offline support**: Notifications work even when the app is closed
-   **User control**: Users can enable/disable notifications

## Setup Instructions

### 1. Generate VAPID Keys

Run the following command to generate VAPID keys:

```bash
php artisan vapid:generate
```

This will output the keys you need to add to your `.env` file:

```env
VAPID_SUBJECT=mailto:your-email@example.com
VAPID_PUBLIC_KEY=your_public_key_here
VAPID_PRIVATE_KEY=your_private_key_here
```

**Important**: Replace `your-email@example.com` with your actual email address.

### 2. Run Migrations

The push notification system requires a database table to store user subscriptions:

```bash
php artisan migrate
```

### 3. Configure Broadcasting (Optional)

For real-time features, you may want to configure broadcasting. The system uses Laravel's broadcasting system for additional real-time features.

## How It Works

### 1. User Subscription

When a user visits the app:

1. The PWA requests notification permission
2. If granted, the service worker subscribes to push notifications
3. The subscription details are sent to the server and stored
4. The user can now receive push notifications

### 2. Notification Delivery

When a new ticket is created:

1. The `TicketCreated` event is fired
2. The `NewTicketNotification` is sent to the user
3. The `WebPushService` sends the notification to all user's devices
4. The service worker receives the push message and displays the notification

### 3. User Interaction

When a user clicks on a notification:

1. The service worker handles the click event
2. If the notification has ticket data, it opens the specific ticket
3. Otherwise, it opens the dashboard

## User Interface

### PWA Status Component

The PWA status component (bottom-right corner) shows:

-   Online/offline status
-   Notification permission status
-   Push notification subscription status
-   Install app button (when available)

### Notification Controls

Users can:

-   Enable/disable push notifications
-   See notification permission status
-   Install the app as a PWA

## Testing

### Development Testing

In development mode, you can test push notifications using the test endpoint:

```bash
curl -X POST http://your-app.test/test-push-notification \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  --cookie "laravel_session=your_session"
```

### Manual Testing

1. Open the app in a supported browser
2. Click "Enable Notifications" in the PWA status component
3. Grant notification permission
4. Create a new ticket (manually or via email)
5. You should receive a push notification

## Browser Support

### Full Support

-   Chrome (Desktop & Mobile)
-   Edge
-   Firefox
-   Safari (iOS 11.3+)

### Requirements

-   HTTPS connection (required for service workers)
-   User permission granted
-   Service worker support

## Troubleshooting

### Common Issues

1. **Notifications not working**

    - Check if HTTPS is enabled
    - Verify VAPID keys are correctly set
    - Check browser console for errors
    - Ensure notification permission is granted

2. **Subscription errors**

    - Check if the service worker is registered
    - Verify VAPID public key is accessible
    - Check network connectivity

3. **Notifications not showing**
    - Check if the app is in focus (notifications may be silent)
    - Verify notification settings in browser
    - Check if notifications are blocked

### Debug Commands

```javascript
// Check PWA status
console.log(window.pwaManager.getInstallationStatus());

// Check push subscription
navigator.serviceWorker.ready.then((registration) => {
    registration.pushManager.getSubscription().then((subscription) => {
        console.log("Push subscription:", subscription);
    });
});

// Test notification
window.pwaManager.sendNotification("Test", { body: "Test message" });
```

### Logs

Check the Laravel logs for push notification errors:

```bash
tail -f storage/logs/laravel.log | grep -i push
```

## Security Considerations

1. **VAPID Keys**: Keep private keys secure and never expose them in client-side code
2. **User Consent**: Always request explicit permission before sending notifications
3. **Rate Limiting**: Consider implementing rate limiting for notification endpoints
4. **Data Privacy**: Only send necessary data in notifications

## Performance

-   Push notifications are sent asynchronously
-   Failed subscriptions are automatically cleaned up
-   Notifications are queued for better performance
-   Expired subscriptions are removed automatically

## Future Enhancements

-   Notification preferences per user
-   Different notification types (urgent, normal, etc.)
-   Notification history
-   Bulk notification management
-   Advanced notification actions
