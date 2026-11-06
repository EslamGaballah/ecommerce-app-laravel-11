<?php

namespace App\Http\Controllers\Front;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\TestPaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function show(Order $order)
    {
        $this->authorizePayment($order);

        if (! $order->isPayable()) {
            return redirect()->route('front.orders.show', $order);
        }

        return view('front.payments.show', [
            'order' => $order,
            'cards' => TestPaymentService::CARDS,
        ]);
    }

    public function store(Request $request, Order $order, TestPaymentService $payments)
    {
        $this->authorizePayment($order);

        $data = $request->validate([
            'card' => ['required', Rule::in(array_keys(TestPaymentService::CARDS))],
        ]);

        $payment = $payments->pay($order, $data['card']);

        if (! $payment) {
            return redirect()->route('front.orders.show', $order);
        }

        if ($payment->status === PaymentStatus::FAILED) {
            return redirect()
                ->route('front.payments.show', $order)
                ->with('error', __('app.payment_failed_'.$payment->failure_reason));
        }

        return redirect()->route('front.orders.success', $order);
    }

    private function authorizePayment(Order $order): void
    {
        // A simulated payment on a live site would be a free order.
        abort_if(app()->isProduction(), 404);
        abort_unless((int) $order->user_id === auth()->id(), 403);
    }
}
