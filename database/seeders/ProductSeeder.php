<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Twenty test products for local development: ten simple and ten variable.
 * Run it with: php artisan db:seed --class=ProductSeeder
 *
 * It needs the categories and the admin user from DatabaseSeeder. Running it again
 * adds no products and leaves prices and stock alone; it only refreshes the images.
 */
class ProductSeeder extends Seeder
{
    private const ATTRIBUTES = [
        'Color' => 'اللون',
        'Size' => 'المقاس',
        'Storage' => 'السعة',
    ];

    private const COLORS = [
        'Black' => '#334155',
        'White' => '#f8fafc',
        'Blue' => '#2563eb',
        'Red' => '#dc2626',
    ];

    public function run(): void
    {
        $admin = User::whereHas('roles', fn ($query) => $query->where('name', 'admin'))->firstOrFail();

        DB::transaction(function () use ($admin) {
            foreach ($this->products() as $data) {
                $product = $this->findOrCreateProduct($data, $admin);

                $this->saveProductImages($product, $data);

                foreach ($data['variations'] ?? [] as $index => $variation) {
                    $this->seedVariation($product, $data, $variation, $index, $admin);
                }
            }
        });
    }

    /**
     * A product with "variations" is variable. Each variation is
     * [attribute values, price, compare price, stock], and the first one is the primary.
     */
    private function products(): array
    {
        return [
            ['en' => 'Test Wireless Earbuds', 'ar' => 'سماعات لاسلكية تجريبية', 'category' => 'electronics', 'brand' => 'electronics', 'sku' => 'TEST-EARBUDS', 'image' => 'earbuds', 'color' => '#f8fafc', 'price' => 750, 'compare' => 900, 'stock' => 40],
            ['en' => 'Test Smart Watch', 'ar' => 'ساعة ذكية تجريبية', 'category' => 'electronics', 'brand' => 'electronics', 'sku' => 'TEST-WATCH', 'image' => 'watch', 'color' => '#94a3b8', 'price' => 1800, 'compare' => null, 'stock' => 15],
            ['en' => 'Test Leather Belt', 'ar' => 'حزام جلد تجريبي', 'category' => 'men-clothes', 'brand' => 'fashion', 'sku' => 'TEST-BELT', 'image' => 'belt', 'color' => '#92400e', 'price' => 350, 'compare' => 450, 'stock' => 25],
            ['en' => 'Test Sports Socks', 'ar' => 'جوارب رياضية تجريبية', 'category' => 'shoes', 'brand' => 'fashion', 'sku' => 'TEST-SOCKS', 'image' => 'socks', 'color' => '#14b8a6', 'price' => 90, 'compare' => null, 'stock' => 3],
            // Out of stock on purpose.
            ['en' => 'Test Silk Scarf', 'ar' => 'وشاح حرير تجريبي', 'category' => 'women-clothes', 'brand' => 'fashion', 'sku' => 'TEST-SCARF', 'image' => 'scarf', 'color' => '#db2777', 'price' => 450, 'compare' => 600, 'stock' => 0],

            ['en' => 'Test Android Phone', 'ar' => 'هاتف اندرويد تجريبي', 'category' => 'android', 'brand' => 'electronics', 'sku' => 'TEST-ANDROID', 'image' => 'phone-android', 'color' => '#475569', 'variations' => [
                [['Storage' => '128GB'], 9000, 10000, 12],
                [['Storage' => '256GB'], 11000, null, 8],
            ]],
            ['en' => 'Test iPhone', 'ar' => 'ايفون تجريبي', 'category' => 'iPhone', 'brand' => 'electronics', 'sku' => 'TEST-IPHONE', 'image' => 'phone-back', 'color' => '#475569', 'variations' => [
                [['Storage' => '128GB', 'Color' => 'Black'], 30000, 33000, 6],
                [['Storage' => '128GB', 'Color' => 'Blue'], 30000, 33000, 4],
                [['Storage' => '256GB', 'Color' => 'Black'], 35000, null, 5],
                [['Storage' => '256GB', 'Color' => 'Blue'], 35000, null, 2],
            ]],
            ['en' => 'Test Men T-Shirt', 'ar' => 'تيشيرت رجالي تجريبي', 'category' => 'men-clothes', 'brand' => 'fashion', 'sku' => 'TEST-TSHIRT', 'image' => 'tshirt', 'color' => '#475569', 'variations' => [
                [['Size' => 'M', 'Color' => 'Black'], 300, 400, 20],
                [['Size' => 'M', 'Color' => 'White'], 300, 400, 15],
                [['Size' => 'L', 'Color' => 'Black'], 320, null, 10],
                [['Size' => 'L', 'Color' => 'White'], 320, null, 7],
            ]],
            // The largest size is out of stock on purpose.
            ['en' => 'Test Running Shoes', 'ar' => 'حذاء جري تجريبي', 'category' => 'shoes', 'brand' => 'fashion', 'sku' => 'TEST-SHOES', 'image' => 'shoes', 'color' => '#ea580c', 'variations' => [
                [['Size' => '41'], 1200, 1500, 9],
                [['Size' => '42'], 1200, 1500, 6],
                [['Size' => '43'], 1250, null, 0],
            ]],
            ['en' => 'Test Women Dress', 'ar' => 'فستان نسائي تجريبي', 'category' => 'women-clothes', 'brand' => 'fashion', 'sku' => 'TEST-DRESS', 'image' => 'dress', 'color' => '#dc2626', 'variations' => [
                [['Size' => 'S', 'Color' => 'Red'], 850, 1000, 11],
                [['Size' => 'S', 'Color' => 'Blue'], 850, 1000, 5],
                [['Size' => 'M', 'Color' => 'Red'], 900, null, 8],
                [['Size' => 'M', 'Color' => 'Blue'], 900, null, 1],
            ]],

            ['en' => 'Test Sport Earbuds', 'ar' => 'سماعات رياضية تجريبية', 'category' => 'electronics', 'brand' => 'electronics', 'sku' => 'TEST-SPORT-EARBUDS', 'image' => 'earbuds', 'color' => '#0d9488', 'price' => 480, 'compare' => 600, 'stock' => 30],
            ['en' => 'Test Classic Watch', 'ar' => 'ساعة كلاسيكية تجريبية', 'category' => 'electronics', 'brand' => 'electronics', 'sku' => 'TEST-CLASSIC-WATCH', 'image' => 'watch', 'color' => '#92400e', 'price' => 1250, 'stock' => 12],
            ['en' => 'Test Canvas Belt', 'ar' => 'حزام قماش تجريبي', 'category' => 'men-clothes', 'brand' => 'fashion', 'sku' => 'TEST-CANVAS-BELT', 'image' => 'belt', 'color' => '#475569', 'price' => 180, 'compare' => 240, 'stock' => 40],
            ['en' => 'Test Wool Socks', 'ar' => 'جوارب صوف تجريبية', 'category' => 'shoes', 'brand' => 'fashion', 'sku' => 'TEST-WOOL-SOCKS', 'image' => 'socks', 'color' => '#7c3aed', 'price' => 120, 'stock' => 4],
            ['en' => 'Test Cotton Scarf', 'ar' => 'وشاح قطن تجريبي', 'category' => 'women-clothes', 'brand' => 'fashion', 'sku' => 'TEST-COTTON-SCARF', 'image' => 'scarf', 'color' => '#0284c7', 'price' => 260, 'compare' => 320, 'stock' => 18],

            ['en' => 'Test Budget Phone', 'ar' => 'هاتف اقتصادي تجريبي', 'category' => 'android', 'brand' => 'electronics', 'sku' => 'TEST-BUDGET-PHONE', 'image' => 'phone-android', 'color' => '#059669', 'variations' => [
                [['Storage' => '128GB'], 5200, 5800, 14],
                [['Storage' => '256GB'], 6100, null, 9],
            ]],
            ['en' => 'Test Fitness Watch', 'ar' => 'ساعة رياضية تجريبية', 'category' => 'electronics', 'brand' => 'electronics', 'sku' => 'TEST-FITNESS-WATCH', 'image' => 'watch', 'color' => '#334155', 'variations' => [
                [['Color' => 'Black'], 1500, 1800, 10],
                [['Color' => 'Blue'], 1500, 1800, 6],
                [['Color' => 'Red'], 1550, null, 3],
            ]],
            ['en' => 'Test Polo Shirt', 'ar' => 'قميص بولو تجريبي', 'category' => 'men-clothes', 'brand' => 'fashion', 'sku' => 'TEST-POLO', 'image' => 'tshirt', 'color' => '#2563eb', 'variations' => [
                [['Size' => 'M', 'Color' => 'Blue'], 420, 500, 16],
                [['Size' => 'M', 'Color' => 'Red'], 420, 500, 11],
                [['Size' => 'L', 'Color' => 'Blue'], 440, null, 8],
                [['Size' => 'L', 'Color' => 'Red'], 440, null, 2],
            ]],
            ['en' => 'Test Walking Shoes', 'ar' => 'حذاء مشي تجريبي', 'category' => 'shoes', 'brand' => 'fashion', 'sku' => 'TEST-WALKING', 'image' => 'shoes', 'color' => '#0f766e', 'variations' => [
                [['Size' => '41'], 950, null, 13],
                [['Size' => '42'], 950, null, 7],
                [['Size' => '43'], 980, null, 5],
            ]],
            ['en' => 'Test Summer Dress', 'ar' => 'فستان صيفي تجريبي', 'category' => 'women-clothes', 'brand' => 'fashion', 'sku' => 'TEST-SUMMER-DRESS', 'image' => 'dress', 'color' => '#db2777', 'variations' => [
                [['Size' => 'S', 'Color' => 'White'], 720, 900, 9],
                [['Size' => 'S', 'Color' => 'Black'], 720, 900, 6],
                [['Size' => 'M', 'Color' => 'White'], 760, null, 4],
                [['Size' => 'M', 'Color' => 'Black'], 760, null, 3],
            ]],
        ];
    }

