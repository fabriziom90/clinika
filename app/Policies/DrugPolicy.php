<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Drug;

class DrugPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('drug.view');
    }

    public function view(User $user, Drug $drug): bool
    {
        return $user->can('drug.view');
    }

    public function create(User $user): bool
    {
        return $user->can('drug.create');
    }

    public function update(User $user, Drug $drug): bool
    {
        return $user->can('drug.update');
    }

    public function delete(User $user, Drug $drug): bool
    {
        return $user->can('drug.delete');
    }
}
