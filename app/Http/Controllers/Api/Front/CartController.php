<?php

namespace App\Http\Controllers\Api\Front;

use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Order\CartService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function __construct(protected CartService $cart)
    {
    }

    public function index()
    {
        return response()->json($this->getCartData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'int', 'exists:products,id'],
            'quantity' => ['nullable', 'int', 'min:1', 'max:65535'],
            'variation_id' => [
                'nullable',
                Rule::exists('product_variations', 'id')->where('product_id', $request->input('product_id')),
            ],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $this->cart->add($product, $validated['quantity'] ?? 1, $validated['variation_id'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully!',
            'cart' => $this->getCartData(),
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'quantity' => ['required', 'int', 'min:1', 'max:65535'],
        ]);

        $this->cart->update($id, $data['quantity']);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart' => $this->getCartData(),
        ]);
    }

    public function destroy(string $id)
    {
        $this->cart->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart' => $this->getCartData(),
        ]);
    }

    public function empty()
    {
        $this->cart->empty();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
        ]);
    }

    protected function getCartData(): array
    {
        $items = $this->cart->items();

        $formattedItems = $items->map(function ($item) {
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

        return [
            'items' => $formattedItems,
            'count' => $items->count(),
            'total' => Currency::format($this->cart->total()),
        ];
    }
}
