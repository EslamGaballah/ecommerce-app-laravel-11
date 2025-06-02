<?php

namespace App\Models;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderItem extends Pivot
{
    protected $table = 'order_items';
    public $incrementing = true;

    public $timestamps = false;

    public function product(){
        return $this->belongsTo(Product::class)->withDefault([
            'name' => $this->product_name
        ]);
    }
    public function order() {
        return $this->belongsTo(Order::class);
    }
}
