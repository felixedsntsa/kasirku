<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * SATU-satunya tempat kolom products.stock boleh diubah (lihat AGENTS.md).
 *
 * products.stock hanyalah cache dari riwayat stock_movements:
 * - recordIn()  -> stok + qty,  movement type=in
 * - recordOut() -> stok - qty,  movement type=out (ditolak bila stok kurang)
 * - adjust()    -> stok = qty,  movement type=adjust (opname stok fisik)
 *
 * Semua operasi berjalan dalam DB transaction + lockForUpdate() pada baris
 * product, supaya dua kasir yang bertransaksi bersamaan tidak membuat stok minus.
 */
class StockService
{
    /**
     * Catat stok masuk (pembelian/restock).
     *
     * @param  string|null  $note  keterangan, mis. "Restock dari supplier X"
     * @param  Model|null  $reference  sumber (mis. PurchaseOrder) — dicatat sebagai polymorphic
     */
    public function recordIn(Product $product, int $qty, ?string $note = null, ?Model $reference = null, ?User $user = null): StockMovement
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Qty stok masuk harus lebih dari 0.');
        }

        return $this->change($product, $qty, StockMovement::TYPE_IN, $note, $reference, $user);
    }

    /**
     * Catat stok keluar (penjualan/penyesuaian manual).
     *
     * @throws InsufficientStockException bila stok akan menjadi minus.
     */
    public function recordOut(Product $product, int $qty, ?string $note = null, ?Model $reference = null, ?User $user = null): StockMovement
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Qty stok keluar harus lebih dari 0.');
        }

        return $this->change($product, -$qty, StockMovement::TYPE_OUT, $note, $reference, $user);
    }

    /**
     * Opname: SET stok ke nilai fisik baru (qty = stok hasil hitung fisik).
     */
    public function adjust(Product $product, int $newQty, ?string $note = null, ?User $user = null): StockMovement
    {
        if ($newQty < 0) {
            throw new \InvalidArgumentException('Stok fisik tidak boleh negatif.');
        }

        return DB::transaction(function () use ($product, $newQty, $note, $user) {
            // Kunci baris product — cegah race condition antar kasir.
            $fresh = Product::lockForUpdate()->findOrFail($product->id);

            $fresh->stock = $newQty;
            $fresh->save();

            return StockMovement::create([
                'product_id' => $fresh->id,
                'type' => StockMovement::TYPE_ADJUST,
                'qty' => $newQty,
                'note' => $note,
                'user_id' => $user?->id,
            ]);
        });
    }

    /**
     * Terapkan perubahan stok relatif (delta positif/negatif) + tulis movement.
     */
    private function change(Product $product, int $delta, string $type, ?string $note, ?Model $reference, ?User $user): StockMovement
    {
        return DB::transaction(function () use ($product, $delta, $type, $note, $reference, $user) {
            // Kunci baris product — cegah race condition antar kasir.
            $fresh = Product::lockForUpdate()->findOrFail($product->id);

            $newStock = $fresh->stock + $delta;

            if ($newStock < 0) {
                // Lempar di dalam transaction -> otomatis rollback, stok tetap utuh.
                throw new InsufficientStockException($fresh, abs($delta), $fresh->stock);
            }

            $fresh->stock = $newStock;
            $fresh->save();

            return StockMovement::create([
                'product_id' => $fresh->id,
                'type' => $type,
                'qty' => abs($delta),
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'note' => $note,
                'user_id' => $user?->id,
            ]);
        });
    }
}
