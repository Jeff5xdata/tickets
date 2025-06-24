<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        // Appearance Settings
        'theme',
        'language',
        'timezone',
        'date_format',
        'time_format',
        // Email Settings
        'email_notifications',
        'push_notifications',
        'desktop_notifications',
        'email_check_frequency',
        'auto_reply_enabled',
        'auto_reply_message',
        'include_original_in_replies',
        'mark_as_read_on_open',
        // Ticket Settings
        'default_ticket_status',
        'default_ticket_priority',
        'auto_create_tasks',
        'auto_assign_tickets',
        'tickets_per_page',
        'show_ticket_preview',
        'enable_ticket_search',
        'show_closed_tickets',
        'ticket_display_format',
        'ticket_sort',
        // Calendar Settings
        'calendar_sync_enabled',
        'default_calendar_view',
        'show_calendar_events',
        'auto_create_calendar_events',
        'calendar_sync_frequency',
        // Task Settings
        'google_tasks_sync',
        'default_task_list',
        'auto_save_tasks',
        'task_auto_save_interval',
        'show_completed_tasks',
        'task_sort',
        // AI Settings
        'ai_rewriting_enabled',
        'ai_summarization_enabled',
        'ai_response_generation',
        'ai_tone',
        // Security Settings
        'two_factor_enabled',
        'session_timeout_enabled',
        'session_timeout_minutes',
        'login_notifications',
        // Display Settings
        'compact_mode',
        'show_sidebar',
        'show_breadcrumbs',
        'dashboard_layout',
        'show_activity_feed',
        // Integration Settings
        'slack_integration',
        'slack_webhook_url',
        'webhook_notifications',
        'webhook_url',
        // Performance Settings
        'enable_caching',
        'lazy_loading',
        'preload_images',
        // Privacy Settings
        'share_analytics',
        'allow_cookies',
        'show_online_status',
    ];

    protected $casts = [
        // Email Settings
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'desktop_notifications' => 'boolean',
        'auto_reply_enabled' => 'boolean',
        'include_original_in_replies' => 'boolean',
        'mark_as_read_on_open' => 'boolean',
        // Ticket Settings
        'auto_create_tasks' => 'boolean',
        'auto_assign_tickets' => 'boolean',
        'show_ticket_preview' => 'boolean',
        'enable_ticket_search' => 'boolean',
        'show_closed_tickets' => 'boolean',
        // Calendar Settings
        'calendar_sync_enabled' => 'boolean',
        'show_calendar_events' => 'boolean',
        'auto_create_calendar_events' => 'boolean',
        // Task Settings
        'google_tasks_sync' => 'boolean',
        'auto_save_tasks' => 'boolean',
        'show_completed_tasks' => 'boolean',
        // AI Settings
        'ai_rewriting_enabled' => 'boolean',
        'ai_summarization_enabled' => 'boolean',
        'ai_response_generation' => 'boolean',
        // Security Settings
        'two_factor_enabled' => 'boolean',
        'session_timeout_enabled' => 'boolean',
        'login_notifications' => 'boolean',
        // Display Settings
        'compact_mode' => 'boolean',
        'show_sidebar' => 'boolean',
        'show_breadcrumbs' => 'boolean',
        'show_activity_feed' => 'boolean',
        // Integration Settings
        'slack_integration' => 'boolean',
        'webhook_notifications' => 'boolean',
        // Performance Settings
        'enable_caching' => 'boolean',
        'lazy_loading' => 'boolean',
        'preload_images' => 'boolean',
        // Privacy Settings
        'share_analytics' => 'boolean',
        'allow_cookies' => 'boolean',
        'show_online_status' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create user preferences for a user
     */
    public static function getForUser(User $user): self
    {
        return $user->preferences ?? $user->preferences()->create();
    }

    /**
     * Get theme setting with fallback
     */
    public function getThemeAttribute($value): string
    {
        return $value ?: 'system';
    }

    /**
     * Get timezone setting with fallback
     */
    public function getTimezoneAttribute($value): string
    {
        return $value ?: config('app.timezone', 'UTC');
    }

    /**
     * Get effective theme (resolves 'system' to actual theme)
     */
    public function getEffectiveTheme(): string
    {
        if ($this->theme === 'system') {
            return request()->cookie('darkMode') === 'true' ? 'dark' : 'light';
        }
        return $this->theme;
    }

    /**
     * Check if dark mode is enabled
     */
    public function isDarkMode(): bool
    {
        return $this->getEffectiveTheme() === 'dark';
    }

    /**
     * Get available themes
     */
    public static function getAvailableThemes(): array
    {
        return [
            'light' => 'Light',
            'dark' => 'Dark',
            'system' => 'System',
        ];
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages(): array
    {
        return [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'ja' => '日本語',
            'ko' => '한국어',
            'zh' => '中文',
        ];
    }

    /**
     * Get available timezones
     */
    public static function getAvailableTimezones(): array
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'America/Chicago' => 'Central Time',
            'America/Denver' => 'Mountain Time',
            'America/Los_Angeles' => 'Pacific Time',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
            'Australia/Sydney' => 'Sydney',
        ];
    }

    /**
     * Get available AI tones
     */
    public static function getAvailableAiTones(): array
    {
        return [
            'professional' => 'Professional',
            'casual' => 'Casual',
            'friendly' => 'Friendly',
            'formal' => 'Formal',
        ];
    }

    /**
     * Get available ticket statuses
     */
    public static function getAvailableTicketStatuses(): array
    {
        return [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'waiting' => 'Waiting',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
    }

    /**
     * Get available ticket priorities
     */
    public static function getAvailableTicketPriorities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    /**
     * Get available ticket display formats
     */
    public static function getAvailableTicketDisplayFormats(): array
    {
        return [
            'html' => 'HTML (Default)',
            'plain_text' => 'Plain Text',
        ];
    }

    /**
     * Get available calendar views
     */
    public static function getAvailableCalendarViews(): array
    {
        return [
            'month' => 'Month',
            'week' => 'Week',
            'day' => 'Day',
        ];
    }

    /**
     * Get available dashboard layouts
     */
    public static function getAvailableDashboardLayouts(): array
    {
        return [
            'grid' => 'Grid',
            'list' => 'List',
            'compact' => 'Compact',
        ];
    }

    /**
     * Get available task sort options
     */
    public static function getAvailableTaskSorts(): array
    {
        return [
            'due_date_desc' => 'Due Date (Newest First)',
            'due_date_asc' => 'Due Date (Oldest First)',
            'title_asc' => 'Title (A-Z)',
            'title_desc' => 'Title (Z-A)',
            'list_asc' => 'Task List (A-Z)',
            'list_desc' => 'Task List (Z-A)',
            'created_desc' => 'Created (Newest First)',
            'created_asc' => 'Created (Oldest First)',
        ];
    }

    /**
     * Get available ticket sort options
     */
    public static function getAvailableTicketSorts(): array
    {
        return [
            'received_desc' => 'Received Date (Newest First)',
            'received_asc' => 'Received Date (Oldest First)',
            'subject_asc' => 'Subject (A-Z)',
            'subject_desc' => 'Subject (Z-A)',
            'from_email_asc' => 'From Email (A-Z)',
            'from_email_desc' => 'From Email (Z-A)',
            'status_asc' => 'Status (A-Z)',
            'status_desc' => 'Status (Z-A)',
            'priority_asc' => 'Priority (Low to High)',
            'priority_desc' => 'Priority (High to Low)',
        ];
    }
}
