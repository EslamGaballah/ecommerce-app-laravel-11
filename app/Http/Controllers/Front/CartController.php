<?php

namespace App\Http\Controllers\Front;

use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Interfaces\CartRepositoryInterface;

use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $cart;

     public function __construct(CartRepositoryInterface $cart)
    {
        $this->cart = $cart;
    }

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
       $validated= $request->validate([
            'product_id' => ['required', 'int', 'exists:products,id'],
            'quantity' => ['nullable', 'int', 'min:1'], // quentity default(1)
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $this->cart->add($product, $validated['quantity'] ?? 1, $request->variation_id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart!',
                'count' => $this->cart->count(),
                'total' => Currency::format($this->cart->total()),
            ], 201);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Product added to cart!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => ['required', 'int', 'min:1'],
        ]);

        $item = $this->cart->update($id, $request->quantity);

        $price = $item->variation?->price ?? $item->product->price;

        $cartTotal = $this->cart->total(); 

        // $totals = self::calculateTotal($this->cart->total());

        return response()->json([
            'success' => true,
            'item_total' => Currency::format($item->quantity * $price),
            'cart_subtotal' => Currency::format($cartTotal),
            'cart_total' => Currency::format($cartTotal),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->cart->delete($id);

        $cartTotal = $this->cart->total();

        return response()->json([
            'success' => true,
            'cart_subtotal' => Currency::format($cartTotal),
            'cart_total' => Currency::format($cartTotal),
        ]);
    }

    public function cartJson()
{
    $items = $this->cart->get();

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
