<?php

namespace App\Http\Controllers\Api\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    
    protected $items;

    public function __construct()
    {
        $this->items = collect([]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = Cart::with('product')->get();
        return response()->json($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request ,$quantity)
    {
        $items = collect([]);
        $product = Product::first();
        $items = Cart::where('product_id', '=', $product->id);

        if(!$items) {
        $cart = Cart::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'quantity' => $quantity
        ]);
        $items->get()->push($cart);
        return response()->json( $cart);
    }
     return response()->json( $items->increment('quantity', $quantity));

    // return response()->json();
}

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, $quantity=1 )
    {
        $cart = Cart::where('id', '=', $id)
        ->update([
            'quantity' =>$quantity]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $cart = Cart::where('id',$id)->delete();
    }

    public function empty(string $id)
    {
        Cart::query()->delete();
    }
}
