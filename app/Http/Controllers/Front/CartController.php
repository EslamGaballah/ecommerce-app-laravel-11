<?php

namespace App\Http\Controllers\Front;


use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Bundle\BundleService;
use App\Services\Order\CartService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    protected $cart;

     public function __construct(CartService  $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

     session()->forget('coupon_id');
     
        return view('front.cart', [
            'cart' => $this->cart,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'int', 'exists:products,id'],
            'quantity' => ['nullable', 'int', 'min:1', 'max:65535'], // quantity default(1)
            'variation_id' => [
                'nullable',
                Rule::exists('product_variations', 'id')->where('product_id', $request->input('product_id')),
            ],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $this->cart->add($product, $validated['quantity'] ?? 1, $validated['variation_id'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('app.added_to_cart'),
                'count' => $this->cart->count(),
                'total' => Currency::format($this->cart->total()),
            ], 201);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', __('app.added_to_cart'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => ['required', 'int', 'min:1', 'max:65535'],
        ]);

        $item = $this->cart->update($id, $request->quantity);

        $price = $item->variation?->price ?? $item->product->price;

        return response()->json([
            'success' => true,
            'item_total' => Currency::format($item->quantity * $price),
            ...$this->totals(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->cart->delete($id);

        return response()->json([
            'success' => true,
            ...$this->totals(),
        ]);
    }

    /** The figures the cart page redraws after a quantity changes. */
    private function totals(): array
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->total();
        $bundles = app(BundleService::class);
        $discount = $bundles->calculate($items)['discount'];

        return [
            'cart_subtotal' => Currency::format($subtotal),
            'cart_total' => Currency::format(max($subtotal - $discount, 0)),
            'bundle_discount' => $discount > 0 ? Currency::format($discount) : null,
            'bundle_progress' => view('front.bundles._cart-progress', ['progress' => $bundles->progress($items)])->render(),
        ];
    }

    public function empty(Request $request)
    {
        $this->cart->empty();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('app.cart_cleared')
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', __('app.cart_cleared'));
    }

    public function cartJson()
    {
        $items = $this->cart->items();

        $total = $this->cart->total();

        $data = $items->map(function ($item) {

            $price = $item->variation?->price ?? $item->product->price;

            return [
                'id' => $item->id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'quantity' => $item->quantity,
                'price' => Currency::format($price),
                'total' => Currency::format($price * $item->quantity),
                'image' => asset('storage/' . ($item->variation?->image ?? $item->product->images->first()?->image)),
            ];
        });

        return response()->json([
            'items' => $data,
            'count' => count($items),
            'total' => Currency::format($total),
        ]);
    }

}
