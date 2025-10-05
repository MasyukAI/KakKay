<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class Product
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
