<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SATU-satunya tempat transaksi penjualan dibuat.
 *
 * Alur store() (wajib atomik — lihat AGENTS.md):
 *   DB::transaction()
 *     -> generate invoice_no unik
 *     -> create Sale + SaleItem (harga di-snapshot dari products.sell_price)
 *     -> per item: Product::lockForUpdate() -> cek stok -> kurangi via StockService::recordOut()
 *   -> commit(); bila stok kurang, exception -> rollback total, stok tidak berubah.
 *
 * Kenapa lockForUpdate()? Tanpa lock, dua kasir yang menjual produk yang sama
 * bisa membaca stok yang sama bersamaan (race condition) lalu mengurangi dobel
 * sampai stok minus. Dengan FOR UPDATE, baris product "dikunci" sampai transaksi
 * selesai — kasir kedua menunggu dan membaca stok yang sudah benar.
 */
class SaleService
{
    public function __construct(private readonly StockService $stockService) {}

    /**
     * Simpan transaksi penjualan dari keranjang POS.
     *
     * @param  array<int, array{product_id: int, qty: int}>  $cart
     * @param  array{paid: int|float, payment_method: string, discount?: int|float, tax?: int|float, customer_name?: string|null}  $payment
     *
     * @throws InsufficientStockException saat stok salah satu item kurang (stok tidak berubah).
     * @throws ValidationException saat input bayar tidak valid.
     */
    public function store(array $cart, array $payment, User $user): Sale
    {
        if ($cart === []) {
            throw ValidationException::withMessages(['cart' => 'Keranjang kosong.']);
        }

        return DB::transaction(function () use ($cart, $payment, $user) {
            $sale = Sale::create([
                'invoice_no' => $this->generateInvoiceNo(),
                'user_id' => $user->id,
                'customer_name' => $payment['customer_name'] ?? null,
                'subtotal' => 0,
                'discount' => $payment['discount'] ?? 0,
                'tax' => $payment['tax'] ?? 0,
                'total' => 0,
                'paid' => $payment['paid'],
                'change' => 0,
                'payment_method' => $payment['payment_method'],
                'status' => Sale::STATUS_PAID,
            ]);

            $subtotal = 0;

            foreach ($cart as $line) {
                // Kunci baris product — cegah dua kasir menjual produk sama bersamaan.
                $product = Product::lockForUpdate()->find($line['product_id']);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Ada produk yang tidak ditemukan atau sudah nonaktif. Muat ulang halaman.',
                    ]);
                }

                $qty = (int) $line['qty'];

                if ($qty < 1) {
                    throw ValidationException::withMessages(['cart' => 'Qty minimal 1 untuk setiap item.']);
                }

                if ($product->stock < $qty) {
                    // Lempar di dalam transaction -> rollback total, stok & sale tidak tersimpan.
                    throw new InsufficientStockException($product, $qty, $product->stock);
                }

                $lineSubtotal = (float) $product->sell_price * $qty;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'price' => $product->sell_price, // snapshot harga jual saat ini
                    'qty' => $qty,
                    'subtotal' => $lineSubtotal,
                ]);

                // Perubahan stok wajib lewat StockService -> tercatat di stock_movements.
                $this->stockService->recordOut(
                    $product,
                    $qty,
                    'Penjualan '.$sale->invoice_no,
                    $sale,
                    $user,
                );

                $subtotal += $lineSubtotal;
            }

            $discount = (float) ($payment['discount'] ?? 0);
            $tax = (float) ($payment['tax'] ?? 0);
            $total = $subtotal - $discount + $tax;
            $paid = (float) $payment['paid'];

            if ($total < 0 || $discount > $subtotal) {
                throw ValidationException::withMessages(['discount' => 'Diskon tidak boleh melebihi subtotal.']);
            }

            if ($paid < $total) {
                throw ValidationException::withMessages(['paid' => 'Uang bayar kurang dari total transaksi.']);
            }

            $sale->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'change' => $paid - $total,
            ]);

            return $sale->fresh('items');
        });
    }

    /**
     * Nomor invoice unik berformat INV-YYYYMMDD-0001 (urut per hari).
     * Dipanggil DI DALAM transaction; kombinasi lock + unique index menjamin tidak dobel.
     */
    private function generateInvoiceNo(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';

        $last = Sale::where('invoice_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
