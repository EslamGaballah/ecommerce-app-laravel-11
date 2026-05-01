<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\SkuGenerator;

class ProductVariationService
{
    public function __construct(
        protected StockService $stockService,
        protected ProductImageService $imageService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | STORE Product Variations
    |--------------------------------------------------------------------------
    */
    public function store($request, Product $product)
    {
        foreach ($request->variations as $index => $varData) {

            $sku = blank($varData['sku'])
                ? SkuGenerator::generateForVariation($product, $varData['attribute_value_ids'])
                : $varData['sku'];

            $variation = $product->variations()->create([
                'price' => $varData['price'],
                'compare_price' => $varData['compare_price'] ?? null,
                'stock' => $varData['stock'],
                'sku' => $sku,
                'is_primary' => $request->primary == $index,
            ]);

            $variation->values()->sync($varData['attribute_value_ids']);

            $this->stockService->create($variation, $varData['stock']);

            $this->imageService->handleVariationImages($request, $variation, $index);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE OR CREATE Product variations
    |--------------------------------------------------------------------------
    */
    public function update($request, $product)
    {
        $existingIds = $product->variations()->pluck('id')->toArray();
        $incomingIds = [];

        foreach ($request->variations as $index => $varData) {

            $sku = blank($varData['sku'])
                ? \App\Services\SkuGenerator::generateForVariation($product, $varData['attribute_value_ids'])
                : $varData['sku'];

            /*
            |--------------------------------------------------------------------------
            | UPDATE OR CREATE
            |--------------------------------------------------------------------------
            */
            if (!empty($varData['id'])) {

                $variation = $product->variations()->find($varData['id']);

                $variation->update([
                    'price' => $varData['price'],
                    'compare_price' => $varData['compare_price'] ?? null,
                    'stock' => $varData['stock'],
                    'sku' => $sku,
                    'is_primary' => $request->primary == $index,
                ]);

            } else {

                $variation = $product->variations()->create([
                    'price' => $varData['price'],
                    'compare_price' => $varData['compare_price'] ?? null,
                    'stock' => $varData['stock'],
                    'sku' => $sku,
                    'is_primary' => $request->primary == $index,
                ]);

                $this->stockService->create($variation, $varData['stock']);
            }

            $incomingIds[] = $variation->id;

            // sync attributes
            $variation->values()->sync($varData['attribute_value_ids']);

            // images
            $this->imageService->handleVariationImages($request, $variation, $index);
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE REMOVED
        |--------------------------------------------------------------------------
        */
        $deletedIds = array_diff($existingIds, $incomingIds);

        if (!empty($deletedIds)) {
            $this->forceDeleteByIds($deletedIds);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Soft DELETE Variations
    |--------------------------------------------------------------------------
    */
    public function deleteAll($product)
    {
        foreach ($product->variations as $variation) {
            $variation->delete();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Soft DELETE Variations
    |--------------------------------------------------------------------------
    */
    public function forceDeleteAll($product)
    {
        foreach ($product->variations as $variation) {

            // delete images from storage
            foreach ($variation->images as $img) {
                $this->imageService->deleteImage($img->image);
            }

            $variation->images()->delete();
            $variation->values()->detach();
            $variation->delete();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE DELETE Variations FOR UPDATE
    |--------------------------------------------------------------------------
    */
    public function forceDeleteByIds(array $ids)
    {
        $variations = ProductVariation::with('images')->whereIn('id', $ids)->get();

        foreach ($variations as $variation) {

            foreach ($variation->images as $img) {
                $this->imageService->deleteImage($img->image);
            }

            $variation->images()->delete();
            $variation->values()->detach();
            $variation->delete();
        }
    }
}