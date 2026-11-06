<?php

namespace App\Observers;

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartObserver
{
    public function creating(Cart $cart): void
    {
        $cart->id = Str::uuid();

        // Only guest rows share the browser's cookie id; a signed-in user's rows are found by user_id.
        $cart->cookie_id = Auth::check() ? (string) Str::uuid() : Cart::cartCookieId();
    }
}
