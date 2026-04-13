<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValue;


class Attribute extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        ];

    public function getNameAttribute()
    {
        return app()->getLocale() == 'ar'
            ? $this->name_ar
            : $this->name_en;
    }

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
