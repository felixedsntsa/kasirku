<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

/**
 * Otorisasi transaksi penjualan.
 *
 * Kasir hanya boleh melihat struk & membatalkan (void) transaksi miliknya sendiri;
 * admin boleh semua. Aturan ini dipakai di PosController dan tombol @can di Blade.
 */
class SalePolicy
{
    /**
     * Lihat struk / detail transaksi.
     */
    public function view(User $user, Sale $sale): bool
    {
        return $this->owns($user, $sale);
    }

    /**
     * Void transaksi (mengembalikan stok) — hanya pemilik atau admin.
     */
    public function void(User $user, Sale $sale): bool
    {
        return $this->owns($user, $sale);
    }

    /**
     * Transaksi milik user ini, atau user admin (boleh semua).
     */
    private function owns(User $user, Sale $sale): bool
    {
        return $user->isAdmin() || $sale->user_id === $user->id;
    }
}
