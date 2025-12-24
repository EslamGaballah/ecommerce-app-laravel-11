<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadImageTrait
{
    /**
     * Upload image to storage and return its path
     *
     * @param UploadedFile $image
     * @param string $folder
     * @return string|null
     */
    public function uploadImage(UploadedFile $image, string $folder = 'images')
    {   
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        //  storage/app/public/images
        return $image->storeAs($folder, $imageName,'public');
    }

    /**
     * Delete old image if exists
     */
    public function deleteImage(?string $path)
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}