<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => fake()->numberBetween(100, 200),
            'description'=> fake()->paragraph(),
            'compare_price' => fake()->numberBetween(201, 300),
            'quantity' => fake()->numberBetween(50, 200),
            'status' => fake()->randomElement(['active', 'draft', 'archived']),

            'user_id' => User::inRandomOrder()->first()?->id?? User::factory(),
            'category_id' =>Category::inRandomOrder()->first()?->id?? Category::factory(),
        ];
    }
}
