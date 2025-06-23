# Email-Based Ticket Management System

A comprehensive Laravel-based email ticket management system with AI-powered features, Google Tasks integration, calendar management, push notifications, and automated email processing.

## 🚀 Features

### Core Functionality

-   **Email Ticket Management**: Convert emails into organized tickets with full lifecycle management
-   **Multi-Account Support**: Connect multiple email accounts (Gmail, Outlook, IMAP)
-   **Dark Mode**: Full dark/light mode toggle with persistent preferences
-   **Responsive Design**: Mobile-friendly interface built with Tailwind CSS
-   **Progressive Web App (PWA)**: Installable app with offline support and push notifications

### AI-Powered Features

-   **Email Rewriting**: AI-powered email content rewriting using Google Gemini
-   **Smart Summaries**: Automatic email summarization
-   **Action Item Extraction**: Extract actionable items from emails
-   **Response Generation**: AI-assisted email response generation

### Google Tasks Integration

-   **Task Creation**: Create Google Tasks directly from email tickets
-   **Task Management**: View, edit, and manage tasks with interactive calendar
-   **Auto-Save**: Automatic draft saving while editing tasks (saves every 2 seconds)
-   **Sync Capabilities**: Two-way synchronization with Google Tasks
-   **Priority Management**: Set task priorities and due dates
-   **Parent-Child Tasks**: Create subtasks and organize task hierarchies
-   **Task Duplication**: Duplicate existing tasks for similar workflows

### Calendar Integration

-   **Multi-Provider Support**: Google Calendar, Microsoft Outlook, and iCal calendars
-   **Event Management**: Create, edit, and delete calendar events
-   **Calendar Sync**: Automatic synchronization with external calendars
-   **Event Details**: Rich event information including attendees, location, and recurrence
-   **Calendar Selection**: Choose from multiple calendars per account
-   **All-Day Events**: Support for all-day and timed events

### Email Automation

-   **Email Rules**: Create automated rules for email processing
-   **Auto-Responses**: Set up automatic email responses
-   **Filtering**: Filter emails by sender, subject, or content
-   **Forwarding**: Automatically forward emails based on rules

### Email Signatures

-   **Rich Text Signatures**: Create professional signatures with formatting
-   **Image Signatures**: Add logos and signature images to emails
-   **Per-Account Signatures**: Different signatures for different email accounts
-   **Live Preview**: See how signatures will appear in emails
-   **Signature Management**: Easy signature creation and editing interface

### Push Notifications

-   **Real-time Notifications**: Receive notifications for new tickets
-   **Cross-platform**: Works on desktop and mobile browsers
-   **Offline Support**: Notifications work even when app is closed
-   **Rich Notifications**: Include ticket details and actions
-   **User Control**: Enable/disable notifications per user

### Advanced Features

-   **Notes System**: Add internal and public notes to tickets
-   **Reply Management**: Send replies with original email context
-   **Bulk Operations**: Bulk update ticket status and priority
-   **Search & Filter**: Advanced search and filtering capabilities
-   **File Attachments**: Support for email attachments with metadata
-   **Auto-Save**: Automatic saving of task drafts while editing
-   **Task Hierarchies**: Create parent-child task relationships
-   **Task Duplication**: Duplicate tasks for similar workflows

## 📋 Requirements

-   **PHP**: 8.2 or higher
-   **Laravel**: 12.0 or higher
-   **Database**: MySQL 8.0+ or PostgreSQL 13+
-   **Node.js**: 18+ and NPM
-   **Composer**: Latest version
-   **Web Server**: Apache/Nginx
-   **SSL Certificate**: Required for PWA and push notifications

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd tickets
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment Variables

Add the following to your `.env` file:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tickets_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Google OAuth Configuration
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/email-accounts/google/callback

# Google Tasks OAuth
GOOGLE_TASKS_CLIENT_ID=your_google_tasks_client_id
GOOGLE_TASKS_CLIENT_SECRET=your_google_tasks_client_secret
GOOGLE_TASKS_REDIRECT_URI=https://yourdomain.com/email-accounts/google-tasks/callback

# Microsoft OAuth Configuration
MICROSOFT_CLIENT_ID=your_microsoft_client_id
MICROSOFT_CLIENT_SECRET=your_microsoft_client_secret
MICROSOFT_REDIRECT_URI=https://yourdomain.com/email-accounts/microsoft/callback

# Google Gemini AI Configuration
GEMINI_API_KEY=your_gemini_api_key
GEMINI_ENABLED=true

# Push Notifications (VAPID)
VAPID_SUBJECT=mailto:your-email@example.com
VAPID_PUBLIC_KEY=your_vapid_public_key
VAPID_PRIVATE_KEY=your_vapid_private_key

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=local
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### 6. Generate VAPID Keys for Push Notifications

```bash
php artisan vapid:generate
```

### 7. Set Up File Storage

