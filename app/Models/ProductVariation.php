<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id',
        'price',
        'compare_price',
        'stock',
        'sku',
        'is_primary',
    ];

     // primary variation
    protected static function booted()
    {
        static::saving(function ($variation) {
            if ($variation->is_primary) {
                ProductVariation::where('product_id', $variation->product_id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    public function product() 
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function values()
    {
        return $this->belongsToMany(AttributeValue::class,'variation_attribute_values');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'stockable');
    }

    public function images() 
    {
        return $this->morphMany(Image::class, 'imageable');
        
    }

    public function primaryImage()
    {
        return $this->morphOne(Image::class, 'imageable')
                    ->where('is_primary', true);
    }

    public function getImageAttribute()
    {
        return $this->images()->first()?->image;
    }

}
