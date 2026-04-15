<?php 

namespace App\Services;

use App\Traits\UploadImageTrait;

class ProductImageService
{
    use UploadImageTrait;

    public function handleCreateImages($product, $request)
    {
        // thumbnail
        if ($request->hasFile('thumbnail')) {
            $path = $this->uploadImage($request->file('thumbnail'), 'products');

            $product->images()->create([
                'image' => $path,
                'type' => 'thumbnail',
                'alt' => $product->name
            ]);
        }

        // gallery
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $imageFile) {
                $path = $this->uploadImage($imageFile, 'products');

                $product->images()->create([
                    'image' => $path,
                    'type' => 'gallery',
                    'alt' => $product->name
                ]);
            }
        }
    }

    public function deleteImages($images)
    {
        foreach ($images as $img) {
            $this->deleteImage($img->image);
            $img->delete();
        }
    }
}