```bash
# Create storage link for public access
php artisan storage:link
```

### 8. Build Assets

```bash
# Build for production
npm run build

# Or for development
npm run dev
```

### 9. Set Up Cron Jobs

Add the following to your server's crontab for automated email checking:

```bash
# Check emails every 5 minutes
*/5 * * * * cd /path/to/your/app && php artisan emails:check

# Or use the provided setup script
chmod +x setup_cron.sh
./setup_cron.sh
```

## 🔧 OAuth Setup

### Google OAuth Setup

1. **Create Google Cloud Project**

    - Go to [Google Cloud Console](https://console.cloud.google.com/)
    - Create a new project or select existing one

2. **Enable APIs**

    - Gmail API
    - Google Tasks API
    - Google Calendar API
    - Google+ API

3. **Create OAuth 2.0 Credentials**

    - Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"
    - Set application type to "Web application"
    - Add authorized redirect URIs:
        - `https://yourdomain.com/email-accounts/google/callback`
        - `https://yourdomain.com/email-accounts/google-tasks/callback`

4. **Configure Environment**
    - Copy Client ID and Client Secret to your `.env` file

### Microsoft OAuth Setup

1. **Register Application**

    - Go to [Microsoft Azure Portal](https://portal.azure.com/)
    - Register a new application

2. **Configure Permissions**

    - Add the following permissions:
        - `Mail.Read`
        - `Mail.Send`
        - `Tasks.ReadWrite`
        - `Calendars.ReadWrite`
        - `offline_access`

3. **Set Redirect URIs**

    - Add: `https://yourdomain.com/email-accounts/microsoft/callback`

4. **Configure Environment**
    - Copy Client ID and Client Secret to your `.env` file

### Google Gemini AI Setup

1. **Get API Key**

    - Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
    - Create an API key

2. **Configure Environment**
    - Add the API key to your `.env` file
    - Set `GEMINI_ENABLED=true`

## 📖 Usage Guide

### Getting Started

1. **Register/Login**

    - Create an account or log in to the system

2. **Connect Email Accounts**

    - Go to "Email Accounts" in settings
    - Click "Add Email Account"
    - Choose your email provider (Gmail, Outlook, or IMAP)
    - Complete OAuth authentication or enter IMAP details

3. **Set Up Email Rules (Optional)**

    - Go to "Email Rules" in settings
    - Create rules for automatic email processing

4. **Enable Push Notifications**

    - Click "Enable Notifications" in the PWA status component
    - Grant notification permission when prompted

5. **Install as PWA (Optional)**
    - Click the install button in the navigation or browser
    - The app will be installed on your device

### Managing Tickets

1. **View Tickets**

    - All tickets are displayed on the main tickets page
    - Use filters to find specific tickets
    - Search by subject, sender, or content

2. **Create Tasks from Tickets**

    - Open a ticket
    - Click "Create Google Task"
    - Fill in task details with the interactive calendar
    - Set priority and due date

3. **Reply to Tickets**
    - Open a ticket
    - Use the reply form
    - Include original email context if needed
    - Send reply through connected email account

### Calendar Management

1. **Sync Calendars**

    - Go to "Calendar Events" in the navigation
    - Click "Sync All Calendars" to sync with external calendars
    - View all events from connected calendar accounts

2. **Create Events**

    - Click "Create Event" in the calendar events page
    - Select the calendar account and specific calendar
    - Fill in event details including title, description, location, and time
    - Add attendees if needed

3. **Manage Events**

    - View event details with rich information
    - Edit events with automatic sync to external calendars
    - Delete events (syncs with external calendar)
    - Filter events by provider, date range, and status

### Email Signatures

1. **Access Signature Management**

    - Go to "Email Accounts" → "Manage Signatures"
    - Or click on an email account and use the "Email Signature" section

2. **Create Signatures**

    - Use the rich text editor for formatted signatures
    - Upload signature images (JPEG, PNG, JPG, GIF, max 2MB)
    - Preview how signatures will appear in emails
    - Save signatures per email account

3. **Manage Signatures**

    - Edit existing signatures
    - Remove text or image components
    - View signature status for all accounts

### AI Features

1. **Email Rewriting**

    - Use AI to rewrite email content
    - Improve tone and clarity
    - Generate professional responses

2. **Smart Summaries**

    - Get AI-generated summaries of long emails
    - Extract key points and action items

3. **Response Generation**
    - Generate context-aware email responses
    - Customize tone and style

### Google Tasks Integration

1. **Sync Tasks**

    - Click "Sync Tasks" to sync with Google Tasks
    - View all tasks in the tasks section

2. **Create Tasks**

    - Create tasks directly from tickets
    - Set due dates with interactive calendar
    - Assign priorities
    - Create parent-child task relationships

3. **Manage Tasks**

    - Mark tasks as complete
    - Edit task details with auto-save functionality
    - Organize by priority and due date
    - Duplicate tasks for similar workflows
    - Set up task hierarchies with parent and subtasks

4. **Auto-Save Feature**
    - Tasks automatically save as you type (every 2 seconds)
    - No need to manually save while editing
    - Draft changes are preserved even if you navigate away

## 🔄 Automated Email Processing

### Email Checking

The system automatically checks for new emails every 5 minutes (configurable via cron).

### Email Rules

Create rules to automatically:

-   Forward emails to specific addresses
-   Send auto-responses
-   Delete unwanted emails
-   Move emails to specific folders
-   Create tickets with specific status/priority

## 🎨 Customization

### Themes

-   Toggle between light and dark modes
-   Preferences are saved per user

### Email Templates

-   Customize email templates in `resources/views/emails/`
-   Modify auto-response templates

### Styling

-   Built with Tailwind CSS
-   Customize styles in `resources/css/app.css`

### PWA Customization

-   Update app icons in `public/images/`
-   Modify manifest in `public/manifest.json`
-   Customize service worker in `public/sw.js`

## 🚀 Deployment

### Production Setup

1. **Environment**

    ```bash
    # Set production environment
    APP_ENV=production
    APP_DEBUG=false
    ```

2. **Optimize**

    ```bash
    # Cache configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

3. **Web Server**

    - Configure Apache/Nginx to point to `public/` directory
    - Set up SSL certificates (required for PWA and push notifications)
    - Configure proper file permissions

4. **Database**
    - Use production database
    - Set up database backups
    - Configure connection pooling if needed

### Docker Deployment (Optional)

```bash
# Build and run with Docker
docker-compose up -d
```

## 🔧 Maintenance

### Regular Tasks

-   Monitor log files: `storage/logs/laravel.log`
-   Check email account connections
-   Review and update email rules
-   Monitor Google API quotas
-   Check push notification delivery
-   Monitor calendar sync status

### Troubleshooting

**Common Issues:**

1. **OAuth Authentication Errors**

    - Check OAuth credentials in `.env`
    - Verify redirect URIs match exactly
    - Ensure APIs are enabled in Google Cloud Console

2. **Email Sync Issues**

    - Check email account status
    - Verify IMAP/SMTP settings
    - Review email server logs

3. **Google Tasks Sync Issues**

    - Re-authenticate Google Tasks account
    - Check Google Tasks API quotas
    - Verify task list permissions

4. **Push Notification Issues**

    - Verify VAPID keys are correctly set
    - Check if HTTPS is enabled
    - Ensure notification permission is granted
    - Check browser console for errors

5. **Calendar Sync Issues**

    - Re-authenticate calendar accounts
    - Check calendar API permissions
    - Verify calendar IDs are correct

6. **PWA Installation Issues**
    - Ensure HTTPS is enabled
    - Check if app meets install criteria
    - Verify service worker is registered
    - Check manifest.json configuration

## 📝 API Documentation

The system includes RESTful APIs for:

-   Ticket management
-   Email account operations
-   Google Tasks integration
-   Calendar events management
-   Email rules management
-   Push notification subscriptions

API endpoints are available at `/api/` with proper authentication.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

-   Check the troubleshooting section above
-   Review the logs in `storage/logs/`
-   Check the setup documentation files:
    -   `PWA_SETUP.md` - Progressive Web App setup
    -   `PUSH_NOTIFICATIONS_SETUP.md` - Push notification configuration
    -   `SIGNATURE_SETUP.md` - Email signature management
    -   `EMAIL_CHECKING_SETUP.md` - Email checking configuration
-   Create an issue in the repository

## 🔄 Changelog

### Version 1.3.0

-   **Calendar Integration**: Full calendar management with Google, Microsoft, and iCal support
-   **Push Notifications**: Real-time notifications for new tickets with VAPID support
-   **Progressive Web App**: Installable PWA with offline support and service worker
-   **Email Signatures**: Rich text and image signature management per email account
-   **File Attachments**: Enhanced attachment handling with metadata support
-   **Enhanced Security**: Improved authorization and file validation

### Version 1.2.0

-   **Auto-Save Feature**: Added automatic draft saving for Google Tasks (saves every 2 seconds)
-   **Task Hierarchies**: Support for parent-child task relationships and subtasks
-   **Task Duplication**: Ability to duplicate existing tasks for similar workflows
-   **Enhanced Task Management**: Improved task editing interface with better validation
-   **Better Error Handling**: Improved error handling for Google Tasks operations

### Version 1.1.0

-   **Notes System**: Add internal and public notes to tickets
-   **Reply Management**: Enhanced reply functionality with email context
-   **Bulk Operations**: Bulk update ticket status and priority
-   **Advanced Search**: Improved search and filtering capabilities

### Version 1.0.0

-   Initial release with core email ticket management
-   Google Tasks integration
-   AI-powered email features
-   Multi-account email support

---

**Built with ❤️ using Laravel, Tailwind CSS, and Google AI**
