<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Otorisasi produk — hanya admin yang boleh mengelola katalog.
 *
 * Route admin sudah dijaga middleware role:admin; policy ini lapis kedua
 * (defense in depth) sekaligus sumber kebenaran untuk @can di Blade.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
