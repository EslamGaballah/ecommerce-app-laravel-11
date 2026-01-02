<?php

namespace App\Models;
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
        'name',
        'slug',
        'description',
        'category_id',
        'price', 
        'compare_price',
        'quantity',
        'status',
    ];

    public static function scopeFilter (Builder $builder, $filters)
    {
        $builder->when($filters['name'] ?? false, function($builder, $value){
            $builder->where('products.name', 'LIKE', "%{$value}");
        });

        $builder->when($filters['status'] ?? false, function($builder, $value) {
            $builder->where('products.status', '=', $value);
        });
    }

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category() 
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function images() 
    {
        return $this->hasMany(ProductImage::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating()
    {
        return round($this->ratings()->avg('rating'), 1);
    }


}