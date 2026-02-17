<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'image', // path
        'alt',
        'is_primary'
    ];

public function imageable()
{
    return $this->morphTo();
}
}
