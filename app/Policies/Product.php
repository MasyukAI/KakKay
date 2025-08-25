<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class Product
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return true;
    }

    public function view_any(User $user): bool
    {
        return true;
    }
}
