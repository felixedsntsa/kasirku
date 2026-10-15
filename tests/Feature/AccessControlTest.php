<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirect_ke_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/kasir')->assertRedirect('/login');
    }

    public function test_kasir_tidak_bisa_akses_route_admin(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/admin')->assertForbidden();
        $this->actingAs($kasir)->get('/admin/produk')->assertForbidden();
        $this->actingAs($kasir)->get('/admin/stok')->assertForbidden();
        $this->actingAs($kasir)->get('/admin/laporan')->assertForbidden();
    }

    public function test_admin_bisa_akses_dashboard_dan_master_data(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/produk')->assertOk();
        $this->actingAs($admin)->get('/admin/kategori')->assertOk();
        $this->actingAs($admin)->get('/admin/supplier')->assertOk();
        $this->actingAs($admin)->get('/admin/stok')->assertOk();
        $this->actingAs($admin)->get('/admin/laporan')->assertOk();
    }

    public function test_admin_bisa_crud_kategori(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/kategori', ['name' => 'Kategori Uji'])
            ->assertRedirect('/admin/kategori');

        $this->assertDatabaseHas('categories', ['name' => 'Kategori Uji']);

        $category = Category::first();

        $this->actingAs($admin)->put("/admin/kategori/{$category->id}", ['name' => 'Kategori Diubah'])
            ->assertRedirect('/admin/kategori');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Kategori Diubah']);

        $this->actingAs($admin)->delete("/admin/kategori/{$category->id}")
            ->assertRedirect('/admin/kategori');

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_kasir_bisa_akses_pos(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/kasir')->assertOk();
        $this->actingAs($kasir)->get('/kasir/riwayat')->assertOk();
    }

    public function test_pos_search_hanya_produk_aktif(): void
    {
        $kasir = User::factory()->create();
        $aktif = Product::factory()->create(['name' => 'Produk Aktif', 'is_active' => true]);
        Product::factory()->create(['name' => 'Produk Nonaktif', 'is_active' => false]);

        $response = $this->actingAs($kasir)->getJson('/kasir/produk?q=Produk')->assertOk();

        $names = collect($response->json())->pluck('name');
        $this->assertTrue($names->contains('Produk Aktif'));
        $this->assertFalse($names->contains('Produk Nonaktif'));
    }
}
