<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\CommentPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Category::class => CategoryPolicy::class,
        Product::class => ProductPolicy::class,
        Comment::class => CommentPolicy::class,

    ];

    public function boot(): void
    {

        $this->registerPolicies();

        Gate::policy(Product::class, ProductPolicy::class);

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