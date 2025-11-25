<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InventoryDrug;

class InventoryDrugPolicy
{
    /**
     * Create a new policy instance.
     */
   public function viewAny(User $user): bool
    {
        return $user->can('inventory-drug.view');
    }

    public function view(User $user, InventoryDrug $inventoryDrug): bool
    {
        return $user->can('inventory-drug.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory-drug.create');
    }

    public function update(User $user, InventoryDrug $inventoryDrug): bool
    {
        return $user->can('inventory-drug.update');
    }

    public function delete(User $user, InventoryDrug $inventoryDrug): bool
    {
        return $user->can('inventory-drug.delete');
    }
}
