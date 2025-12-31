<?php

namespace App\Models;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'payment_status',
        'total'
        
    ];

    protected static function booted()
    {
        static::creating(function(Order $order) {
            // 20220001, 20220002
            $order->number = Order::getNextOrderNumber();
        });
    }

    public static function getNextOrderNumber()
    {
        // SELECT MAX(number) FROM orders
        $year =  Carbon::now()->year;
        $number = Order::whereYear('created_at', $year)->max('number');
        if ($number) {
            return $number + 1;
        }
        return $year . '00001';
    }

     // glopale scope
    public function scopeFilter(Builder $builder, $filters)
    {
        $builder->when($filters['number'] ?? false, function($builder, $value) {
            $builder->where('orders.number', 'LIKE', "%{$value}%");
        });

        $builder->when($filters['status'] ?? false, function($builder, $value) {
            $builder->where('orders.status', '=', $value);
        });
    }

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function products() 
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id', 'id', 'id')
        ->using(OrderItem::class)
        ->as('Order_item')->withPivot(['product_name', 'price', 'quantity', 'options']);
    }

    public function items() 
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function address() 
    {
        return $this->hasOne(OrderAddress::class);
    }
    
}
