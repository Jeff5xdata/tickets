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
        Schema::create('google_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('parent_task_id')->nullable()->constrained('google_tasks')->onDelete('set null');
            $table->string('google_task_id')->nullable(); // Google Tasks API ID
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->boolean('completed')->default(false);
            $table->string('list_id')->nullable(); // Google Tasks list ID
            $table->string('list_name')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamps();
            
            $table->index(['user_id', 'completed']);
            $table->index(['ticket_id']);
            $table->index('google_task_id');
            $table->index('parent_task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_tasks');
    }
};
