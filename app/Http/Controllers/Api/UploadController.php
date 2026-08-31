<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50MB max file size
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $allowedExtensions = [
            // Images
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg',
            // Audio
            'mp3', 'wav', 'ogg', 'm4a', 'aac',
            // Video
            'mp4', 'mov', 'avi', 'mkv', 'webm',
        ];

        if (! in_array($extension, $allowedExtensions)) {
            return response()->json([
                'message' => 'The uploaded file format is not supported.',
            ], 422);
        }

        $filename = Str::random(24) . '.' . $extension;
        $path = $file->storeAs('uploads', $filename, 'public');

        $url = asset('storage/' . $path);

        return response()->json([
            'url' => $url,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
        ], 201);
    }
}
