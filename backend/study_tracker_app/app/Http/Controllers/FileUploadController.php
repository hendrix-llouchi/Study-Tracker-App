<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\FileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    use ApiResponse;

    /**
     * Upload file.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
            'file_type' => 'required|string|in:timetable,result,avatar',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads/' . $request->file_type, 'public');

        // Safely get MIME type - handle case where fileinfo extension is not available
        $mimeType = null;
        try {
            $mimeType = $file->getMimeType();
        } catch (\Exception $e) {
            // If fileinfo extension is not available, use a fallback based on file extension
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $this->getMimeTypeFromExtension($extension);
        }

        $fileUpload = FileUpload::create([
            'user_id' => $request->user()->id,
            'file_name' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $request->file_type,
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'storage_driver' => 'local', // TODO: Configure for S3/Cloudflare R2
        ]);

        return $this->successResponse(['file' => $fileUpload], 'File uploaded successfully', 201);
    }

    /**
     * Get MIME type from file extension (fallback when fileinfo extension is not available).
     */
    private function getMimeTypeFromExtension(string $extension): ?string
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
}

