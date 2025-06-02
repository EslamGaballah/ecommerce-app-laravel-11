<?php

namespace App\Models\Products;
use App\Models\Category;
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

     public function images() {
        return $this->hasMany(ProductImage::class);
    }

  public function variants() {
        return $this->hasMany(ProductVariant::class);
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