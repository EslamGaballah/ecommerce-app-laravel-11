<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    public $table = 'order_address';

    public $timestamps = false;

    protected $fillable = [
        'order_id', 'first_name', 'last_name', 'email', 'phone_number', 'street_address', 'city', 'state', 'country'
    ];

    public function getNameAttribute() 
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
