<?php

namespace App\Providers;

use App\Listeners\SendOrderCreatedNotification;
use App\Models\Products\Product;
use App\Models\User;
use App\Policies\OrderPolice;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       
        Gate::define('update-product',  function (User $user, Product $product) {
           
            return $user->id = $product->user_id;

        });

        // Gate::policy(Product::class, ProductPolicy::class);
    }
}
