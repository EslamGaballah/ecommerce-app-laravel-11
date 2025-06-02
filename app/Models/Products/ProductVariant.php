<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'quantity'
    ];
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_value');
    }

}
