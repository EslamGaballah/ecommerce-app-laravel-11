<?php

namespace App\Http\Controllers\Front;

use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Models\Cart;
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
            'quantity' => ['nullable', 'int', 'min:1'] // quentity default(1)
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $this->cart->add($product, $validated['quantity'] ?? 1);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart!',
            ], 201);
        }
        
        return redirect()
            ->route('cart.index')
            ->with('success', 'Product added to cart!');
    }

    /**
     * Display the specified resource.
     */
    public function show( Cart $cart)
    {
        // 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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

         return response()->json([
        'success' => true,
        'item_total' => Currency::format($item->quantity * $item->product->price),
        'cart_total' => Currency::format($this->cart->total()),
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
        'cart_total' => Currency::format($this->cart->total()),
    ]);
    }
}
