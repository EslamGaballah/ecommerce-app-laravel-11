<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'description',
        'slug',
        'status'

    ];

    protected static function booted()
    {
        static::creating(function ($brand) {

            $slug = Str::slug($brand->name_en);

            $count = Brand::where('slug', 'like', "$slug%")->count();

            $brand->slug = $count ? $slug . '-' . ($count + 1) : $slug;
        });
    }

    public function getNameAttribute()
    {
        return app()->getLocale() == 'ar'
            ? $this->name_ar
            : $this->name_en;
    }

    public function scopeFilter(Builder $builder, $filters)
    {
        $builder->when($filters['name'] ?? null, function ($builder, $value) {
            $builder->where(function ($query) use ($value) {
                $query->where('name_en', 'like', "%$value%")
                    ->orWhere('name_ar', 'like', "%$value%");
            });
        });

        $builder->when($filters['status'] ?? null, function($builder, $value) {
            $builder->where('status', '=', $value);
        });
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
