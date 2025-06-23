# Progressive Web App (PWA) Setup

This Laravel application has been converted into a Progressive Web App (PWA) with the following features:

## Features

### 🚀 Core PWA Features

-   **Installable**: Users can install the app on their devices
-   **Offline Support**: Basic offline functionality with cached resources
-   **Service Worker**: Handles caching and offline requests
-   **Web App Manifest**: Defines app metadata and installation behavior
-   **Responsive Design**: Works on all device sizes

### 📱 Installation

-   **Desktop**: Install button appears in browser address bar
-   **Mobile**: Install prompt appears in navigation
-   **iOS**: Add to home screen via Safari share menu
-   **Android**: Install prompt in Chrome

### 🔄 Offline Functionality

-   **Cached Pages**: Main pages are cached for offline access
-   **Offline Page**: Custom offline page when no cached content available
-   **Background Sync**: Framework for future offline action queuing

### 🔔 Notifications

-   **Push Notifications**: Framework ready for push notifications
-   **Permission Management**: Automatic permission requests
-   **Notification Actions**: View and close actions

## Files Added/Modified

### New Files

-   `public/manifest.json` - Web app manifest
-   `public/sw.js` - Service worker
-   `public/pwa.js` - PWA management JavaScript
-   `public/images/icon-*.png` - App icons in various sizes
-   `public/images/icon.svg` - Source SVG icon
-   `resources/views/offline.blade.php` - Offline page
-   `resources/views/components/pwa-status.blade.php` - PWA status component

### Modified Files

-   `resources/views/layouts/app.blade.php` - Added PWA meta tags and scripts
-   `resources/views/layouts/guest.blade.php` - Added PWA meta tags
-   `resources/views/livewire/layout/navigation.blade.php` - Added install buttons
-   `routes/web.php` - Added offline route

## Configuration

### App Icons

The app uses a custom icon design with the following sizes:

-   72x72, 96x96, 128x128, 144x144, 152x152, 167x167, 180x180, 192x192, 384x384, 512x512

### Manifest Configuration

-   **Name**: "Email to Ticket System"
-   **Short Name**: "Ticket System"
-   **Theme Color**: #6366f1 (Indigo)
-   **Background Color**: #ffffff (White)
-   **Display Mode**: standalone
-   **Orientation**: portrait-primary

### Service Worker

-   **Cache Strategy**: Cache-first with network fallback
-   **Cached Resources**: Main pages, CSS, JS, and icons
-   **Offline Handling**: Serves offline page for navigation requests

## Usage

### For Users

1. **Install**: Click the install button in the navigation or browser
2. **Offline Access**: Previously visited pages work offline
3. **Notifications**: Grant permission when prompted
4. **Updates**: App automatically updates when new version is available

### For Developers

1. **PWA Status**: Development mode shows PWA status indicator
2. **Testing**: Use Chrome DevTools > Application tab for PWA testing
3. **Updates**: Service worker automatically handles cache updates
4. **Debugging**: Check browser console for PWA-related logs

## Browser Support

### Full Support

-   Chrome (Desktop & Mobile)
-   Edge
-   Firefox
-   Safari (iOS 11.3+)

### Partial Support

-   Safari (Desktop) - Limited PWA features
-   Internet Explorer - No PWA support

## Testing

### Installation Testing

1. Open the app in a supported browser
2. Look for install button in navigation
3. Click install and verify app appears in app list
4. Test offline functionality

### Offline Testing

1. Open Chrome DevTools
2. Go to Network tab
3. Check "Offline" checkbox
4. Navigate to different pages
5. Verify offline page appears when appropriate

### Service Worker Testing

1. Open Chrome DevTools
2. Go to Application tab
3. Check Service Workers section
4. Verify service worker is registered and active

## Customization

### Changing App Icon

1. Replace `public/images/icon.svg` with your design
2. Run the ImageMagick commands to regenerate PNG files:

```bash
convert public/images/icon.svg -resize 72x72 public/images/icon-72x72.png
convert public/images/icon.svg -resize 96x96 public/images/icon-96x96.png
# ... repeat for all sizes
```

### Updating Manifest

Edit `public/manifest.json` to change:

-   App name and description
-   Theme colors
-   Display mode
-   Shortcuts

### Modifying Service Worker

Edit `public/sw.js` to:

-   Change caching strategy
-   Add more cached resources
-   Implement background sync
-   Add push notification handling

## Troubleshooting

### Common Issues

1. **Install button not showing**: Ensure HTTPS is enabled and app meets install criteria
2. **Service worker not registering**: Check browser console for errors
3. **Offline not working**: Verify service worker is active and caching resources
4. **Icons not loading**: Check file paths and ensure all icon sizes exist

### Debug Commands

```javascript
// Check PWA status
console.log(window.pwaManager.getInstallationStatus());

// Force service worker update
navigator.serviceWorker.getRegistrations().then((registrations) => {
    registrations.forEach((registration) => registration.update());
});

// Clear all caches
caches.keys().then((names) => {
    names.forEach((name) => caches.delete(name));
});
```

## Future Enhancements

### Planned Features

-   **Background Sync**: Queue offline actions for when online
-   **Push Notifications**: Real-time notifications for new tickets
-   **Advanced Caching**: Intelligent caching based on user behavior
-   **Offline Forms**: Save form data locally when offline

### Performance Optimizations

-   **Lazy Loading**: Load resources on demand
-   **Image Optimization**: WebP format support
-   **Code Splitting**: Reduce initial bundle size
-   **Preloading**: Preload critical resources

## Security Considerations

-   **HTTPS Required**: PWA features require HTTPS in production
-   **Content Security Policy**: Ensure CSP allows service worker
-   **Cross-Origin**: Handle CORS for external resources
-   **Data Privacy**: Be mindful of cached sensitive data

## Resources

-   [MDN Web Docs - Progressive Web Apps](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
-   [Web.dev - PWA Guide](https://web.dev/progressive-web-apps/)
-   [Lighthouse PWA Audit](https://developers.google.com/web/tools/lighthouse)
-   [PWA Builder](https://www.pwabuilder.com/)
