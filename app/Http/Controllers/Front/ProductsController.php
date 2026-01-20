<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{

    public function index(Request $request) {
        // $products = Product::with(['favoritedBy' => function($q) {
        //     $q->where('user_id', auth()->id());
        // }])->paginate(15);
        // $products = Product::paginate();
        $products = Product::query()
        ->byCategory($request->category_id)
        // ->byBrand($request->brand_id)
        ->byPriceRange($request->min_price, $request->max_price)
        ->sortBy($request->sort_by)
        ->paginate(4)
        ->withQueryString();        

        if ($request->ajax()) {
        return view('front.products._list', compact('products'))->render();
        }

        $categories = Category::withCount('products')->get();
        // $brands = Brand::all();

        // dd($categories);
        
        return view('front.products.index', compact('products' , 'categories'));
    }

    public function show( Product $product)
    {
        $product->load(['reviews.user']);

        $ratingsCount = $product->reviews()
        ->selectRaw('rating, COUNT(*) as count')
        ->groupBy('rating')
        ->pluck('count', 'rating');

        // $comments = $product->comments()->whereNull('parent_id')->with('children')->get();

        return view('front.products.show', compact('product', 'ratingsCount'));
        }
}
