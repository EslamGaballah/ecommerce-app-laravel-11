<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'status',
        'slug',
        'user_id',
    ];

    // using accessors
    public function getExcerptAttribute()
    {
        // show 150 char in blpg.index
        return Str::limit(strip_tags($this->body), 150);
    }

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function images() 
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function tags()
    {
        // return $this->belongsToMany(Tag::class);

        return $this->morphToMany(Tag::class, 'taggable');

    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }
}
