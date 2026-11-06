<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->active()
            ->with(BundleController::CARD_RELATIONS)
            ->paginate(12);

        return view('front.favorites.index', compact('favorites'));
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();
        $favorited = ! $user->favorites()->whereKey($product->id)->exists();

        $favorited ? $user->favorites()->attach($product->id) : $user->favorites()->detach($product->id);

        $message = $favorited ? __('app.added_to_favorites') : __('app.removed_from_favorites');

        if ($request->expectsJson()) {
            return response()->json([
                'favorited' => $favorited,
                'count' => $user->favorites()->count(),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
