<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\ProductVariation;
use App\Models\Product;

class SkuGenerator
{
    public static function generateForSimple(Product $product): string
    {

        $base = Str::slug($product->name);
        do {
            $sku = strtoupper($base . '-' . rand(1000, 9999));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    public static function generateForVariation(Product $product, array $attributeIds): string
    {
        $attributeValues = \App\Models\AttributeValue::whereIn('id', $attributeIds)
            ->pluck('value')
            ->map(fn ($value) => Str::upper(Str::slug($value)))
            ->implode('-');

        $baseSku = strtoupper($product->slug . '-' . $attributeValues);

        $sku = $baseSku;
        $counter = 1;

        while (ProductVariation::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }
}
