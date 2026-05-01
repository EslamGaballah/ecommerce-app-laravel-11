<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
   protected $fillable = [
        'name_en',
        'name_ar',
        'shipping_price',
        'delivery_days',
        'is_active'
    ];

    protected $casts = [
    'is_active' => 'boolean',
    ];

    public function getNameAttribute()
    {
        return app()->getLocale() == 'ar'
            ? $this->name_ar
            : $this->name_en;
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_active ? __('app.available') : __('app.unavailable');
    }

    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

     public function scopeFilter(Builder $builder, $filters)
    {
        $builder->when($filters['name'] ?? null, function ($builder, $value) {
            $builder->where(function ($query) use ($value) {
                $query->where('name_en', 'like', "%$value%")
                    ->orWhere('name_ar', 'like', "%$value%");
            });
        });

        $builder->when($filters['is_active'] ?? null, function ($builder, $value) {
            $builder->where('is_active', $value);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

}
