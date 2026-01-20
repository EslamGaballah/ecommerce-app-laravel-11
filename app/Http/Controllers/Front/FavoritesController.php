<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
        public function index()
    {
        $favorites = auth()->user()
            ->favorites()
            ->with('images')
            ->paginate(10);

        return view('front.favorites.index', compact('favorites'));
    }

    // public function store(Product $product)
    // {
    //     auth()->user()->favorites()->syncWithoutDetaching($product->id);

    //     return back()->with('success', 'Added to favorites');
    // }

    // public function destroy(Product $product)
    // {
    //     auth()->user()->favorites()->detach($product->id);

    //     return back()->with('success', 'Removed from favorites');
    // }

     public function toggle(Product $product)
    {
        $user = auth()->user();

        if ($user->favorites()->where('product_id', $product->id)->exists()) {
            $user->favorites()->detach($product->id);
            return back()->with('success', 'تم الحذف من المفضلة');
        }

        $user->favorites()->attach($product->id);
        return back()->with('success', 'تمت الإضافة إلى المفضلة');
    }

}
