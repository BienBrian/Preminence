<?php

namespace App\Traits;

use Intervention\Image\Facades\Image;

trait CompressesImages
{
    /**
     * Compress and save an uploaded image.
     * Skips compression if file is under 500KB.
     * Resizes to max 1920px width, 80% JPEG quality.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $destinationPath
     * @param string $fileName
     * @return bool
     */
    protected function compressAndSave($file, $destinationPath, $fileName)
    {
        $fullPath = $destinationPath . '/' . $fileName;

        // If file is under 500KB, just move it without compression
        if ($file->getSize() < 512000) {
            return $file->move($destinationPath, $fileName) ? true : false;
        }

        $img = Image::make($file);

        // Resize if wider than 1920px, maintaining aspect ratio
        if ($img->width() > 1920) {
            $img->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Save with 80% quality
        $img->save($fullPath, 80);

        return file_exists($fullPath);
    }
}
