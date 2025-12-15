<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show(string $filename)
    {
        $path = 'images/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            // Fallback: Pick a random image from the directory if the requested one doesn't exist
            // Fallback: Use a specific existing image to avoid expensive directory scanning
            // Using a known small image from the source list
            $path = 'images/pexels-imjimmyqian-2076596.jpg';
             
            // Double check it exists to avoid infinite loop (or just 404 if even fallback is missing)
            if (!Storage::disk('public')->exists($path)) {
                 abort(404, 'Image not found');
            }
        }
        
        $fullPath = Storage::disk('public')->path($path);
        $mimeType = mime_content_type($fullPath);
        $fileSize = filesize($fullPath);
        
        // Disable output buffering for streaming
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: public, max-age=31536000');
        header('Connection: close');
        
        // Stream file directly
        readfile($fullPath);
        
        exit;
    }
}


