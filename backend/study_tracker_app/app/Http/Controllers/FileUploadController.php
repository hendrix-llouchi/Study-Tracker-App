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

        $fileUpload = FileUpload::create([
            'user_id' => $request->user()->id,
            'file_name' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $request->file_type,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'storage_driver' => 'local', // TODO: Configure for S3/Cloudflare R2
        ]);

        return $this->successResponse(['file' => $fileUpload], 'File uploaded successfully', 201);
    }
}

