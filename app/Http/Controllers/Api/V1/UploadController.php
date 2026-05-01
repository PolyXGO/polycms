<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload image for editor
     */
    public function image(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:10240'], // 10MB max
        ]);

        $file = $request->file('image');
        
        // Ensure uploads directory exists
        $uploadsDir = public_path('uploads');
        if (!File::exists($uploadsDir)) {
            File::makeDirectory($uploadsDir, 0755, true);
        }

        // Create year/month subdirectory
        $year = date('Y');
        $month = date('m');
        $subDir = "uploads/{$year}/{$month}";
        $fullPath = public_path($subDir);
        
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = Str::slug($originalName) . '-' . time() . '.' . $extension;
        
        // Move file to uploads directory
        $file->move($fullPath, $filename);
        
        // Get image dimensions
        $imagePath = $fullPath . '/' . $filename;
        $width = null;
        $height = null;
        
        if (file_exists($imagePath)) {
            $imageInfo = @getimagesize($imagePath);
            if ($imageInfo !== false) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        // Return URL relative to public
        $url = "/{$subDir}/{$filename}";

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'width' => $width,
                'height' => $height,
                'filename' => $filename,
            ],
        ]);
    }
}

