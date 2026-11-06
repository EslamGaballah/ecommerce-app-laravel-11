<?php

namespace App\Models;
use App\Helpers\Currency;
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
        'name_ar',
        'name_en',
        'user_id',
        'category_id',
        'brand_id',
        'slug',
        'sku',
        'description',
        'status',
        'price', 
        'compare_price',
        'stock',
        'product_type',

        'rating_avg',
        'rating_count',
        'is_featured',
        
        
    ];

    protected $casts = [
        'status' => ProductStatus::class,
        'product_type' => ProductType::class,
        'is_featured' => 'boolean',

    ];

     public function getNameAttribute()
    {
         return app()->getLocale() == 'ar'
        ? ($this->name_ar ?? $this->name_en ?? 'product')
        : ($this->name_en ?? $this->name_ar ?? 'product');
    }

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

        public function scopeFeatured($query)
    {
        return $query->where('products.is_featured', true);
    }

    public function scopeWithSoldQuantity($query)
    {
        return $query->withSum([
            'orderItems as sold_quantity' => fn ($items) => $items->whereHas('order', fn ($orders) => $orders->whereNotIn('status', ['canceled', 'refunded'])),
        ], 'quantity');
    }

    public function scopeBestSelling($query)
    {
        return $query->withSoldQuantity()->having('sold_quantity', '>', 0)->orderByDesc('sold_quantity');
    }

    public function scopeMostViewed($query)
    {
        return $query->where('products.views_count', '>', 0)->orderByDesc('products.views_count');
    }

    /** In stock, on sale for a while, and not sold in this many days. */
    public function scopeStagnant($query, int $days)
    {
        return $query->inStock()
            ->where('products.created_at', '<', now()->subDays($days))
            ->whereDoesntHave('orderItems', fn ($items) => $items->whereHas('order', fn ($orders) => $orders
                ->whereNotIn('status', ['canceled', 'refunded'])
                ->where('created_at', '>=', now()->subDays($days))));
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class);
    }

    public function scopeActive($query)
    {
        return $query->where('products.status', ProductStatus::Active);
    }

    public function scopeInStock($query)
    {
        return $query->where(fn ($query) => $query
            ->where('products.stock', '>', 0)
            ->orWhereHas('variations', fn ($variations) => $variations->where('stock', '>', 0)));
    }

    /**
     * A variable product has no stock of its own, so its stock column is often null.
     */
    public function scopeOutOfStock($query)
    {
        return $query
            ->where(fn ($query) => $query->whereNull('products.stock')->orWhere('products.stock', '<=', 0))
            ->whereDoesntHave('variations', fn ($variations) => $variations->where('stock', '>', 0));
    }

    public function scopeOnSale($query)
    {
        return $query->where(fn ($query) => $query
            ->whereColumn('products.compare_price', '>', 'products.price')
            ->orWhereHas('variations', fn ($variations) => $variations
                ->where('is_primary', true)
                ->whereColumn('compare_price', '>', 'price')));
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    // ------------------------------
    // All filters moved to class filter
    // ------------------------------
    // public static function scopeFilter (Builder $builder, $filters)
    // {
    //     $builder->when($filters['name'] ?? false, function($builder, $value){
    //         $builder->where('products.name', 'LIKE', "%{$value}");
    //     });

    //     $builder->when($filters['status'] ?? false, function($builder, $value) {
    //         $builder->where('products.status', '=', $value);
    //     });
    // }

    // public function scopeByCategory($query, $categoryIds)
    // {
    //     return $query->when($categoryIds, function ($q) use ($categoryIds) {
    //     $q->whereIn('category_id', (array)$categoryIds);
    // });
    // }
    
    // public function scopeByBrand($query, $brandId)
    // {
    //     return $query->when($brandId, function ($q) use ($brandId) {
    //         $q->where('brand_id', $brandId);
    //     });
    // }
   
    // public function scopeByPriceRange($query, $min_price, $max_price)
    // {
    //     return $query->when($min_price, fn($q) => $q->where('price', '>=', $min_price))
    //                 ->when($max_price, fn($q) => $q->where('price', '<=', $max_price));
    // }

    // public function scopeSortBy($query, $type)
    // {
    //     return match ($type) {
    //         'low_price'  => $query->orderBy('price', 'asc'),
    //         'high_price' => $query->orderBy('price', 'desc'),
    //         'newest'     => $query->orderBy('created_at', 'desc'),
    //         'oldest'     => $query->orderBy('created_at', 'asc'),
    //         default      => $query->orderBy('id', 'desc'), 
    //     };
    // }
    // ------------------------------
    // End filters 
    // ------------------------------

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
        return $this->belongsTo(User::class);
    }

    public function category() 
    {
        return $this->belongsTo(Category::class);
    }

    public function brand() 
    {
        return $this->belongsTo(Brand::class);
    }

    public function images() 
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function thumbnail() 
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', 'thumbnail');
    }

     public function gallery() 
    {
        return $this->morphMany(Image::class, 'imageable')->where('type', 'gallery');
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

    public function getFinalPriceAttribute()
    {
        return $this->defaultVariation?->price ?? $this->price;
    }

    public function getFinalComparePriceAttribute()
    {
        return $this->defaultVariation?->compare_price ?? $this->compare_price;
    }

    public function getDiscountPercentAttribute(): int
    {
        $price = (float) $this->final_price;
        $comparePrice = (float) $this->final_compare_price;

        return $comparePrice > $price ? (int) round(($comparePrice - $price) / $comparePrice * 100) : 0;
    }

    public function getInStockAttribute(): bool
    {
        return $this->variations->isNotEmpty()
            ? $this->variations->sum('stock') > 0
            : $this->stock > 0;
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->default_variation?->image ?? $this->images->first()?->image;

        return $path ? asset('storage/'.$path) : asset('assets/images/product-placeholder.svg');
    }

    /**
     * What a product card needs to switch between variations without opening the product page.
     * Uses the loaded relations: variations.images and variations.values.attribute.
     */
    public function getCardOptionsAttribute(): array
    {
        $selected = $this->variations->first(fn ($variation) => $variation->is_primary && $variation->stock > 0)
            ?? $this->variations->first(fn ($variation) => $variation->stock > 0)
            ?? $this->default_variation;

        $selectedValues = $selected->values->pluck('id');

        $variations = $this->variations->map(function ($variation) {
            $price = (float) $variation->price;
            $compare = (float) $variation->compare_price;
            $onSale = $compare > $price;

            return [
                'id' => $variation->id,
                'values' => $variation->values->pluck('id')->all(),
                'price' => Currency::format($price),
                'compare' => $onSale ? Currency::format($compare) : null,
                'discount' => $onSale ? (int) round(($compare - $price) / $compare * 100) : 0,
                'stock' => (int) $variation->stock,
                'image' => $variation->image ? asset('storage/'.$variation->image) : null,
            ];
        })->values();

        $attributes = $this->variations
            ->flatMap->values
            ->groupBy('attribute_id')
            ->map(fn ($values) => [
                'id' => $values->first()->attribute_id,
                'name' => $values->first()->attribute?->name,
                'values' => $values->unique('id')->values()->map(function ($value) use ($selectedValues) {
                    // In stock together with what is selected in the other attribute groups?
                    $others = $selectedValues->reject(fn ($id) => $this->valueAttribute($id) === $value->attribute_id);

                    return [
                        'id' => $value->id,
                        'label' => $value->value,
                        'active' => $selectedValues->contains($value->id),
                        'available' => $this->variations->contains(fn ($variation) => $variation->stock > 0
                            && $variation->values->pluck('id')->contains($value->id)
                            && $others->diff($variation->values->pluck('id'))->isEmpty()),
                    ];
                })->all(),
            ])
            ->values()
            ->all();

        return compact('attributes', 'selected', 'variations');
    }

    private function valueAttribute(int $valueId): ?int
    {
        return $this->variations->flatMap->values->firstWhere('id', $valueId)?->attribute_id;
    }

    public function getStockLeftAttribute(): int
    {
        return $this->variations->isNotEmpty()
            ? (int) $this->variations->sum('stock')
            : (int) $this->stock;
    }

    public function getTotalQuantityAttribute()
    {
        return $this->variations->sum('stock');
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