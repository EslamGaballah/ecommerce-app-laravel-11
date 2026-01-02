<?php

namespace App\Http\Controllers\Front;

use App\Models\Product;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
        public function index()
    {
        $favorites = auth()->user()
            ->favorites()
            ->with('category')
            ->paginate(10);

        return view('front.favorites.index', compact('favorites'));
    }

    public function store(Product $product)
    {
        auth()->user()->favorites()->syncWithoutDetaching($product->id);

        return back()->with('success', 'Added to favorites');
    }

    public function destroy(Product $product)
    {
        auth()->user()->favorites()->detach($product->id);

        return back()->with('success', 'Removed from favorites');
    }

}
