<?php

namespace App\Http\Controllers\Front;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
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

    public function store(StoreOrderRequest $request ) 
    {
        $data= $request->validated();
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
              
                $order->address()->create($data);
                    
                Db::commit();

                 event(new OrderCreated($order));

                return response()->json([
                    'message' => 'تم تسجيل الاوردر بنجاح',
                ]);

            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;
            }

        return redirect()->back()->with('success' , 'order created successfully');
        }
}
