<?php

namespace App\Repositories;

use App\Interfaces\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartRepository implements CartRepositoryInterface
{
    protected $items;

    public function __construct()
    {
        $this->items = collect([]);
    }

    private function getCartKey()
    {
        if (Auth::check()) {
            return ['user_id' => Auth::id()];
        }

        return ['cookie_id' => Cart::cartCookieId(), 'user_id' => null];
    }

    /**
     * The current cart's rows, skipping products that were deleted or unpublished after being added.
     */
    private function sellableItems(): Builder
    {
        return Cart::where($this->getCartKey())
            ->whereHas('product', fn ($query) => $query->active());
    }

    public function get(): Collection
    {
        if (! $this->items->count()) {
            $this->items = $this->sellableItems()
                ->with('product', 'product.images', 'variation.values.attribute')
                ->get();
        }

        return $this->items;
    }

    public function find($id)
    {
        return $this->sellableItems()
            ->with('product', 'variation')
            ->whereKey($id)
            ->firstOrFail();
    }

    public function add(Product $product, $quantity = 1, $variationId = null)
    {
        $cartKey = $this->getCartKey();

        $item = Cart::where($cartKey)
            ->where('product_id', $product->id)
            ->where('variation_id', $variationId)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $item = Cart::create(array_merge($cartKey, [
                'product_id' => $product->id,
                'variation_id' => $variationId,
                'quantity' => $quantity,
            ]));
        }

        $this->items = collect();

        return $item;
    }

    public function update($id, $quantity)
    {
        $item = Cart::with('product', 'variation')
            ->where('id', $id)
            ->where($this->getCartKey())
            ->firstOrFail();

        $item->update([
            'quantity' => $quantity,
        ]);

        $this->items = collect();

        return $item;
    }

    public function delete($id)
    {
        Cart::where('id', '=', $id)
            ->where($this->getCartKey())
            ->delete();

        $this->items = collect();
    }

    public function empty()
    {
        Cart::where($this->getCartKey())->delete();
        $this->items = collect();
    }

    public function total(): float
    {
        return $this->get()->sum(function ($item) {
            $price = $item->variation?->price ?? $item->product->price;

            return $item->quantity * $price;
        });
    }

    public function count(): int
    {
        return $this->get()->count();
    }

    /**
     * Move a guest's cart into the user's cart, combining rows for the same item.
     */
    public function mergeGuestCart(string $cookieId, int $userId): void
    {
        $guestRows = Cart::where('cookie_id', $cookieId)->whereNull('user_id')->get();

        foreach ($guestRows as $row) {
            $existing = Cart::where('user_id', $userId)
                ->where('product_id', $row->product_id)
                ->where('variation_id', $row->variation_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $row->quantity);
                $row->delete();
            } else {
                // A new cookie id keeps this row out of the browser's next guest cart.
                $row->update(['user_id' => $userId, 'cookie_id' => (string) Str::uuid()]);
            }
        }

        $this->items = collect();
    }
}
