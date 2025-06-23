# Gmail Scope Issue Resolution

## Problem

If you're seeing errors like "Request had insufficient authentication scopes" or "ACCESS_TOKEN_SCOPE_INSUFFICIENT" when fetching emails from Gmail, this means your Gmail account was connected with limited permissions.

## Cause

The application needs the `gmail.modify` scope to mark emails as read after processing them. Accounts connected before this scope was added will encounter this error.

## Solution

You need to reconnect your Gmail account to grant the additional permissions:

1. Go to **Email Accounts** in your dashboard
2. Find your Gmail account and click **Edit**
3. Click the **"Reconnect Account"** button
4. Complete the Google OAuth process again
5. The account will now have all required permissions

## What This Fixes

-   ✅ Emails will be marked as read after processing
-   ✅ No more scope-related errors
-   ✅ Full Gmail functionality restored

## Technical Details

The application now requests these Gmail scopes:

-   `gmail.readonly` - Read emails
-   `gmail.send` - Send emails
-   `gmail.modify` - Modify email labels (mark as read)
-   `calendar` - Calendar access
-   `calendar.events` - Calendar event management

## Alternative

If you prefer not to reconnect, the application will still fetch and process emails, but they will remain marked as unread in Gmail.
