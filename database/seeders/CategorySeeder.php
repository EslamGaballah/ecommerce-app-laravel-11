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
            'name_en' => 'Electronics',
            'name_ar' => 'الكترونيات',
             'slug' => 'electronics'
             ]);

        // Child Categories
        $mobiles = Category::create([
            'name_en' => 'Mobiles',
            'name_ar' => 'موبايلات',
            'parent_id' => $electronics->id,
            'slug' => 'Mobiles'
        ]);

        // Grandchild
        Category::create([
            'name_en' => 'android',
            'name_ar' => 'اندرويد',
            'parent_id' => $mobiles->id,
            'slug' => 'android'
        ]);

        // Grandchild
        Category::create([
            'name_en' => 'iPhone',
            'name_ar' => 'ايفون',
            'parent_id' => $mobiles->id,
            'slug' => 'iPhone'
        ]);

        // Parent Categories
        $fashion = Category::create([
            'name_en' => 'Fashion' ,
            'name_ar' => 'الموضة' ,
            'slug' => 'fashion'
        ]);

        // Child Categories
        $men = Category::create([
            'name_en' => 'Men ',
            'name_ar' => 'الرجال ',
            'parent_id' => $fashion->id,
            'slug' => 'Men'
        ]);

        // Grandchild
        Category::create([
            'name_en' => 'men clothes ',
            'name_ar' => 'ملابس رجالى ',
            'parent_id' => $men->id,
            'slug' => 'men-clothes'
        ]);

        // Grandchild
        Category::create([
            'name_en' => 'shoes ',
            'name_ar' => 'احذية ',
            'parent_id' => $men->id,
            'slug' => 'shoes'
        ]);

        // Child Categories
        $women = Category::create([
            'name_en' => 'Women ',
            'name_ar' => 'النساء ',
            'parent_id' => $fashion->id,
            'slug' => 'Women'
        ]);

        // Grandchild
        Category::create([
            'name_en' => 'women-clothes ',
            'name_ar' => 'ملابس المرأه',
            'parent_id' => $women->id,
            'slug' => 'women-clothes'
        ]);
    }
    
}
