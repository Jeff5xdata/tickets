<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $preferences = $user->getPreferences();
        
        return view('settings.index', [
            'user' => $user,
            'preferences' => $preferences,
            'themes' => UserPreference::getAvailableThemes(),
            'languages' => UserPreference::getAvailableLanguages(),
            'timezones' => UserPreference::getAvailableTimezones(),
            'aiTones' => UserPreference::getAvailableAiTones(),
            'ticketStatuses' => UserPreference::getAvailableTicketStatuses(),
            'ticketPriorities' => UserPreference::getAvailableTicketPriorities(),
            'ticketDisplayFormats' => UserPreference::getAvailableTicketDisplayFormats(),
            'ticketSorts' => UserPreference::getAvailableTicketSorts(),
            'calendarViews' => UserPreference::getAvailableCalendarViews(),
            'dashboardLayouts' => UserPreference::getAvailableDashboardLayouts(),
            'taskSorts' => UserPreference::getAvailableTaskSorts(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $preferences = $user->getPreferences();

        $validated = $request->validate([
            // Appearance Settings
            'theme' => 'required|in:light,dark,system',
            'language' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:10',
            
            // Email Settings
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'desktop_notifications' => 'boolean',
            'email_check_frequency' => 'integer|min:1|max:1440',
            'auto_reply_enabled' => 'boolean',
            'auto_reply_message' => 'nullable|string|max:1000',
            'include_original_in_replies' => 'boolean',
            'mark_as_read_on_open' => 'boolean',
            
            // Ticket Settings
            'default_ticket_status' => 'required|string|max:20',
            'default_ticket_priority' => 'required|string|max:20',
            'auto_create_tasks' => 'boolean',
            'auto_assign_tickets' => 'boolean',
            'tickets_per_page' => 'integer|min:5|max:100',
            'show_ticket_preview' => 'boolean',
            'enable_ticket_search' => 'boolean',
            'show_closed_tickets' => 'boolean',
            'ticket_display_format' => 'required|in:html,plain_text',
            'ticket_sort' => 'required|string|max:20',
            
            // Calendar Settings
            'calendar_sync_enabled' => 'boolean',
            'default_calendar_view' => 'required|string|max:10',
            'show_calendar_events' => 'boolean',
            'auto_create_calendar_events' => 'boolean',
            'calendar_sync_frequency' => 'integer|min:1|max:1440',
            
            // Task Settings
            'google_tasks_sync' => 'boolean',
            'default_task_list' => 'nullable|string|max:255',
            'auto_save_tasks' => 'boolean',
            'task_auto_save_interval' => 'integer|min:1|max:60',
            'show_completed_tasks' => 'boolean',
            'task_sort' => 'required|string|max:20',
            
            // AI Settings
            'ai_rewriting_enabled' => 'boolean',
            'ai_summarization_enabled' => 'boolean',
            'ai_response_generation' => 'boolean',
            'ai_tone' => 'required|string|max:20',
            
            // Security Settings
            'two_factor_enabled' => 'boolean',
            'session_timeout_enabled' => 'boolean',
            'session_timeout_minutes' => 'integer|min:5|max:1440',
            'login_notifications' => 'boolean',
            
            // Display Settings
            'compact_mode' => 'boolean',
            'show_sidebar' => 'boolean',
            'show_breadcrumbs' => 'boolean',
            'dashboard_layout' => 'required|string|max:20',
            'show_activity_feed' => 'boolean',
            
            // Integration Settings
            'slack_integration' => 'boolean',
            'slack_webhook_url' => 'nullable|url|max:500',
            'webhook_notifications' => 'boolean',
            'webhook_url' => 'nullable|url|max:500',
            
            // Performance Settings
            'enable_caching' => 'boolean',
            'lazy_loading' => 'boolean',
            'preload_images' => 'boolean',
            
            // Privacy Settings
            'share_analytics' => 'boolean',
            'allow_cookies' => 'boolean',
            'show_online_status' => 'boolean',
        ]);

        // Convert checkbox values to boolean
        $booleanFields = [
            'email_notifications', 'push_notifications', 'desktop_notifications',
            'auto_reply_enabled', 'include_original_in_replies', 'mark_as_read_on_open',
            'auto_create_tasks', 'auto_assign_tickets', 'show_ticket_preview',
            'enable_ticket_search', 'calendar_sync_enabled', 'show_calendar_events',
            'auto_create_calendar_events', 'google_tasks_sync', 'auto_save_tasks',
            'show_completed_tasks', 'ai_rewriting_enabled', 'ai_summarization_enabled',
            'ai_response_generation', 'two_factor_enabled', 'session_timeout_enabled',
            'login_notifications', 'compact_mode', 'show_sidebar', 'show_breadcrumbs',
            'show_activity_feed', 'slack_integration', 'webhook_notifications',
            'enable_caching', 'lazy_loading', 'preload_images', 'share_analytics',
            'allow_cookies', 'show_online_status', 'show_closed_tickets'
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = isset($validated[$field]) && $validated[$field];
        }

        $preferences->update($validated);

        // If theme changed, update the cookie
        if (isset($validated['theme']) && $validated['theme'] !== 'system') {
            $cookieValue = $validated['theme'] === 'dark' ? 'true' : 'false';
            cookie()->queue('darkMode', $cookieValue, 60 * 24 * 365); // 1 year
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    public function resetToDefaults(): RedirectResponse
    {
        $user = auth()->user();
        $preferences = $user->getPreferences();

        // Reset to default values
        $preferences->update([
            'theme' => 'system',
            'language' => 'en',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'email_notifications' => true,
            'push_notifications' => true,
            'desktop_notifications' => true,
            'email_check_frequency' => 5,
            'auto_reply_enabled' => false,
            'auto_reply_message' => null,
            'include_original_in_replies' => true,
            'mark_as_read_on_open' => true,
            'default_ticket_status' => 'open',
            'default_ticket_priority' => 'medium',
            'auto_create_tasks' => false,
            'auto_assign_tickets' => false,
            'tickets_per_page' => 20,
            'show_ticket_preview' => true,
            'enable_ticket_search' => true,
            'show_closed_tickets' => false,
            'ticket_display_format' => 'html',
            'ticket_sort' => 'received_desc',
            'calendar_sync_enabled' => true,
            'default_calendar_view' => 'month',
            'show_calendar_events' => true,
            'auto_create_calendar_events' => false,
            'calendar_sync_frequency' => 15,
            'google_tasks_sync' => true,
            'default_task_list' => null,
            'auto_save_tasks' => true,
            'task_auto_save_interval' => 2,
            'show_completed_tasks' => false,
            'task_sort' => 'due_date_desc',
            'ai_rewriting_enabled' => true,
            'ai_summarization_enabled' => true,
            'ai_response_generation' => true,
            'ai_tone' => 'professional',
            'two_factor_enabled' => false,
            'session_timeout_enabled' => true,
            'session_timeout_minutes' => 120,
            'login_notifications' => true,
            'compact_mode' => false,
            'show_sidebar' => true,
            'show_breadcrumbs' => true,
            'dashboard_layout' => 'grid',
            'show_activity_feed' => true,
            'slack_integration' => false,
            'slack_webhook_url' => null,
            'webhook_notifications' => false,
            'webhook_url' => null,
            'enable_caching' => true,
            'lazy_loading' => true,
            'preload_images' => false,
            'share_analytics' => true,
            'allow_cookies' => true,
            'show_online_status' => true,
        ]);

        return back()->with('success', 'Settings reset to defaults successfully.');
    }

    public function export(): \Symfony\Component\HttpFoundation\Response
    {
        $user = auth()->user();
        $preferences = $user->getPreferences();

        $data = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
            'preferences' => $preferences->toArray(),
            'exported_at' => now()->toISOString(),
        ];

        $filename = 'user-settings-' . $user->id . '-' . now()->format('Y-m-d-H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json|max:1024',
        ]);

        try {
            $file = $request->file('settings_file');
            $data = json_decode($file->get(), true);

            if (!isset($data['preferences'])) {
                throw new \Exception('Invalid settings file format');
            }

            $user = auth()->user();
            $preferences = $user->getPreferences();

            // Only import preference fields, not user data
            $preferences->update($data['preferences']);

            return back()->with('success', 'Settings imported successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['settings_file' => 'Failed to import settings: ' . $e->getMessage()]);
        }
    }
}
