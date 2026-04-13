<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name_en',
        'name_ar',
        'description',
        'parent_id',
        'slug',
        'status'

    ];

    public function getNameAttribute()
    {
        return app()->getLocale() == 'ar'
            ? $this->name_ar
            : $this->name_en;
    }

    // glopale scope
    public function scopeFilter(Builder $builder, $filters)
    {
        $builder->when($filters['name'] ?? false, function($builder, $value) {
            $builder->where('categories.name', 'LIKE', "%{$value}%");
        });

        $builder->when($filters['status'] ?? false, function($builder, $value) {
            $builder->where('categories.status', '=', $value);
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class,'category_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }

    public function children(){
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }
}
