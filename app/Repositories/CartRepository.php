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

    public function get() : Collection
    {
        if (!$this->items->count()) {
            $this->items = Cart::with('product')->get();
        }

        return $this->items;
    }

    public function add(Product $product, $quantity = 1)
    {
        $item =  Cart::where('product_id', '=', $product->id)
            ->first();
        
        if (!$item) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            $this->get()->push($cart); // add product to collection ($this=> collection)
            return $cart;
        }

        return $item->increment('quantity', $quantity);
    }

    public function update($id, $quantity)
    {
        Cart::where('id', '=', $id)
            ->update([
                'quantity' => $quantity,
            ]);
    }

    public function delete($id)
    {
        Cart::where('id', '=', $id)
            ->delete();
    }

    public function empty()
    {
        Cart::query()->delete();
    }

    public function total() : float
    {   
        // with out collections
        /*return (float) Cart::join('products', 'products.id', '=', 'carts.product_id')
            ->selectRaw('SUM(products.price * carts.quantity) as total')
            ->value('total');*/


            // using collection
        return $this->get()->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
    }
}