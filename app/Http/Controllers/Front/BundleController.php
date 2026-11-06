<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Services\Bundle\BundleService;
use App\Services\Order\CartService;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public const CARD_RELATIONS = ['category', 'images', 'variations.images', 'variations.values.attribute', 'primaryVariation.images'];

    public function index()
    {
        return view('front.bundles.index', [
            'bundles' => Bundle::running()
                ->with(['tiers', 'products' => fn ($products) => $products->active()->with('images', 'primaryVariation.images', 'variations.images')])
                ->latest()
                ->get()
                ->filter(fn ($bundle) => $bundle->tiers->isNotEmpty() && $bundle->products->isNotEmpty()),
        ]);
    }

    public function show(Request $request, Bundle $bundle, BundleService $bundles, CartService $cart)
    {
        abort_unless(Bundle::running()->whereKey($bundle->id)->exists(), 404);

        $bundle->load('tiers');

        $bundle->load(['products' => fn ($products) => $products->active()->with(self::CARD_RELATIONS)]);

        $progress = $bundles->progressFor($bundle, $cart->items());

        // The page asks for just this part after a product is added from it.
        if ($request->ajax()) {
            return view('front.bundles._progress', compact('bundle', 'progress'))->render();
        }

        return view('front.bundles.show', compact('bundle', 'progress'));
    }
}
