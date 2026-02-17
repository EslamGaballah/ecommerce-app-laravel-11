<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValue;


class Attribute extends Model
{
    protected $fillable = ['name'];

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
