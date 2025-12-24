<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Interfaces\CartRepositoryInterface;
use App\Repositories\CartRepository;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   
    public $cartItems;

    protected $cart;

     public function __construct(CartRepository $cart)
    {
        $this->cart = $cart;
    }


    // moved to controller
//    public function cartCookieId()
//    {
//     $cookie_id = Cookie::get('cart_id');
//     if(!$cookie_id) {
//         $cookie_id = Str::uuid();
//         Cookie::queue('cart_id', $cookie_id, 30*24*60 );
//     }
//     return $cookie_id;
//    }

    public function index()
    {
        // if (Auth::check()) {
        //     $cartItems = Auth::user()->cart()->with('product')->get();
        // } else {
            // $cartItems = $this->cartCookieId();
            // $cart = Cart::where('cookie_id', '=', $this->cartCookieId())
            // ->with('product')
            // ->get();
            // $cart = Cart::with('product')->get();
            // $total = $cart->sum(fn($item)
            //     => $item->quantity  * ($item->product->price ?? 0));
        // }
        // return $cartItems;
        // dd($cart);
        return view('cart', [
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

        $product = Product::findOrFail($request->post('product_id'));
        $this->cart->add($product, $request->post('quantity'));

        if ($request->expectsJson()) {
            
            return response()->json([
                'message' => 'Item added to cart!',
            ], 201);
        }
        
        return redirect()->route('cart.index')
            ->with('success', 'Product added to cart!');
       
        // return redirect()->back()->with('success' , 'product added to cart');
    }


    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        // $cart = Cart::findOrFail($id);

        // return view('front.cart.show', compact('cart'));
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

         $this->cart->update($id, $request->post('quantity'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $this->cart->delete($id);
        
        return [
            'message' => 'Item deleted!',
        ];
    }
}
