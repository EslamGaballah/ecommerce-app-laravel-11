<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'category_id',
        'price', 
        'compare_price',
        'quantity',
        'status',
    ];

    public function category() 
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }


public static function scopeFilter (Builder $builder, $filters){

    $builder->when($filters['name'] ?? false, function($builder, $value){
        $builder->where('products.name', 'LIKE', "%{$value}");
    });

    $builder->when($filters['status'] ?? false, function($builder, $value) {
        $builder->where('products.status', '=', $value);
    });

}

}