<?php

namespace App\Providers;

use App\Listeners\SendOrderCreatedNotification;
use App\Models\Category;
use App\Models\User;
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
            \App\Interfaces\CartRepositoryInterface::class,
            \App\Repositories\CartRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.category-menu', function ($view) {
        $categories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->with('childrenRecursive')
            ->get();

        $view->with('categories', $categories);
        });

    }
}
