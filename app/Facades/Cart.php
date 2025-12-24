<?php

namespace App\Facades;

use App\Interfaces\CartRepositoryInterface;
use Illuminate\Support\Facades\Facade;

class Cart extends Facade 
{
    protected static function getFacadeAccessor()
    {
        return CartRepositoryInterface::class;
    }
}