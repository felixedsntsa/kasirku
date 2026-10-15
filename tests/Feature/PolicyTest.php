<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Test Gate/Policy: otorisasi per-object (produk & transaksi).
 */
class PolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Buat satu transaksi milik $user (stok product 10, jual 3).
     */
    private function checkoutFor(User $user): Sale
    {
        $product = Product::factory()->create(['stock' => 10, 'sell_price' => 10000, 'is_active' => true]);

        return app(SaleService::class)->store(
            [['product_id' => $product->id, 'qty' => 3]],
            ['paid' => 100000, 'payment_method' => 'cash'],
            $user,
        );
    }

    public function test_product_policy_hanya_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->create();
        $product = Product::factory()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Product::class));
        $this->assertTrue(Gate::forUser($admin)->allows('create', Product::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $product));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $product));

        $this->assertFalse(Gate::forUser($kasir)->allows('viewAny', Product::class));
        $this->assertFalse(Gate::forUser($kasir)->allows('create', Product::class));
        $this->assertFalse(Gate::forUser($kasir)->allows('update', $product));
        $this->assertFalse(Gate::forUser($kasir)->allows('delete', $product));
    }

    public function test_sale_policy_kasir_hanya_untuk_transaksi_sendiri(): void
    {
        $kasir = User::factory()->create();
        $kasirLain = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $sale = $this->checkoutFor($kasir);

        $this->assertTrue(Gate::forUser($kasir)->allows('view', $sale));
        $this->assertTrue(Gate::forUser($kasir)->allows('void', $sale));

        $this->assertFalse(Gate::forUser($kasirLain)->allows('view', $sale));
        $this->assertFalse(Gate::forUser($kasirLain)->allows('void', $sale));

        $this->assertTrue(Gate::forUser($admin)->allows('view', $sale));
        $this->assertTrue(Gate::forUser($admin)->allows('void', $sale));
    }

    public function test_kasir_tidak_bisa_lihat_struk_kasir_lain(): void
    {
        $kasir = User::factory()->create();
        $kasirLain = User::factory()->create();

        $sale = $this->checkoutFor($kasir);

        // Pemilik sendiri: 200.
        $this->actingAs($kasir)->get("/kasir/struk/{$sale->id}")->assertOk();

        // Kasir lain: 403.
        $this->actingAs($kasirLain)->get("/kasir/struk/{$sale->id}")->assertForbidden();
    }

    public function test_admin_bisa_lihat_struk_semua_transaksi(): void
    {
        $kasir = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $sale = $this->checkoutFor($kasir);

        $this->actingAs($admin)->get("/kasir/struk/{$sale->id}")->assertOk();
    }
}
