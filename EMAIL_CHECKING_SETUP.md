# Email Checking Service Setup

This document explains how to set up the automated email checking service that runs every 5 minutes.

## 🚀 Quick Setup

### 1. Run the Setup Script

```bash
./setup_cron.sh
```

This will automatically add the necessary cron job to run the email checker every 5 minutes.

### 2. Manual Cron Setup (Alternative)

If you prefer to set up the cron job manually:

```bash
# Edit crontab
crontab -e

# Add this line:
*/5 * * * * cd /var/www/tickets && php artisan schedule:run >> /dev/null 2>&1
```

## 📋 What the Service Does

### Every 5 Minutes:

-   ✅ Checks all active email accounts
-   ✅ Fetches new emails from IMAP, Gmail, and Outlook
-   ✅ Creates tickets from new emails
-   ✅ Applies email rules (auto-reply, forwarding, etc.)
-   ✅ Refreshes OAuth tokens when needed
-   ✅ Logs all activities for monitoring

### Supported Email Providers:

-   **IMAP** (custom email servers)
-   **Gmail** (OAuth)
-   **Outlook/Microsoft 365** (OAuth)

## 🛠️ Manual Testing

### Test the Email Checker:

```bash
# Basic check
php artisan emails:check

# Detailed output
php artisan emails:check --detailed

# Check specific account
php artisan emails:check --account=1
```

### View Logs:

```bash
# View recent logs
tail -f storage/logs/laravel.log

# Filter email check logs
grep "Email check" storage/logs/laravel.log
```

## 📊 Monitoring

### Check Service Status:

```bash
# View cron jobs
crontab -l

# Check if scheduler is running
php artisan schedule:list

# View last check time (via cache)
php artisan tinker
>>> Cache::get('last_email_check')
```

### Common Issues:

1. **No refresh token**: Reconnect OAuth accounts
2. **Permission errors**: Check API permissions in Google/Microsoft
3. **IMAP connection failed**: Verify server settings

## 🔧 Configuration

### Email Check Frequency:

Edit `routes/console.php` to change the frequency:

```php
// Every 5 minutes (current)
Schedule::command('emails:check')->everyFiveMinutes();

// Every minute (for testing)
Schedule::command('emails:check')->everyMinute();

// Every 10 minutes
Schedule::command('emails:check')->everyTenMinutes();
```

### Logging:

-   All email checks are logged to `storage/logs/laravel.log`
-   Failed checks include detailed error information
-   Successful checks show email counts

## 🚨 Troubleshooting

### Service Not Running:

1. Check if cron is running: `systemctl status cron`
2. Verify cron job exists: `crontab -l`
3. Check Laravel logs: `tail -f storage/logs/laravel.log`

### OAuth Issues:

1. Reconnect email accounts in the web interface
2. Check API permissions in Google/Microsoft consoles
3. Verify environment variables are set correctly

### IMAP Issues:

1. Verify server settings (host, port, encryption)
2. Check username/password
3. Test connection manually

## 📈 Performance

The service is designed to be lightweight:

-   Runs in background
-   Prevents overlapping executions
-   Handles errors gracefully
-   Caches last check time
-   Logs performance metrics

## 🔐 Security

-   OAuth tokens are automatically refreshed
-   Failed connections are logged but don't stop the service
-   No sensitive data is logged
-   Each account is checked independently
