<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;


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
            $cart = Cart::with('product')->get();
            $total = $cart->sum(fn($item)
          
            => $item->quantity  * ($item->product->price ?? 0));
        // }
        // return $cartItems;
        // dd($cart);
        return view('cart', compact('cart','total'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function add(Request $request, $id)
    // {
    //     // $quantity = 1;
    //    $validated= $request->validate([
    //         'product_id' => ['required', 'int', 'exists:products,id'],
    //         'quantity' => ['required', 'int', 'min:1']
    //     ]);
    //     $product = Product::findOrFail($validated('product_id'));

    //     $cartItems = Cart::where('product_id', '=', $product->id)->first();

    //     if ($cartItems) {
    //         $cartItems->quantity += $validated['quantity'];
    //         $cartItems->save();
    //     } else {
    //           Cart::create([
    //             'product_id' => $product->id,
    //             'quantity' => $validated['quantity']
    //         ]);
           
    //             }
    //     dd($request);
    //     return response()->json([
    //     'message' => 'تم إضافة المنتج إلى السلة بنجاح',
    // ]);
        // return redirect()->back()->with('succes' , 'product added to cart');
    // }
    public function store(Request $request)
    {
        
       $validated= $request->validate([
            'product_id' => ['required', 'int', 'exists:products,id'],
            'quantity' => ['required', 'int', 'min:1']
        ]);
        $product = Product::findOrFail($request->product_id);

        $cartItems = Cart::where('product_id', '=', $product->id)->first();

        if ($cartItems) {
            $cartItems->quantity += $request['quantity'];
            $cartItems->save();
        } else {
              Cart::create([
                'product_id' => $product->id,
                'quantity' => $request['quantity']
            ]);
           
                }
        // dd($request);
    //     return response()->json([
    //     'message' => 'تم إضافة المنتج إلى السلة بنجاح',
    // ]);
        return redirect()->back()->with('success' , 'product added to cart');
    }


    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        $cart = Cart::findOrFail($id);

        return view('front.cart.show', compact('cart'));
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
        Cart::where('id', '=', $id)
        ->update([
            'quantity' => $request->quantity
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
