<?php

namespace App\Models;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, HasFactory;
    
    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'description',
        'status',
        'price', 
        'compare_price',
        'stock',
        'product_type',
        
        
    ];

    protected $casts = [
        'status' => ProductStatus::class,
        'product_type' => ProductType::class,

    ];

    protected static function booted()
    {
        static::saving(function ($product) {

        if (
            $product->product_type === ProductType::SIMPLE &&
            empty($product->sku)
        ) {
            $product->sku = app(\App\Services\SkuGenerator::class)
                ->generateForSimple($product);
            }
        });
    }


    public static function scopeFilter (Builder $builder, $filters)
    {
        $builder->when($filters['name'] ?? false, function($builder, $value){
            $builder->where('products.name', 'LIKE', "%{$value}");
        });

        $builder->when($filters['status'] ?? false, function($builder, $value) {
            $builder->where('products.status', '=', $value);
        });
    }

    public function scopeByCategory($query, $categoryIds)
    {
        return $query->when($categoryIds, function ($q) use ($categoryIds) {
        $q->whereIn('category_id', (array)$categoryIds);
    });
    }
    
    public function scopeByBrand($query, $brandId)
    {
        return $query->when($brandId, function ($q) use ($brandId) {
            $q->where('brand_id', $brandId);
        });
    }
   
    public function scopeByPriceRange($query, $min_price, $max_price)
    {
        return $query->when($min_price, fn($q) => $q->where('price', '>=', $min_price))
                    ->when($max_price, fn($q) => $q->where('price', '<=', $max_price));
    }

    public function scopeSortBy($query, $type)
    {
        return match ($type) {
            'low_price'  => $query->orderBy('price', 'asc'),
            'high_price' => $query->orderBy('price', 'desc'),
            'newest'     => $query->orderBy('created_at', 'desc'),
            'oldest'     => $query->orderBy('created_at', 'asc'),
            default      => $query->orderBy('id', 'desc'), 
        };
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function getIsFavoritedAttribute()
    {
        if (!auth()->check()) return false;
        
        return $this->favoritedBy()->where('user_id', auth()->id())->exists();
    }

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category() 
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function brand() 
    {
        return $this->belongsTo(Category::class, 'brand_id', 'id');
    }

    public function images() 
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function tags()
    {
         return $this->morphToMany(Tag::class, 'taggable');
    }

     public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function primaryVariation()
    {
        return $this->hasOne(ProductVariation::class)
                    ->where('is_primary', true);
    }

    public function getDefaultVariationAttribute()
    {
        return $this->primaryVariation
            ?? $this->variations->first();
    }

    public function getTotalQuantityAttribute()
    {
        return $this->variations->sum('quantity');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'stockable');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating()
    {
        return round($this->ratings()->avg('rating'), 1);
    }

    


}