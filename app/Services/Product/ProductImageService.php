<?php 

namespace App\Services\Product;

use App\Traits\UploadImageTrait;

class ProductImageService
{
    use UploadImageTrait;

    public function handleProductImages($request, $product)
    {
        // thumbnail
        if ($request->hasFile('thumbnail')) {
            $path = $this->uploadImage($request->file('thumbnail'), 'products');

            $product->images()->create([
                'image' => $path,
                'type' => 'thumbnail',
            ]);
        }

        // gallery
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $this->uploadImage($file, 'products');

                $product->images()->create([
                    'image' => $path,
                    'type' => 'gallery',
                ]);
            }
        }
    }

    public function handleVariationImages($request, $variation, $index)
    {
        $files = $request->file("variations.$index.images");

        if ($files) {
            foreach ($files as $file) {
                $path = $this->uploadImage($file, 'products');

                $variation->images()->create([
                    'image' => $path,
                ]);
            }
        }
    }

    public function updateProductImages($request, $product)
{
    /*
    |--------------------------------------------------------------------------
    | THUMBNAIL
    |--------------------------------------------------------------------------
    */
    if ($request->hasFile('thumbnail')) {

        $old = $product->images()->where('type', 'thumbnail')->first();

        if ($old) {
            $this->deleteImage($old->image);
            $old->delete();
        }

        $path = $this->uploadImage($request->file('thumbnail'), 'products');

        $product->images()->create([
            'image' => $path,
            'type' => 'thumbnail',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE SELECTED IMAGES
    |--------------------------------------------------------------------------
    */
    if ($request->filled('deleted_images')) {

        $ids = is_array($request->deleted_images)
            ? $request->deleted_images
            : explode(',', $request->deleted_images);

        $images = $product->images()->whereIn('id', $ids)->get();

        foreach ($images as $img) {
            $this->deleteImage($img->image);
            $img->delete();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADD NEW GALLERY
    |--------------------------------------------------------------------------
    */
    if ($request->hasFile('gallery')) {
        foreach ($request->file('gallery') as $file) {

            $path = $this->uploadImage($file, 'products');

            $product->images()->create([
                'image' => $path,
                'type' => 'gallery',
            ]);
        }
    }
}

    /*
    |--------------------------------------------------------------------------
    | DELETE PRODUCT IMAGES
    |--------------------------------------------------------------------------
    */
    public function deleteProductImages($product)
    {
        foreach ($product->images as $image) {
            $this->deleteImage($image->image);
        }

        $product->images()->delete();
    }
}