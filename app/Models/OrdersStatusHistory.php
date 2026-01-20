<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'old_status',
        'new_status',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
