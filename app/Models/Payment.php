<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'reference',
        'amount',
        'status',
        'card_last4',
        'failure_reason',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
