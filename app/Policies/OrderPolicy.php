<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-order');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermission('view-order');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-order');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermission('edit-order');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermission('delete-order');
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->hasPermission('restore-order');
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->hasPermission('force-delete-order');
    }
}
