<?php

namespace App\Http\Controllers\Api\Front;

use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Order\CartService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class CartController extends Controller
{
    protected $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Display a listing of the cart items.
     */
    public function index()
    {
        return $this->getCartResponse();
    }

    /**
     * Store a newly created resource in storage (Add to cart).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'   => ['required', 'int', 'exists:products,id'],
            'quantity'     => ['nullable', 'int', 'min:1'],
            'variation_id' => [
                'nullable',
                'exists:product_variations,id',
                Rule::exists('product_variations', 'id')->where('product_id', $request->product_id)
            ]
        ]);

        try {
            $product = Product::findOrFail($validated['product_id']);

            $this->cart->add($product, $validated['quantity'] ?? 1, $request->variation_id);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully!',
                'cart'    => $this->getCartData()
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => ['required', 'int', 'min:1'],
        ]);

        try {
            $item = $this->cart->update($id, $request->quantity);
            $maxStock = $item->variation?->stock ?? $item->product->stock;

            if ($request->quantity > $maxStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكمية المطلوبة أكبر من المخزون المتاح'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart'    => $this->getCartData()
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->cart->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart'    => $this->getCartData()
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all items from the cart.
     */
    public function empty()
    {
        try {
            $this->cart->empty();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Helper method to format cart details structure identically to Web Controller.
     */
    protected function getCartData()
    {
        $items = $this->cart->items();
        $total = $this->cart->total();

        $formattedItems = $items->map(function ($item) {
            $price = $item->variation?->price ?? $item->product->price;

            return [
                'id'       => $item->id,
                'name'     => $item->product->name,
                'slug'     => $item->product->slug,
                'quantity' => $item->quantity,
                'price'    => Currency::format($price),
                'total'    => Currency::format($price * $item->quantity),
                'image'    => asset('storage/' . ($item->variation?->image ?? $item->product->images->first()?->image)),
            ];
        });

        return [
            'items' => $formattedItems,
            'count' => count($items),
            'total' => Currency::format($total),
        ];
    }

    /**
     * Helper method to return direct cart json response.
     */
    protected function getCartResponse()
    {
        return response()->json($this->getCartData());
    }
}