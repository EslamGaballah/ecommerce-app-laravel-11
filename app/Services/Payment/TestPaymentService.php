<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Simulates a card payment. No money moves and no card details are collected:
 * the customer picks one of these made-up cards, and each one has a fixed result.
 */
class TestPaymentService
{
    public const CARDS = [
        'success' => ['last4' => '4242', 'failure' => null],
        'declined' => ['last4' => '0002', 'failure' => 'card_declined'],
        'insufficient_funds' => ['last4' => '9995', 'failure' => 'insufficient_funds'],
    ];

    /**
     * Returns the new payment, or null when the order can no longer be paid.
     */
    public function pay(Order $order, string $card): ?Payment
    {
        return DB::transaction(function () use ($order, $card) {
            // Lock the row so a double click cannot pay the same order twice.
            $current = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $current->isPayable()) {
                return null;
            }

            $result = self::CARDS[$card];
            $status = $result['failure'] ? PaymentStatus::FAILED : PaymentStatus::PAID;

            $current->update(['payment_status' => $status]);

            return $current->payments()->create([
                'gateway' => 'test',
                'reference' => 'TEST-'.Str::upper(Str::random(16)),
                'amount' => $current->total,
                'status' => $status,
                'card_last4' => $result['last4'],
                'failure_reason' => $result['failure'],
            ]);
        });
    }
}
