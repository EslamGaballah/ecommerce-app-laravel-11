<?php

namespace App\Http\Controllers\Front;

use App\Filters\ProductFilter;
use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{

    public function index(Request $request) {
        
         $filters = new ProductFilter($request);

        $products = Product::with('category', 'variations')
            ->filter($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($request->ajax()) {
            return view('front.products._list', compact('products'))->render();
        }    

        $categories = Category::withCount('products')->get();
        $brands = Brand::withCount('products')->get();

        return view('front.products.index', compact('products', 'categories', 'brands'));
    }

    public function show( Product $product)
    {
        // refresh data to get latest ratings
        $product->refresh();

        $product->load([
            'category', 
            'variations.values.attribute', 
            'primaryVariation.values.attribute',
            'variations.images',
            'primaryVariation.images', 
            'reviews.user'
        ]); 

        $defaultVariation = $product->default_variation;

        $attributes = $product->variations
        ->flatMap->values
        ->groupBy('attribute_id')
        ->map(function ($values) {
        return [
            'id'     => $values->first()->attribute->id,
            'name'   => $values->first()->attribute->name,
            'values' => $values->unique('id')
        ];
    });


        $ratingsCount = $product->reviews()
        ->selectRaw('rating, COUNT(*) as count')
        ->groupBy('rating')
        ->pluck('count', 'rating');
         

        // $comments = $product->comments()->whereNull('parent_id')->with('children')->get();

        return view('front.products.show', compact('product', 'defaultVariation', 'attributes', 'ratingsCount' ));
    
    }


    public function match(Request $request, Product $product)
    {
       // attributes القادمة من الـ JS (attribute_id => value_id)
    $selected = collect($request->input('attributes', []));

    $variation = $product->variations->first(function ($variation) use ($selected) {

        // attribute_id => value_id الخاصة بالـ variation
        $variationValues = $variation->values
            ->pluck('id', 'attribute_id');

        // كل attributes المختارة لازم تطابق
        return $selected->every(function ($valueId, $attributeId) use ($variationValues) {
            return ($variationValues[$attributeId] ?? null) == $valueId;
        });
    });
        if (!$variation) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists'    => true,
            'id'        => $variation->id,
            'price'     => Currency::format($variation->price),
            'compare_price' => $variation->compare_price
                ? Currency::format($variation->compare_price)
                : null,
            'quantity'  => $variation->stock,
            'images'    => $variation->images->pluck('image')->values(),
            'available' => $variation->stock > 0,
        ]);
    }


}
