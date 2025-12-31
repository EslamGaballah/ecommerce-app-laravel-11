<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent Categories
        $electronics = Category::create([
            'name' => 'Electronics',
             'slug' => 'electronics'
             ]);

        // Child Categories
        $mobiles = Category::create([
            'name' => 'Mobiles',
            'parent_id' => $electronics->id,
            'slug' => 'Mobiles'
        ]);

        // Grandchild
        Category::create([
            'name' => 'android',
            'parent_id' => $mobiles->id,
            'slug' => 'android'
        ]);

        // Grandchild
        Category::create([
            'name' => 'iPhone',
            'parent_id' => $mobiles->id,
            'slug' => 'iPhone'
        ]);

        // Parent Categories
        $fashion = Category::create([
            'name' => 'Fashion' ,
            'slug' => 'fashion'
        ]);

        // Child Categories
        $men = Category::create([
            'name' => 'Men ',
            'parent_id' => $fashion->id,
            'slug' => 'Men'
        ]);

        // Grandchild
        Category::create([
            'name' => 'men-clothes ',
            'parent_id' => $men->id,
            'slug' => 'mwn-clothes'
        ]);

        // Grandchild
        Category::create([
            'name' => 'shoes ',
            'parent_id' => $men->id,
            'slug' => 'shoes'
        ]);

        // Child Categories
        $women = Category::create([
            'name' => 'Women ',
            'parent_id' => $fashion->id,
            'slug' => 'Women'
        ]);

        // Grandchild
        Category::create([
            'name' => 'women-clothes ',
            'parent_id' => $women->id,
            'slug' => 'women-clothes'
        ]);
    }
    
}
