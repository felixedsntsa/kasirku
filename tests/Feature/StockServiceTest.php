<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_in_menambah_stok_dan_mencatat_movement(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $user = User::factory()->create();

        app(StockService::class)->recordIn($product, 10, 'Restock supplier', null, $user);

        $this->assertSame(15, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_IN,
            'qty' => 10,
            'user_id' => $user->id,
        ]);
    }

    public function test_record_out_ditolak_bila_stok_kurang(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->expectException(InsufficientStockException::class);

        try {
            app(StockService::class)->recordOut($product, 5);
        } finally {
            $this->assertSame(3, $product->fresh()->stock);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }

    public function test_adjust_mengeset_stok_ke_nilai_fisik(): void
    {
        $product = Product::factory()->create(['stock' => 20]);
        $user = User::factory()->create();

        app(StockService::class)->adjust($product, 17, 'Opname rak depan', $user);

        $this->assertSame(17, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_ADJUST,
            'qty' => 17,
        ]);
    }

    public function test_qty_masuk_negatif_ditolak(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->expectException(\InvalidArgumentException::class);

        app(StockService::class)->recordIn($product, -3);
    }
}
