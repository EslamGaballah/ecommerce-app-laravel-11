<?php

namespace App\Services;

use App\Models\Product;
use App\Services\SkuGenerator;

class ProductVariationService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function createVariations(Product $product, $request)
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

            // attributes
            $variation->values()->sync($varData['attribute_value_ids']);

            // stock
            $this->stockService->createStock($variation, $varData['stock']);

            // images
            $files = $request->file("variations.$index.images");

            if ($files) {
                foreach ($files as $imageFile) {
                    $path = app(ProductImageService::class)
                        ->uploadImage($imageFile, 'products');

                    $variation->images()->create([
                        'image' => $path,
                        'type' => 'variation',
                        'alt' => $product->name . ' - ' . $sku
                    ]);
                }
            }
        }
    }
}