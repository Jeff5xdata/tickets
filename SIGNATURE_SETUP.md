# Email Signature Setup Guide

This guide explains how to use the email signature functionality that has been added to the ticket management system.

## Features

The email signature system allows users to:

1. **Add text signatures** - Plain text signatures that are automatically appended to outgoing emails
2. **Add image signatures** - Logo or signature images that are embedded in emails
3. **Preview signatures** - Live preview of how the signature will appear in emails
4. **Manage signatures per account** - Each email account can have its own unique signature

## How to Use

### 1. Accessing Signature Management

**Option A: From Navigation Menu**

1. Click on "Settings" in the top navigation
2. Select "Email Signatures" from the dropdown menu

**Option B: From Email Accounts**

1. Navigate to your email accounts list
2. Click "Manage Signatures" button in the header
3. Or click on an email account to view its details
4. In the account details page, you'll see a new "Email Signature" section
5. Click "Add Signature" or "Edit Signature" to manage the signature

### 2. Signature List View

The signature list view provides an overview of all your email accounts and their signature status:

-   **Account Information**: Name, email, and type for each account
-   **Signature Status**: Visual indicators showing if signatures are configured
-   **Signature Preview**: Truncated preview of text signatures and image file names
-   **Quick Actions**: Edit, Delete, or Add Signature buttons for each account
-   **Statistics**: Ticket counts for each account

### 3. Adding a Text Signature

1. In the signature edit page, use the rich text editor in the "Signature Text" field
2. The editor provides formatting options including bold, italic, lists, alignment, and more
3. You can create professional-looking signatures with proper formatting
4. The signature will be automatically appended to all outgoing emails from this account
5. Click "Update Signature" to save

### 4. Adding an Image Signature

1. In the signature edit page, click "Choose File" in the "Signature Image" section
2. Select an image file (JPEG, PNG, JPG, GIF supported, max 2MB)
3. The image will be uploaded and embedded in outgoing emails
4. Click "Update Signature" to save

### 5. Combining Text and Image

You can have both text and image signatures:

-   Text appears first, followed by the image
-   Both will be included in all outgoing emails
-   You can edit or remove either component independently

### 6. Removing Signatures

-   **Remove text**: Clear the signature text field and save
-   **Remove image**: Click the "Remove Image" button
-   **Remove both**: Clear text and remove image, then save

### 7. Rich Text Editor Features

The signature editor includes a full-featured WYSIWYG editor with the following capabilities:

-   **Text Formatting**: Bold, italic, underline, strikethrough
-   **Text Alignment**: Left, center, right, justify
-   **Lists**: Bullet points and numbered lists
-   **Links**: Add clickable links to your signature
-   **Colors**: Text and background color options
-   **Live Preview**: See your signature exactly as it will appear in emails
-   **HTML Support**: Full HTML formatting for professional signatures

The editor automatically saves your content and provides a real-time preview of how your signature will look in emails.

## Technical Implementation

### Database Changes

The following fields were added to the `email_accounts` table:

-   `signature_text` (TEXT, nullable) - Stores the signature text (supports HTML formatting, max 2000 characters)
-   `signature_image_path` (VARCHAR, nullable) - Stores the path to the signature image

### Files Added/Modified

#### New Files:

-   `app/Http/Controllers/EmailSignatureController.php` - Handles signature CRUD operations
-   `resources/views/email-accounts/signature.blade.php` - Signature edit interface
-   `resources/views/email-accounts/signatures/index.blade.php` - Signature list view
-   `tests/Feature/EmailSignatureTest.php` - Test coverage for signature functionality

#### Modified Files:

-   `app/Models/EmailAccount.php` - Added signature fields and helper methods
-   `app/Services/EmailService.php` - Updated to include signatures in outgoing emails
-   `app/Http/Controllers/TicketController.php` - Updated to pass signature data when sending emails
-   `resources/views/email-accounts/show.blade.php` - Added signature display section
-   `routes/web.php` - Added signature routes

### Helper Methods

The `EmailAccount` model includes several helper methods:

-   `hasSignature()` - Returns true if the account has any signature content
-   `getSignatureText()` - Returns the signature text
-   `getSignatureImageUrl()` - Returns the URL to the signature image
-   `getSignatureHtml()` - Returns formatted HTML for the signature
-   `getFormattedSignature()` - Returns a text representation of the signature

### Email Integration

When sending emails through the system:

1. **Gmail**: Signatures are added to the email body and images are embedded as inline attachments
2. **Outlook**: Signatures are added to the email body and images are embedded as inline attachments
3. **IMAP**: Signatures are added to the email body (image support depends on SMTP configuration)

## Security Features

-   **Authorization**: Users can only manage signatures for their own email accounts
-   **File validation**: Only image files are accepted for signature images
-   **File size limits**: Maximum 2MB for signature images
-   **Secure storage**: Images are stored in the public storage directory with proper access controls

## Routes

The following routes were added:

-   `GET /email-accounts/signatures` - Signature list view
-   `GET /email-accounts/{emailAccount}/signature` - Edit signature page
-   `PUT /email-accounts/{emailAccount}/signature` - Update signature
-   `DELETE /email-accounts/{emailAccount}/signature/image` - Remove signature image
-   `DELETE /email-accounts/{emailAccount}/signature` - Delete entire signature

## Testing

Run the signature tests with:

```bash
php artisan test tests/Feature/EmailSignatureTest.php
```

## Troubleshooting

### Common Issues:

1. **Images not displaying**: Ensure the storage link is created with `php artisan storage:link`
2. **Permission errors**: Check that the storage directory is writable
3. **Signature not appearing in emails**: Verify the email account has an active signature configured

### File Permissions:

Ensure the following directories are writable:

-   `storage/app/public/signatures/`
-   `public/storage/` (symlink to storage/app/public)

## Future Enhancements

Potential improvements for the signature system:

1. **Multiple signatures** - Different signatures for different contexts
2. **Signature templates** - Pre-built signature templates
3. **Conditional signatures** - Signatures that change based on email content or recipient
4. **Signature analytics** - Track signature usage and effectiveness
5. **Advanced formatting** - More styling options and custom CSS support
