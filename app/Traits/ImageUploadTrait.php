<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

trait ImageUploadTrait
{
    /**
     * Upload ảnh vào thư mục public/uploads
     */
    public function uploadImage($file, $directory = 'general')
    {
        if (!$file) return null;

        $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = public_path("uploads/{$directory}");

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $file->move($path, $imageName);

        return "uploads/{$directory}/{$imageName}";
    }

    /**
     * Xóa ảnh khỏi thư mục
     */
    public function deleteImage($path)
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}