<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = User::factory()->create();
    }

    public function test_transaksi_mengurangi_stok_dan_mencatat_movement(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'sell_price' => 10000, 'is_active' => true]);

        $sale = app(SaleService::class)->store(
            [['product_id' => $product->id, 'qty' => 3]],
            ['paid' => 100000, 'payment_method' => 'cash'],
            $this->kasir,
        );

        // Stok berkurang 10 -> 7.
        $this->assertSame(7, $product->fresh()->stock);

        // Movement type=out qty=3 tercatat dengan referensi sale.
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_OUT,
            'qty' => 3,
            'reference_id' => $sale->id,
        ]);

        // Sale & item tersimpan dengan invoice & total benar.
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'user_id' => $this->kasir->id,
            'status' => Sale::STATUS_PAID,
        ]);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 3,
        ]);
        $this->assertSame((float) $product->sell_price * 3, (float) $sale->total);
    }

    public function test_transaksi_dengan_stok_kurang_gagal_dan_stok_tidak_berubah(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'is_active' => true]);

        try {
            app(SaleService::class)->store(
                [['product_id' => $product->id, 'qty' => 5]],
                ['paid' => 100000, 'payment_method' => 'cash'],
                $this->kasir,
            );
            $this->fail('Seharusnya melempar InsufficientStockException.');
        } catch (InsufficientStockException $e) {
            $this->assertSame(5, $e->requested);
            $this->assertSame(2, $e->available);
        }

        // Stok tidak berubah, tidak ada sale, tidak ada movement.
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_checkout_gagal_sebagian_rollback_total(): void
    {
        // 2 produk: satu cukup stok, satu tidak — SEMUA harus dibatalkan.
        $ok = Product::factory()->create(['stock' => 10, 'is_active' => true]);
        $lack = Product::factory()->create(['stock' => 1, 'is_active' => true]);

        try {
            app(SaleService::class)->store(
                [
                    ['product_id' => $ok->id, 'qty' => 2],
                    ['product_id' => $lack->id, 'qty' => 9],
                ],
                ['paid' => 100000, 'payment_method' => 'cash'],
                $this->kasir,
            );
            $this->fail('Seharusnya melempar InsufficientStockException.');
        } catch (InsufficientStockException) {
            // expected
        }

        // Produk pertama juga tidak jadi berkurang (rollback total).
        $this->assertSame(10, $ok->fresh()->stock);
        $this->assertSame(1, $lack->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_nomor_invoice_urut_per_hari(): void
    {
        $product = Product::factory()->create(['stock' => 100, 'sell_price' => 1000, 'is_active' => true]);
        $service = app(SaleService::class);

        $cart = [['product_id' => $product->id, 'qty' => 1]];
        $payment = ['paid' => 100000, 'payment_method' => 'cash'];

        $first = $service->store($cart, $payment, $this->kasir);
        $second = $service->store($cart, $payment, $this->kasir);

        $this->assertSame('INV-'.now()->format('Ymd').'-0001', $first->invoice_no);
        $this->assertSame('INV-'.now()->format('Ymd').'-0002', $second->invoice_no);
    }

    public function test_checkout_menolak_bayar_kurang(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'sell_price' => 50000, 'is_active' => true]);

        $this->expectException(ValidationException::class);

        try {
            app(SaleService::class)->store(
                [['product_id' => $product->id, 'qty' => 2]], // total 100rb
                ['paid' => 50000, 'payment_method' => 'cash'], // bayar 50rb
                $this->kasir,
            );
        } finally {
            // Stok tidak boleh berubah.
            $this->assertSame(10, $product->fresh()->stock);
            $this->assertDatabaseCount('sales', 0);
        }
    }
}
