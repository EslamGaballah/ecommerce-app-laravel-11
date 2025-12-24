<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
            User::class => UserPolicy::class,

    ];

    public function boot(): void
    {

        $this->registerPolicies();

        // Gate::policy(Product::class, ProductPolicy::class);

        Gate::before(function ($user, $ability) {

            if (!$user) {
            return null;
            }

            if ($user->hasRole('admin')) {
                return true;
            }
            return null;
        });

       
        $permissions = collect(config('permissions'))->flatten(); // flatten return array to string

        foreach ($permissions as $permission) {

            Gate::define($permission, function ($user) use ($permission) {

                return $user->hasPermission($permission);
      
            });
        }
    }
}