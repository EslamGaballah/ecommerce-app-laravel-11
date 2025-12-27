<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ProductPolicy
{
    // public function before(User $user, string $ability): bool|null
    // {
    //     if (!$user) {
    //     return null;
    //     }
        
    //    if ($user->hasRole('admin')) {
    //         return true;
    //     }

    //     return null;  
    // }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-product');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->hasPermission('view-product');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create-product');

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {

        return  $user->hasPermission('edit-product') &&
            $user->id === $product->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return
            $user->hasPermission('delete-product')
            && (
                $user->id === $product->user_id
            );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
       return
            $user->hasPermission('restore-product')
            && (
                $user->id === $product->user_id
            );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return
            $user->hasPermission('force-delete-product')
            && (
                $user->id === $product->user_id
                
            );
    }
}
