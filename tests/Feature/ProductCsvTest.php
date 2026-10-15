<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductCsvTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_export_csv_mengunduh_semua_produk(): void
    {
        Product::factory()->create(['sku' => 'EXP-001', 'name' => 'Produk Export']);

        $response = $this->actingAs($this->admin)->get('/admin/produk/export');

        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString('SKU;Nama;Kategori', $content);
        $this->assertStringContainsString('EXP-001', $content);
        $this->assertStringContainsString('Produk Export', $content);
    }

    public function test_kasir_tidak_bisa_akses_csv_produk(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/admin/produk/export')->assertForbidden();
        $this->actingAs($kasir)->get('/admin/produk/import')->assertForbidden();
        $this->actingAs($kasir)->post('/admin/produk/import')->assertForbidden();
    }

    public function test_import_file_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/produk/import', [])
            ->assertSessionHasErrors('file');
    }

    public function test_import_csv_membuat_produk_kategori_supplier_dan_stok_awal(): void
    {
        $csv = implode("\n", [
            'SKU;Nama;Kategori;Supplier;Barcode;Satuan;Harga Beli;Harga Jual;Stok;Stok Minimum;Status',
            'IMP-001;Produk Impor;Kategori Baru;Supplier Baru;;pcs;8000;12000;5;2;aktif',
        ]);

        $this->actingAs($this->admin)
            ->post('/admin/produk/import', ['file' => UploadedFile::fake()->createWithContent('produk.csv', $csv)])
            ->assertRedirect(route('admin.produk.index'));

        $this->assertDatabaseHas('products', ['sku' => 'IMP-001', 'name' => 'Produk Impor', 'stock' => 5, 'is_active' => true]);
        $this->assertDatabaseHas('categories', ['name' => 'Kategori Baru']);
        $this->assertDatabaseHas('suppliers', ['name' => 'Supplier Baru']);

        // Stok awal wajib lewat StockService -> ada jejak movement.
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => Product::where('sku', 'IMP-001')->value('id'),
            'type' => 'in',
            'qty' => 5,
            'note' => 'Import CSV',
        ]);
    }

    public function test_import_baris_gagal_dilaporkan_baris_baik_tetap_masuk(): void
    {
        Product::factory()->create(['sku' => 'SUDAH-ADA']);

        $csv = implode("\n", [
            'SKU;Nama;Kategori;Stok',
            'BARU-001;Produk Baru;Umum;3',
            'BARU-001;Dobel Di File;Umum;2',
            'SUDAH-ADA;Sudah Ada Di DB;Umum;9',
            ';Tanpa SKU;Umum;1',
            'BARU-002;Tanpa Kategori;;1',
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/produk/import', ['file' => UploadedFile::fake()->createWithContent('produk.csv', $csv)]);

        $response->assertRedirect(route('admin.produk.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHasErrors('import'); // daftar baris gagal per nomor baris

        // Hanya baris valid yang masuk; produk lain tidak berubah.
        $this->assertSame(1, Product::where('sku', 'BARU-001')->count());
        $this->assertSame(1, Product::where('sku', 'SUDAH-ADA')->count());
        $this->assertSame(0, Product::where('name', 'Dobel Di File')->count());
        $this->assertSame(0, Product::where('name', 'Sudah Ada Di DB')->count());
        $this->assertSame(0, Product::where('name', 'Tanpa Kategori')->count());
    }

    public function test_import_format_tidak_sesuai_ditolak(): void
    {
        $csv = "kolom1;kolom2\nfoo;bar";

        $response = $this->actingAs($this->admin)
            ->post('/admin/produk/import', ['file' => UploadedFile::fake()->createWithContent('salah.csv', $csv)]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('products', 0);
    }
}
