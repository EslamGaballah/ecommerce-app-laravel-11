<?php

namespace App\Models;

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

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
