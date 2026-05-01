<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Governorate;
use App\Services\Order\CheckoutService;
use Illuminate\Http\Request;
use Throwable;


class CheckoutController extends Controller
{

    protected $checkout;

    public function __construct(CheckoutService $checkout)
    {
        $this->checkout = $checkout;
    }


    public function create()
    {
        try {
            $data = $this->checkout->prepareCheckoutData();

            return view('front.checkout', [
                'items' => $data['items'],
                'total' => $data['cartTotal'],
                'totals' => $data['totals'],
                'governorates' => Governorate::all(),
            ]);

        } catch (\Exception $e) {
            return redirect()->route('home')->with('info', $e->getMessage());
        }
    }

    public function store(StoreOrderRequest $request )
    {
        
        try {

        $order = $this->checkout->checkout($request->validated());

        return redirect()
            ->route('front.orders.success', $order->id)
            ->with('success', 'order created successfully');

        } catch (\Throwable $e) {

            return back()->with('error', $e->getMessage());
        }

    }
   public function applyCoupon(Request $request)
    {
        $data = $this->checkout->prepareCheckoutData(
            $request->governorate_id,
            $request->code
        );

        if (isset($data['error'])) {
            return response()->json([
                'error' => $data['error']
            ]);
        }

        return response()->json([
            'totals' => $data['totals']
        ]);
    }
}
