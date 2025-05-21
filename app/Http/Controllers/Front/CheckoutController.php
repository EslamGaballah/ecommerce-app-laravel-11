<?php

namespace App\Http\Controllers\Front;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

use function Pest\Laravel\json;

class CheckoutController extends Controller
{
    public function create(){
        $cart = Cart::get();
        if ($cart->count() == 0) {
            // throw new InvalidOrderException('Cart is impty');
            return 'cart is impty';
        }
        $total = $cart->sum(fn($item) =>
            $item->quantity * ($item->product->price ?? 0)
        );
        return view('front.checkout',compact('cart', 'total'));
    }
    public function store(Request $request ) {
        // dd($request->all());
         $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'street_address' => ['required', 'string', 'max:255'],
        ]);
       $cart = Cart::get();
        if ($cart->count() == 0) {
            // throw new InvalidOrderException('Cart is impty');
            return 'cart is impty';
        }
        $total = $cart->sum(fn($item) =>
            $item->quantity * ($item->product->price ?? 0)
        );

        DB::beginTransaction();

        try {
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'total'=>$total
                ]);
            
                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' =>$item->product_id,
                        'product_name' => $item->product->name,
                        'price' => $item->product->price,
                        'quantity' => $item->quantity
                    ]);
                }
              
                    $order->address()->create([
                        'first_name' =>$request->first_name,
                        'last_name' =>$request->last_name,
                        'email' =>$request->email,
                        'phone_number' =>$request->phone_number,
                        'street_address' => $request->street_address,
                        'city' =>$request->city,
                        'state' =>$request->state,
                        'country' =>$request->country,
                    ]);
                    
                Db::commit();

                 event(new OrderCreated($order));

        // dd($request->all());

                return response()->json([
                    'message' => 'تم إضافة المنتج إلى السلة بنجاح',
                ]);

            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;
            }

        return redirect()->back()->with('success' , 'order created successfully');
        }
}
