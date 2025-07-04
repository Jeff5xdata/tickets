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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('email_account_id')->constrained()->onDelete('cascade');
            $table->string('external_id')->nullable(); // Google/Microsoft event ID
            $table->string('calendar_id')->nullable(); // Calendar ID from provider
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->boolean('all_day')->default(false);
            $table->string('status')->default('confirmed'); // confirmed, tentative, cancelled
            $table->string('provider'); // google, microsoft, ical
            $table->json('attendees')->nullable(); // Array of attendee emails
            $table->json('recurrence')->nullable(); // Recurrence rules
            $table->string('color_id')->nullable(); // Calendar color
            $table->boolean('is_synced')->default(true); // Whether synced with external calendar
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'start_time']);
            $table->index(['email_account_id', 'external_id']);
            $table->index(['provider', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
}; 