<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Download an attachment.
     */
    public function download(Attachment $attachment): StreamedResponse
    {
        // Check if the user has access to the attachment
        $this->authorizeAttachment($attachment);

        // Check if the file exists
        if (!$attachment->exists()) {
            abort(404, 'File not found');
        }

        // Get the file stream
        $stream = $attachment->getStream();

        // Return the file as a download
        return response()->stream(
            function () use ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 8192);
                }
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $attachment->original_name . '"',
                'Content-Length' => $attachment->size,
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache',
            ]
        );
    }

    /**
     * View an attachment (for images, PDFs, etc.).
     */
    public function view(Attachment $attachment): Response
    {
        // Check if the user has access to the attachment
        $this->authorizeAttachment($attachment);

        // Check if the file exists
        if (!$attachment->exists()) {
            abort(404, 'File not found');
        }

        // Get the file contents
        $contents = $attachment->getContents();

        // Return the file for viewing
        return response($contents, 200, [
            'Content-Type' => $attachment->mime_type,
            'Content-Length' => $attachment->size,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Get attachment info (for AJAX requests).
     */
    public function info(Attachment $attachment): \Illuminate\Http\JsonResponse
    {
        // Check if the user has access to the attachment
        $this->authorizeAttachment($attachment);

        return response()->json([
            'id' => $attachment->id,
            'original_name' => $attachment->original_name,
            'size' => $attachment->size,
            'formatted_size' => $attachment->formatted_size,
            'mime_type' => $attachment->mime_type,
            'extension' => $attachment->extension,
            'icon' => $attachment->icon,
            'is_image' => $attachment->isImage(),
            'is_pdf' => $attachment->isPdf(),
            'is_text' => $attachment->isText(),
            'exists' => $attachment->exists(),
            'created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
            'download_url' => route('attachments.download', $attachment),
            'view_url' => route('attachments.view', $attachment),
        ]);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Attachment $attachment): \Illuminate\Http\JsonResponse
    {
        // Check if the user has access to the attachment
        $this->authorizeAttachment($attachment);

        // Delete the attachment
        $attachment->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully'
        ]);
    }

    /**
     * Authorize access to the attachment.
     */
    protected function authorizeAttachment(Attachment $attachment): void
    {
        $attachable = $attachment->attachable;

        if (!$attachable) {
            abort(404, 'Attachment not found');
        }

        // Check if the user has access to the parent model
        if ($attachable instanceof \App\Models\Ticket) {
            $this->authorize('view', $attachable);
        } elseif ($attachable instanceof \App\Models\Reply) {
            $this->authorize('view', $attachable->ticket);
        } else {
            abort(403, 'Access denied');
        }
    }
}
