<?php

namespace App\Http\Controllers\Front;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Interfaces\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\CartRepository;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery\Exception\InvalidOrderException;
use Throwable;

use function Pest\Laravel\json;

class CheckoutController extends Controller
{
    public function create(CartRepositoryInterface $cart)
    {

        $items = $cart->get();
        if ($items->count() == 0) {
                return redirect()->route('home')->with('info', 'Cart is impty');
            }

        $total = $items->sum(fn($item) =>
            $item->quantity * ($item->product->price ?? 0)
        );

        return view('front.checkout',compact('items', 'total'));
    }

    public function store(StoreOrderRequest $request , CartRepositoryInterface $cart) 
    {
        $data= $request->validated();
        $items = Cart::get();

        $total = $items->sum(fn($item) =>
            $item->quantity * ($item->product->price ?? 0)
        );

        DB::beginTransaction();

        try {
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'total'=>$total
                ]);
            
                foreach ($items as $item) {
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
                
                $cart->empty();
                 event(new OrderCreated($order));
            return redirect()->route('home')->with('success' , 'order created successfully');
            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;

                return redirect()->back()->with('erroe' , 'failed to create order!');
            }
        
        }
}
