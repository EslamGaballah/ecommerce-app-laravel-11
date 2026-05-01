<?php

namespace App\Repositories;

use App\Interfaces\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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

        return ['cookie_id' => Cart::cartCookieId()];
    }

    public function get() : Collection
    {
        if (!$this->items->count()) {
            $this->items = Cart::with('product','product.images','variation.values.attribute')
                ->where($this->getCartKey())
                ->get();
        }

        return $this->items;
    }

    public function add(Product $product, $quantity = 1, $variationId = null)
    {
        $cartKey = $this->getCartKey();

        // 🔍 البحث عن العنصر
        $item = Cart::where($cartKey)
            ->where('product_id', $product->id)
            ->where('variation_id', $variationId)
            ->first();

        if (!$item) {

            $item = Cart::create(array_merge($cartKey,[
                'product_id' => $product->id,
                'variation_id' => $variationId, // 🔥 المهم
                'quantity' => $quantity,
            ]));

            $this->items->push($item);

            return $item;
        }

        $item->increment('quantity', $quantity);

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

        $this->items = collect(); // reset

        return $item;
    }

    public function delete($id)
    {
        Cart::where('id', '=', $id)
            ->where($this->getCartKey())
            ->delete();

        $this->items = collect(); // reset cache
    }

    public function empty()
    {
        Cart::where($this->getCartKey())->delete();
        $this->items = collect();
    }

    public function total() : float
    {
            // using collection
        return $this->get()->sum(function ($item) {
        $price = $item->variation?->price ?? $item->product->price;
        return $item->quantity * $price;
    });
    }

    public function count() : int
    {
        return $this->get()->count();
    }
   
}
