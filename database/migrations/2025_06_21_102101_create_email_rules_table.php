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
        Schema::create('email_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('email_account_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('action', ['auto_reply', 'delete', 'move_to_folder', 'mark_as_read', 'forward', 'create_task']);
            $table->enum('condition_type', ['from_email', 'to_email', 'subject_contains', 'body_contains', 'has_attachment', 'priority']);
            $table->string('condition_value');
            $table->text('auto_reply_message')->nullable();
            $table->string('forward_to')->nullable();
            $table->string('move_to_folder_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority_order')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['email_account_id', 'priority_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_rules');
    }
};
