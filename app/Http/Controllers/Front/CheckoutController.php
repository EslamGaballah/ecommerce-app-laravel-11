<?php

namespace App\Http\Controllers\Front;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Governorate;
use App\Services\Order\CheckoutService;
use Illuminate\Http\Request;

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
                'governorates' => Governorate::active()->get(),
            ]);
        } catch (CheckoutException $e) {
            return redirect()->route('home')->with('info', $e->getMessage());
        }
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->checkout->checkout($request->validated());

            return redirect()
                ->route('front.orders.success', $order->id)
                ->with('success', 'order created successfully');
        } catch (CheckoutException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', __('app.error_occurred'));
        }
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:255'],
            'governorate_id' => ['nullable', 'integer'],
        ]);

        try {
            $checkout = $this->checkout->prepareCheckoutData($data['governorate_id'] ?? null, $data['code'] ?? null);
        } catch (CheckoutException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['totals' => $checkout['totals']]);
    }
}
