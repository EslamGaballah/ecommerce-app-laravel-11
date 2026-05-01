<?php

namespace App\Services\Product;

use App\Models\StockMovement;

class StockService
{
    public function create($model, $stock, $reason = 'Initial stock')
    {
        return StockMovement::create([
            'stockable_id' => $model->id,
            'stockable_type' => $model->getMorphClass(),
            'stock' => $stock,
            'type' => 'in',
            'reason' => $reason,
            'user_id' => auth()->id()
        ]);
    }
}