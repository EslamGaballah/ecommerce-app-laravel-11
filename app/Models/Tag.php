<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public static function scopeFilter (Builder $builder, $filters)
    {
        $builder->when($filters['name'] ?? false, function($builder, $value){
            $builder->where('tags.name', 'LIKE', "%{$value}");
        });

        $builder->when($filters['status'] ?? false, function($builder, $value) {
            $builder->where('tags.status', '=', $value);
        });
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
