<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VoidTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = User::factory()->create();
    }

    /**
     * Checkout langsung lewat SaleService — stok 10, jual 3 -> sisa 7.
     *
     * @return array{0: Product, 1: Sale}
     */
    private function checkout(int $qty = 3): array
    {
        $product = Product::factory()->create(['stock' => 10, 'sell_price' => 10000, 'is_active' => true]);

        $sale = app(SaleService::class)->store(
            [['product_id' => $product->id, 'qty' => $qty]],
            ['paid' => 100000, 'payment_method' => 'cash'],
            $this->kasir,
        );

        return [$product, $sale];
    }

    public function test_void_mengembalikan_stok_dan_menandai_status(): void
    {
        [$product, $sale] = $this->checkout(3);
        $this->assertSame(7, $product->fresh()->stock);

        app(SaleService::class)->void($sale, $this->kasir);

        // Stok kembali penuh + status void + jejak movement type=in.
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(Sale::STATUS_VOID, $sale->fresh()->status);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_IN,
            'qty' => 3,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_void_dobel_ditolak_dan_stok_tidak_digandakan(): void
    {
        [$product, $sale] = $this->checkout();

        $service = app(SaleService::class);
        $service->void($sale, $this->kasir);

        try {
            $service->void($sale, $this->kasir);
            $this->fail('Seharusnya melempar ValidationException untuk void dobel.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame(10, $product->fresh()->stock, 'Stok tidak boleh digandakan oleh void dobel.');
    }

    public function test_kasir_bisa_void_transaksi_sendiri(): void
    {
        [, $sale] = $this->checkout();

        $this->actingAs($this->kasir)
            ->post("/kasir/riwayat/{$sale->id}/void")
            ->assertRedirect(route('kasir.riwayat'));

        $this->assertSame(Sale::STATUS_VOID, $sale->fresh()->status);
    }

    public function test_kasir_tidak_bisa_void_transaksi_kasir_lain(): void
    {
        [, $sale] = $this->checkout();
        $kasirLain = User::factory()->create();

        $this->actingAs($kasirLain)
            ->post("/kasir/riwayat/{$sale->id}/void")
            ->assertForbidden();

        $this->assertSame(Sale::STATUS_PAID, $sale->fresh()->status);
    }

    public function test_admin_bisa_void_transaksi_kasir_lain(): void
    {
        [, $sale] = $this->checkout();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post("/kasir/riwayat/{$sale->id}/void")
            ->assertRedirect(route('kasir.riwayat'));

        $this->assertSame(Sale::STATUS_VOID, $sale->fresh()->status);
    }

    public function test_guest_tidak_bisa_void(): void
    {
        [, $sale] = $this->checkout();

        $this->post("/kasir/riwayat/{$sale->id}/void")->assertRedirect('/login');

        $this->assertSame(Sale::STATUS_PAID, $sale->fresh()->status);
    }

    public function test_transaksi_void_tidak_dihitung_lagi_sebagai_penjualan(): void
    {
        [, $sale] = $this->checkout(3);
        app(SaleService::class)->void($sale, $this->kasir);

        // Query yang dipakai dashboard & laporan (STATUS_PAID) tidak lagi menghitungnya.
        $this->assertSame(0, Sale::where('status', Sale::STATUS_PAID)->count());
        $this->assertSame(1, Sale::where('status', Sale::STATUS_VOID)->count());
    }
}
