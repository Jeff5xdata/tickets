<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size',
        'disk',
        'path',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
    ];

    /**
     * Get the parent attachable model (ticket or reply).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if the file exists on disk.
     */
    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    /**
     * Get the file contents.
     */
    public function getContents(): string
    {
        return Storage::disk($this->disk)->get($this->path);
    }

    /**
     * Get the file stream for download.
     */
    public function getStream()
    {
        return Storage::disk($this->disk)->readStream($this->path);
    }

    /**
     * Get the file URL (if using public disk).
     */
    public function getUrl(): ?string
    {
        if ($this->disk === 'public') {
            return Storage::disk($this->disk)->url($this->path);
        }
        return null;
    }

    /**
     * Check if this is an image file.
     */
    public function isImage(): bool
    {
        return Str::startsWith($this->mime_type, 'image/');
    }

    /**
     * Check if this is a PDF file.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if this is a text file.
     */
    public function isText(): bool
    {
        return Str::startsWith($this->mime_type, 'text/');
    }

    /**
     * Get the appropriate icon for the file type.
     */
    public function getIconAttribute(): string
    {
        if ($this->isImage()) {
            return 'image';
        } elseif ($this->isPdf()) {
            return 'pdf';
        } elseif ($this->isText()) {
            return 'document-text';
        } elseif (Str::contains($this->mime_type, 'spreadsheet') || in_array($this->extension, ['xlsx', 'xls', 'csv'])) {
            return 'table-cells';
        } elseif (Str::contains($this->mime_type, 'presentation') || in_array($this->extension, ['pptx', 'ppt'])) {
            return 'presentation-chart-line';
        } elseif (Str::contains($this->mime_type, 'word') || in_array($this->extension, ['docx', 'doc'])) {
            return 'document';
        } elseif (Str::contains($this->mime_type, 'zip') || in_array($this->extension, ['zip', 'rar', '7z'])) {
            return 'archive-box';
        } else {
            return 'document';
        }
    }

    /**
     * Store a file and create an attachment record.
     */
    public static function storeFile($file, $attachable, array $metadata = []): self
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storedName = uniqid() . '_' . time() . '.' . $extension;
        $path = 'attachments/' . date('Y/m/d') . '/' . $storedName;

        // Store the file
        Storage::disk('local')->put($path, file_get_contents($file));

        // Create the attachment record
        return self::create([
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => 'local',
            'path' => $path,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Store file content and create an attachment record.
     */
    public static function storeContent(string $content, string $originalName, string $mimeType, $attachable, array $metadata = []): self
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $storedName = uniqid() . '_' . time() . '.' . $extension;
        $path = 'attachments/' . date('Y/m/d') . '/' . $storedName;

        // Store the file
        Storage::disk('local')->put($path, $content);

        // Create the attachment record
        return self::create([
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'mime_type' => $mimeType,
            'size' => strlen($content),
            'disk' => 'local',
            'path' => $path,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Delete the file and the record.
     */
    public function delete(): bool
    {
        // Delete the file from storage
        if ($this->exists()) {
            Storage::disk($this->disk)->delete($this->path);
        }

        // Delete the record
        return parent::delete();
    }
}
