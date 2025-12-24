<?php

namespace App\Providers;

use App\Listeners\SendOrderCreatedNotification;
use App\Models\Product;
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
        $this->app->bind(
            \App\Interfaces\CartRepositoryInterface::class,
            \App\Repositories\CartRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
   
        
    }
}
