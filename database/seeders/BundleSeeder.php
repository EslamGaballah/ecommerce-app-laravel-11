<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\BundleTier;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Two sample bundles and a few featured products, built from the test products.
 * Run it with: php artisan db:seed --class=BundleSeeder
 *
 * It needs ProductSeeder. Running it again changes nothing that already exists.
 */
class BundleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBundle(
            ['slug' => 'fashion-set', 'name_ar' => 'باقة الموضة', 'name_en' => 'Fashion Set', 'description' => 'اختر من منتجات الموضة وكلما زادت القطع زاد الخصم.'],
            ['test-silk-scarf', 'test-leather-belt', 'test-men-t-shirt', 'test-running-shoes', 'test-sports-socks'],
            [[2, BundleTier::PERCENT, 10], [3, BundleTier::PERCENT, 15], [5, BundleTier::PERCENT, 25]],
        );

        $this->seedBundle(
            ['slug' => 'tech-trio', 'name_ar' => 'ثلاثي التقنية', 'name_en' => 'Tech Trio', 'description' => 'اختر 3 منتجات مختلفة واحصل على خصم ثابت.', 'distinct_products' => true],
            ['test-wireless-earbuds', 'test-smart-watch', 'test-android-phone', 'test-iphone'],
            [[3, BundleTier::FIXED, 500]],
        );

        Product::whereIn('slug', ['test-wireless-earbuds', 'test-smart-watch', 'test-men-t-shirt', 'test-running-shoes'])
            ->update(['is_featured' => true]);
    }

    private function seedBundle(array $attributes, array $productSlugs, array $tiers): void
    {
        if (Bundle::where('slug', $attributes['slug'])->exists()) {
            return;
        }

        $bundle = Bundle::create($attributes + ['is_active' => true]);

        $bundle->products()->sync(Product::whereIn('slug', $productSlugs)->pluck('id'));

        foreach ($tiers as [$quantity, $type, $value]) {
            $bundle->tiers()->create(['min_quantity' => $quantity, 'discount_type' => $type, 'discount_value' => $value]);
        }
    }
}
