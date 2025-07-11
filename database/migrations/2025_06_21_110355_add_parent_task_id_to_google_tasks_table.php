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
        Schema::table('google_tasks', function (Blueprint $table) {
            $table->foreignId('parent_task_id')->nullable()->after('ticket_id')->constrained('google_tasks')->onDelete('set null');
            $table->index('parent_task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_task_id']);
            $table->dropIndex(['parent_task_id']);
            $table->dropColumn('parent_task_id');
        });
    }
};
