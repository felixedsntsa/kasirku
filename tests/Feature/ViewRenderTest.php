<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test: semua halaman form & detail harus render 200
 * (menangkap error kompilasi Blade seperti wrapper penengah yang salah).
 */
class ViewRenderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_semua_halaman_form_dan_detail_tampil(): void
    {
        // Halaman auth dicek sebagai guest (admin sudah login -> redirect oleh middleware guest).
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $kasir = User::factory()->create();

        $pages = [
            '/profil',
            '/admin/produk',
            '/admin/produk/create',
            "/admin/produk/{$product->id}/edit",
            '/admin/produk/import',
            '/admin/kategori',
            '/admin/kategori/create',
            "/admin/kategori/{$category->id}/edit",
            '/admin/supplier',
            '/admin/supplier/create',
            "/admin/supplier/{$supplier->id}/edit",
            '/admin/kasir',
            '/admin/kasir/create',
            "/admin/kasir/{$kasir->id}",
            "/admin/kasir/{$kasir->id}/edit",
            '/admin/stok',
            '/admin/stok/masuk',
            '/admin/stok/opname',
            '/kasir',
            '/kasir/riwayat',
        ];

        foreach ($pages as $uri) {
            $this->actingAs($this->admin)->get($uri)->assertOk($uri.' seharusnya 200');
        }
    }

    public function test_halaman_form_kasir_tampil_untuk_role_kasir(): void
    {
        $kasir = User::factory()->create();

        foreach (['/profil', '/kasir', '/kasir/riwayat'] as $uri) {
            $this->actingAs($kasir)->get($uri)->assertOk($uri.' seharusnya 200');
        }
    }
}
