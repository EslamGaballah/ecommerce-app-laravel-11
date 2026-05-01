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
        // View::composer('partials.category-menu', function ($view) {  // add data to selected view only
        View::composer('*', function ($view) { // add data to all views
        // $categories = Category::whereNull('parent_id')
        $categories = Category::where('status', 'active')
            ->with('childrenRecursive')
            ->withCount('products')
            ->get();

        $view->with('categories', $categories);
        });

    }
}
