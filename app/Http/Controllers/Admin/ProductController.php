<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    /**
     * Daftar produk (tabel + pagination). Eager load relasi — hindari N+1.
     */
    public function index()
    {
        $products = Product::with(['category:id,name', 'supplier:id,name'])
            ->orderBy('name')
            ->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Form tambah produk (dropdown kategori + supplier).
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'suppliers'));
    }

    /**
     * Simpan produk baru.
     *
     * Stok awal TIDAK ditulis langsung ke products.stock — dibuat 0 dulu,
     * lalu dicatat lewat StockService::recordIn() supaya ada jejak di stock_movements.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $this->data($request->validated());
        $initialStock = (int) $data['stock'];
        $data['stock'] = 0;

        $product = DB::transaction(function () use ($data, $initialStock, $request) {
            $product = Product::create($data);

            if ($initialStock > 0) {
                $this->stockService->recordIn($product, $initialStock, 'Stok awal produk baru', null, $request->user());
            }

            return $product;
        });

        return redirect()->route('admin.produk.index')->with('success', 'Produk "'.$product->name.'" berhasil ditambahkan.');
    }

    /**
     * Form edit produk.
     */
    public function edit(Product $produk)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.products.edit', compact('produk', 'categories', 'suppliers'));
    }

    /**
     * Update produk (tanpa kolom stok — stok hanya lewat StockService).
     */
    public function update(UpdateProductRequest $request, Product $produk)
    {
        $produk->update($this->data($request->validated()));

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus produk.
     */
    public function destroy(Product $produk)
    {
        $produk->delete();

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Normalisasi input: is_active checkbox (on/off) -> boolean, supplier kosong -> null.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function data(array $data): array
    {
        $data['is_active'] = $data['is_active'] ?? false;
        $data['supplier_id'] = $data['supplier_id'] ?: null;

        return $data;
    }
}
