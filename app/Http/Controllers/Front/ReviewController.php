<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product) 
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

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


        // return response()->json([
        //     'message' => 'تم إضافة التقييم بنجاح',
        // ]);

        return back()->with('success', 'تم إضافة التقييم بنجاح ⭐');
    }
}
