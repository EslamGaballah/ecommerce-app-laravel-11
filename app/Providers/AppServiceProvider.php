<?php

namespace App\Providers;

use App\Interfaces\CartRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Listeners\SendOrderCreatedNotification;
use App\Models\Category;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CartRepositoryInterface::class,
            CartRepository::class
        );

        $this->app->bind(
        ProductRepositoryInterface::class,
        ProductRepository::class
    );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only the storefront header needs the menu.
        View::composer(['layouts.header', 'partials.category-menu'], function ($view) {
            $view->with('categories', Category::menuTree());
        });

    }
}
