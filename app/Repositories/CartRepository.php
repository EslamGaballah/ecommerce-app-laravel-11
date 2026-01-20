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
        $item =  Cart::where('product_id', '=', $product->id)->first();
        
        if (!$item) {
            $item = Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            $this->items->push($item); // add product to collection ($this=> collection)
            return $item;
        }

        return $item->increment('quantity', $quantity);
    }

    public function update($id, $quantity)
    {
        $item = Cart::with('product')->findOrFail($id);

        $item->update([
            'quantity' => $quantity,
        ]);

        $this->items = collect(); // reset cache
        
        return $item;
    }

    public function delete($id)
    {
        Cart::where('id', '=', $id)
            ->delete();

        $this->items = collect(); // reset cache
    }

    public function empty()
    {
        Cart::query()->delete();
        $this->items = collect();
    }

    public function total() : float
    {   
            // using collection
        return $this->get()->sum(function($item) {

            return $item->quantity * $item->product->price;
        });
    }
}