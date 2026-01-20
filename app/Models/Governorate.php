<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
   protected $fillable = [
        'name',
        'shipping_price',
        'delivery_days',
        'is_active'
    ];

    protected $casts = [
    'is_active' => 'boolean',
];

    public function getStatusLabelAttribute()
    {
        return $this->is_active ? __('app.avilable') : __('app.unAvilable');
    }

    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

     public function scopeFilter(Builder $builder, $filters)
    {
        $builder->when($filters['name'] ?? false, function($builder, $value) {
            $builder->where('governorate.name', 'LIKE', "%{$value}%");
        });

        $builder->when(isset($filters['is_active']) && $filters['is_active'] !== '', function($builder) use ($filters) {
        $builder->where('is_active', '=', $filters['is_active']);
    });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

}
