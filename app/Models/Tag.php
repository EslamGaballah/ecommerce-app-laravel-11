<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'slug'];

    public static function scopeFilter (Builder $builder, $filters)
    {
        $builder->when($filters['name'] ?? false, function ($builder, $value) {
            $builder->where(function ($query) use ($value) {
                $query->where('tags.name_en', 'LIKE', "%{$value}%")
                    ->orWhere('tags.name_ar', 'LIKE', "%{$value}%");
            });
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
        // return $this->belongsToMany(Product::class);
        return $this->morphedByMany(Product::class, 'taggable');

    }

     public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

}
