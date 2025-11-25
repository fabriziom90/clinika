<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InventoryProduct;

class InventoryProductPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('inventory-product.view');
    }

    public function view(User $user, InventoryProduct $inventoryProduct): bool
    {
        return $user->can('inventory-product.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory-product.create');
    }

    public function update(User $user, InventoryProduct $inventoryProduct): bool
    {
        return $user->can('inventory-product.update');
    }

    public function delete(User $user, InventoryProduct $inventoryProduct): bool
    {
        return $user->can('inventory-product.delete');
    }
}
