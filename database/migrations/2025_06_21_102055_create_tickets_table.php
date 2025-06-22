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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('email_account_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->text('original_content');
            $table->text('ai_rewritten_content')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->text('to_emails')->nullable(); // JSON array
            $table->text('cc_emails')->nullable(); // JSON array
            $table->text('bcc_emails')->nullable(); // JSON array
            $table->string('message_id')->nullable();
            $table->string('thread_id')->nullable();
            $table->enum('status', ['new', 'in_progress', 'waiting', 'resolved', 'closed'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('attachments')->nullable(); // JSON array
            $table->timestamp('received_at');
            $table->timestamp('responded_at')->nullable();
            $table->boolean('is_ai_rewritten')->default(false);
            $table->text('ai_prompt_used')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['email_account_id', 'received_at']);
            $table->index('thread_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
