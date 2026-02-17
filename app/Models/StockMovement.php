<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'stockable_id',
        'stockable_type',
        'stock',
        'type',
        'reason',
        'user_id',
    ];

    public function stockable()
{
    return $this->morphTo();
}
}
