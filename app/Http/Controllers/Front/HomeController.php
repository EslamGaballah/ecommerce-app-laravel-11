<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;

class HomeController extends Controller
{
    private const CARD_RELATIONS = ['category', 'images', 'variations.images', 'variations.values.attribute', 'primaryVariation.images'];

    public function index()
    {
        // The biggest discounts lead the page; the rest appear under Special Offers.
        $deals = Product::with(self::CARD_RELATIONS)
            ->active()
            ->inStock()
            ->onSale()
            ->latest()
            ->limit(7)
            ->get()
            ->sortByDesc('discount_percent')
            ->values();

        $products = Product::with(self::CARD_RELATIONS)
            ->active()
            ->latest()
            ->limit(8)
            ->get();

        $homeCategories = Category::menuTree()
            ->take(6)
            ->each(function (Category $category) {
                $products = Product::active()->whereIn('category_id', $category->selfAndDescendantIds());

                $category->active_products_count = (clone $products)->count();
                $category->cover_image = (clone $products)
                    ->with(['images', 'variations.images', 'primaryVariation.images'])
                    ->latest()
                    ->first()
                    ?->image_url;
            });

        $brands = Brand::where('status', 'active')
            ->whereHas('products', fn ($products) => $products->active())
            ->limit(8)
            ->get();

                $featured = Product::with(self::CARD_RELATIONS)->active()->featured()->latest()->limit(4)->get();

        $bestSellers = Product::with(self::CARD_RELATIONS)->active()->bestSelling()->limit(4)->get();

        $mostViewed = Product::with(self::CARD_RELATIONS)->active()->mostViewed()->limit(4)->get();

        $bundles = Bundle::running()
            ->with(['tiers', 'products' => fn ($products) => $products->active()->with('images', 'primaryVariation.images', 'variations.images')])
            ->latest()
            ->limit(3)
            ->get()
            ->filter(fn ($bundle) => $bundle->tiers->isNotEmpty() && $bundle->products->isNotEmpty());

        $posts = Post::with('images')
            ->where('status', 'published')
            ->latest()
            ->limit(3)
            ->get();

        return view('front.home', [
            'heroProducts' => $deals->take(3),
            'offers' => $deals->slice(3),
            'products' => $products,
            'homeCategories' => $homeCategories,
            'brands' => $brands,
                        'posts' => $posts,
            'featured' => $featured,
            'bestSellers' => $bestSellers,
            'mostViewed' => $mostViewed,
            'bundles' => $bundles,
        ]);
    }
}
