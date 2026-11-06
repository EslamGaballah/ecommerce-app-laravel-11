<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
        public function store(Request $request, Product $product)
    {
        abort_unless(Setting::get('product_reviews_enabled') && Setting::get('product_ratings_visible'), 403);


        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);
// dd($request);
        Review::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ],
            [
                'rating' => $request->rating,
                'review' => $request->review,
            ]
        );
        // update product ratinsg in table product after review
        $product->update([
            'rating_avg'   => round($product->reviews()->avg('rating'), 1),
            'rating_count' => $product->reviews()->count(),
        ]);

        $product->refresh();

        return back()->with('success', __('app.review_added'));
    }
}