    private function findOrCreateProduct(array $data, User $admin): Product
    {
        $slug = Str::slug($data['en']);
        $existing = Product::withTrashed()->where('slug', $slug)->first();

        if ($existing) {
            return $existing;
        }

        $isVariable = isset($data['variations']);

        $brand = Brand::firstOrCreate(
            ['name_en' => 'Test '.Str::title($data['brand'])],
            ['name_ar' => $data['brand'] === 'electronics' ? 'الكترونيات تجريبية' : 'موضة تجريبية', 'status' => 'active'],
        );

        $product = Product::create([
            'name_en' => $data['en'],
            'name_ar' => $data['ar'],
            'slug' => $slug,
            'description' => "{$data['en']} is a sample product for testing the store.",
            'status' => ProductStatus::Active,
            'product_type' => $isVariable ? ProductType::VARIABLE : ProductType::SIMPLE,
            'sku' => $isVariable ? null : $data['sku'],
            'price' => $data['price'] ?? null,
            'compare_price' => $data['compare'] ?? null,
            'stock' => $data['stock'] ?? null,
            'category_id' => Category::where('slug', $data['category'])->firstOrFail()->id,
            'brand_id' => $brand->id,
            'user_id' => $admin->id,
        ]);

        if (! $isVariable) {
            $this->recordStock($product, $data['stock'], $admin);
        }

        return $product;
    }

