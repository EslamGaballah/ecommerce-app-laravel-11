<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Makes an active simple product by default.
 *
 * Product::factory()->variable()->create();
 * Product::factory()->onSale()->create();
 * Product::factory()->outOfStock()->create();
 * Product::factory()->draft()->create();
 *
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    private const SIZES = ['S', 'M', 'L', 'XL'];

    public function definition(): array
    {
        $name = Str::title(fake()->words(3, true));
        $number = fake()->unique()->numberBetween(1000, 999999);

        return [
            'name_en' => $name,
            'name_ar' => 'منتج '.$number,
            'slug' => Str::slug($name).'-'.$number,
            'sku' => 'SKU-'.$number,
            'description' => fake()->paragraph(),
            'status' => ProductStatus::Active,
            'product_type' => ProductType::SIMPLE,
            'price' => fake()->numberBetween(100, 2000),
            'compare_price' => null,
            'stock' => fake()->numberBetween(1, 100),
            'user_id' => User::factory(),
            'category_id' => fn () => Category::query()->value('id') ?? Category::create([
                'name_en' => 'General',
                'name_ar' => 'عام',
                'slug' => 'general',
            ])->id,
        ];
    }

    public function variable(int $variations = 3): static
    {
        return $this
            ->state(['product_type' => ProductType::VARIABLE, 'sku' => null, 'price' => null, 'stock' => null])
            ->afterCreating(function (Product $product) use ($variations) {
                $attribute = Attribute::firstOrCreate(['name_en' => 'Size'], ['name_ar' => 'المقاس']);

                foreach (array_slice(self::SIZES, 0, $variations) as $index => $size) {
                    $value = AttributeValue::firstOrCreate(['attribute_id' => $attribute->id, 'value' => $size]);

                    $product->variations()->create([
                        'price' => fake()->numberBetween(100, 2000),
                        'stock' => fake()->numberBetween(1, 100),
                        'sku' => Str::upper($product->slug.'-'.$size),
                        'is_primary' => $index === 0,
                    ])->values()->sync([$value->id]);
                }
            });
    }

    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'compare_price' => $attributes['price'] * 2,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }

    public function draft(): static
    {
        return $this->state(['status' => ProductStatus::Draft]);
    }
}
