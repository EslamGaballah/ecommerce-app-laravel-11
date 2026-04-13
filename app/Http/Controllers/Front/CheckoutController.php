<?php

namespace App\Http\Controllers\Front;

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Interfaces\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Governorate;
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
            return redirect()->route('home')->with('info', 'Cart is empty');
        }

        $total = $cart->total();
        $shipping = 0;
        $tax = 20;

        $totals = self::calculateTotal($total, $shipping, $tax);

        $governorates = Governorate::all();

        return view('front.checkout',compact('items', 'total','totals', 'governorates'));
    }

    public function store(StoreOrderRequest $request , CartRepositoryInterface $cart)
    {
        $data= $request->validated();
        $items = $cart->get();

        $coupon = Coupon::find(session('coupon_id'));
        $cartTotal = $cart->total();

        $tax = 20;

        $governorate = Governorate::findOrFail($request->governorate_id);
        $shipping = $governorate->shipping_price;

        $totals = self::calculateTotal($cartTotal, $shipping, $tax);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => OrderStatus::PENDING,
                'coupon_id' => $coupon?->id,
                'discount' => $totals['discount'],
                'shipping' => $shipping,
                'tax' => $tax ,
                'total' => $totals['total'] 
            ]);

            foreach ($items as $item) {

            $stock = $item->variation?->stock ?? $item->product->stock;

            if ($stock < $item->quantity) {
                throw new \Exception("Product {$item->product->name} is out of stock.");
            }
                $price = $item->variation?->price ?? $item->product->price;

                $options = null;
                if($item->variation && $item->variation->values->count()){
                    $options = $item->variation->values->mapWithKeys(function($v){
                        return [$v->attribute->name => $v->value];
                    });
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variation_id' => $item->variation_id ?? null,
                    'product_name' => $item->product->name,
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'options' => $options, // لو عايز تعرض الخصائص بعد كده
                ]);
                // تحديث المخزون
                if ($item->variation_id) {
                    $item->variation->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrement('stock', $item->quantity);
                }
            }

                $order->address()->create($data);

                Db::commit();

                $cart->empty();

                self::finalizeCouponUsage();

                event(new OrderCreated($order));

                return redirect()->route('front.orders.sucess', $order->id)->with('success' , 'order created successfully');
            } catch (Throwable $e) {
                DB::rollBack();
                // throw $e;
                // \Log::error($e->getMessage());

                return redirect()->back()->with('error' , 'failed to create order!');
            }

        }
    public function applyCoupon(Request $request, CartRepositoryInterface $cart)
    {
        $request->validate([
            'code' => 'nullable|string'
        ]);

        $cartTotal = $cart->total();

        $governorate = Governorate::find($request->governorate_id);
        $shipping = $governorate->shipping_price ?? 0;

        $tax = 20;

        $coupon = null;

         if ($request->filled('code')) {

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Coupon not found'], 422);
        }

        if (!$coupon->is_active) {
            return response()->json(['error' => 'Coupon not active'], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['error' => 'Coupon expired'], 422);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['error' => 'Usage limit reached'], 422);
        }

        if ($coupon->min_order_amount && $cartTotal < $coupon->min_order_amount) {
            return response()->json(['error' => 'Minimum not reached'], 422);
        }

        session(['coupon_id' => $coupon->id]);
        }

        if (!$request->filled('code')) {
            session()->forget('coupon_id');
        }

        $totals = self::calculateTotal($cartTotal, $shipping, $tax);

        return response()->json([
            'success' => true,
            'totals' => [
                'original' => Currency::format($totals['original']),
                'discount' => Currency::format($totals['discount']),
                'shipping' => Currency::format($totals['shipping']),
                'tax' => Currency::format($totals['tax']),
                'total' => Currency::format($totals['total']),
            ]
        ]);
    }

    // Calculate cart total with coupon
    public static function calculateTotal($cartTotal, $shipping = 0, $tax = 0)
    {
        $coupon = Coupon::find(session('coupon_id'));
        $discount = 0;

        if ($coupon) {
            if ($coupon->type === 'fixed') {
                $discount = $coupon->value;
            } elseif ($coupon->type === 'percent') {
                $discount = ($coupon->value / 100) * $cartTotal;
            }
        }

        $subtotalAfterDiscount  = max($cartTotal - $discount, 0);

        $finalTotal = $subtotalAfterDiscount + $shipping + $tax;

        return [
        'original' => $cartTotal,
        'discount' => $discount,
        'subtotal' => $subtotalAfterDiscount,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $finalTotal,
    ];
    }

    //  after order is completed
    public static function finalizeCouponUsage()
    {
        $coupon = Coupon::find(session('coupon_id'));

        if ($coupon) {
            $coupon->increment('used_count');
            session()->forget('coupon_id'); // remove coupon from session
        }
    }

}
