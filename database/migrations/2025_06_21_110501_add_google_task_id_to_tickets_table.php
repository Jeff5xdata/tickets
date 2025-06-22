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
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('google_task_id')->nullable()->after('email_account_id')->constrained('google_tasks')->onDelete('set null');
            $table->index('google_task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['google_task_id']);
            $table->dropIndex(['google_task_id']);
            $table->dropColumn('google_task_id');
        });
    }
};