    private function seedVariation(Product $product, array $data, array $variation, int $index, User $admin): void
    {
        [$values, $price, $compare, $stock] = $variation;

        $label = implode(' ', $values);
        $sku = $data['sku'].'-'.Str::upper(Str::slug($label));

        $model = $product->variations()->where('sku', $sku)->first();

        if (! $model && $product->wasRecentlyCreated) {
            $model = $product->variations()->create([
                'price' => $price,
                'compare_price' => $compare,
                'stock' => $stock,
                'sku' => $sku,
                'is_primary' => $index === 0,
            ]);
            $model->values()->sync($this->attributeValueIds($values));
            $this->recordStock($model, $stock, $admin);
        }

        // Skips a variation someone removed from the dashboard.
        if ($model) {
            $this->saveImage($model, $product->slug.'-'.Str::slug($label), $data['image'], [
                'main' => self::COLORS[$values['Color'] ?? ''] ?? $data['color'],
                'label' => $label,
            ]);
        }
    }

    private function saveProductImages(Product $product, array $data): void
    {
        $this->saveImage($product, $product->slug, $data['image'], ['main' => $data['color']], 'thumbnail');
        $this->saveImage($product, $product->slug.'-gallery', $data['image'], ['main' => $data['color'], 'background' => '#1e293b'], 'gallery');
    }

    private function saveImage(Model $owner, string $name, string $drawing, array $options, ?string $type = null): void
    {
        $path = "products/test/{$name}.svg";

        Storage::disk('public')->put($path, $this->renderImage($drawing, $options));

        $owner->images()->firstOrCreate(['image' => $path], ['type' => $type]);
    }

    /**
     * Colors one of the drawings in database/seeders/images, so test products need no photo files.
     */
    private function renderImage(string $drawing, array $options): string
    {
        $background = $options['background'] ?? '#e2e8f0';
        $main = $options['main'];
        $label = e($options['label'] ?? '');
        $labelColor = $background === '#e2e8f0' ? '#1e293b' : '#f8fafc';

        $body = strtr(file_get_contents(__DIR__."/images/{$drawing}.svg.stub"), [
            '{main}' => $main,
            '{dark}' => $this->darken($main),
        ]);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
        <rect width="600" height="600" fill="{$background}"/>
        <circle cx="300" cy="290" r="255" fill="#ffffff" opacity="0.35"/>
        {$body}
        <text x="300" y="575" fill="{$labelColor}" font-family="sans-serif" font-size="26" font-weight="600" text-anchor="middle">{$label}</text>
        </svg>
        SVG;
    }

    private function darken(string $hex): string
    {
        return '#'.collect(str_split(ltrim($hex, '#'), 2))
            ->map(fn ($channel) => str_pad(dechex((int) (hexdec($channel) * 0.62)), 2, '0', STR_PAD_LEFT))
            ->implode('');
    }

    private function attributeValueIds(array $values): array
    {
        return collect($values)->map(function ($value, $attributeName) {
            $attribute = Attribute::firstOrCreate(
                ['name_en' => $attributeName],
                ['name_ar' => self::ATTRIBUTES[$attributeName]],
            );

            return AttributeValue::firstOrCreate(['attribute_id' => $attribute->id, 'value' => $value])->id;
        })->values()->all();
    }

    private function recordStock(Model $stockable, int $stock, User $admin): void
    {
        $stockable->stockMovements()->create([
            'stock' => $stock,
            'type' => 'in',
            'reason' => 'Initial stock',
            'user_id' => $admin->id,
        ]);
    }
}
