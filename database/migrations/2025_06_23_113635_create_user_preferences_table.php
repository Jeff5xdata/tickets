<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Appearance Settings
            $table->string('theme')->default('system'); // light, dark, system
            $table->string('language')->default('en');
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i');
            
            // Email Settings
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('desktop_notifications')->default(true);
            $table->integer('email_check_frequency')->default(5); // minutes
            $table->boolean('auto_reply_enabled')->default(false);
            $table->text('auto_reply_message')->nullable();
            $table->boolean('include_original_in_replies')->default(true);
            $table->boolean('mark_as_read_on_open')->default(true);
            
            // Ticket Settings
            $table->string('default_ticket_status')->default('open');
            $table->string('default_ticket_priority')->default('medium');
            $table->boolean('auto_create_tasks')->default(false);
            $table->boolean('auto_assign_tickets')->default(false);
            $table->integer('tickets_per_page')->default(20);
            $table->boolean('show_ticket_preview')->default(true);
            $table->boolean('enable_ticket_search')->default(true);
            
            // Calendar Settings
            $table->boolean('calendar_sync_enabled')->default(true);
            $table->string('default_calendar_view')->default('month'); // month, week, day
            $table->boolean('show_calendar_events')->default(true);
            $table->boolean('auto_create_calendar_events')->default(false);
            $table->integer('calendar_sync_frequency')->default(15); // minutes
            
            // Task Settings
            $table->boolean('google_tasks_sync')->default(true);
            $table->string('default_task_list')->nullable();
            $table->boolean('auto_save_tasks')->default(true);
            $table->integer('task_auto_save_interval')->default(2); // seconds
            $table->boolean('show_completed_tasks')->default(false);
            
            // AI Settings
            $table->boolean('ai_rewriting_enabled')->default(true);
            $table->boolean('ai_summarization_enabled')->default(true);
            $table->boolean('ai_response_generation')->default(true);
            $table->string('ai_tone')->default('professional'); // professional, casual, friendly, formal
            
            // Security Settings
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('session_timeout_enabled')->default(true);
            $table->integer('session_timeout_minutes')->default(120);
            $table->boolean('login_notifications')->default(true);
            
            // Display Settings
            $table->boolean('compact_mode')->default(false);
            $table->boolean('show_sidebar')->default(true);
            $table->boolean('show_breadcrumbs')->default(true);
            $table->string('dashboard_layout')->default('grid'); // grid, list, compact
            $table->boolean('show_activity_feed')->default(true);
            
            // Integration Settings
            $table->boolean('slack_integration')->default(false);
            $table->string('slack_webhook_url')->nullable();
            $table->boolean('webhook_notifications')->default(false);
            $table->string('webhook_url')->nullable();
            
            // Performance Settings
            $table->boolean('enable_caching')->default(true);
            $table->boolean('lazy_loading')->default(true);
            $table->boolean('preload_images')->default(false);
            
            // Privacy Settings
            $table->boolean('share_analytics')->default(true);
            $table->boolean('allow_cookies')->default(true);
            $table->boolean('show_online_status')->default(true);
            
            $table->timestamps();
            
            // Ensure one preference record per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
