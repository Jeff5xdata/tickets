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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // This allows attachments to belong to tickets or replies
            $table->string('original_name'); // Original filename
            $table->string('stored_name'); // Name as stored on disk
            $table->string('mime_type');
            $table->bigInteger('size'); // File size in bytes
            $table->string('disk')->default('local'); // Storage disk
            $table->string('path'); // Path within the disk
            $table->json('metadata')->nullable(); // Additional metadata (content_id, is_inline, etc.)
            $table->timestamps();
            
            // Indexes for performance
            $table->index('stored_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
