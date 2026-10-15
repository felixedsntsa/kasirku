<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Hapus produk — ditolak bila punya riwayat penjualan (jejak audit + FK).
     */
    public function destroy(Product $produk)
    {
        if ($produk->saleItems()->exists()) {
            return redirect()->route('admin.produk.index')->with(
                'error',
                'Produk "'.$produk->name.'" memiliki riwayat penjualan — tidak bisa dihapus. Nonaktifkan saja agar tidak dijual.',
            );
        }

        $produk->delete();

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Export CSV semua produk (fputcsv native PHP — format = template import).
     */
    public function export(): StreamedResponse
    {
        $products = Product::with(['category:id,name', 'supplier:id,name'])
            ->orderBy('name')
            ->get();

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'SKU', 'Nama', 'Kategori', 'Supplier', 'Barcode', 'Satuan',
                'Harga Beli', 'Harga Jual', 'Stok', 'Stok Minimum', 'Status',
            ], ';');

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->category?->name,
                    $product->supplier?->name,
                    $product->barcode,
                    $product->unit,
                    $product->cost_price,
                    $product->sell_price,
                    $product->stock,
                    $product->min_stock,
                    $product->is_active ? 'aktif' : 'nonaktif',
                ], ';');
            }

            fclose($handle);
        }, 'produk_'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Form import CSV produk.
     */
    public function importForm()
    {
        return view('admin.products.import');
    }

    /**
     * Proses import CSV — baris valid diimpor (stok awal lewat StockService),
     * baris gagal dilaporkan per nomor baris.
     *
     * Kategori/supplier kosong di CSV dibuat otomatis (firstOrCreate by name).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:csv,txt'],
        ], [
            'file.mimes' => 'File harus berformat CSV.',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if ($handle === false) {
            return back()->with('error', 'File tidak bisa dibaca.');
        }

        $firstLine = (string) fgets($handle);

        // Deteksi pemisah: template memakai ';' (gaya Excel ID), file umum memakai ','.
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';
        $header = array_map(
            fn ($h) => strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string) $h))),
            str_getcsv($firstLine, $delimiter),
        );

        $map = [];
        foreach (['sku', 'nama', 'kategori', 'supplier', 'barcode', 'satuan', 'harga beli', 'harga jual', 'stok', 'stok minimum', 'status'] as $col) {
            $idx = array_search($col, $header, true);

            if ($idx !== false) {
                $map[$col] = $idx;
            }
        }

        if (! isset($map['sku'], $map['nama'], $map['kategori'])) {
            fclose($handle);

            return back()->with(
                'error',
                'Format file tidak sesuai — kolom "SKU", "Nama", dan "Kategori" wajib ada. Muat dulu template lewat tombol "Export CSV".',
            );
        }

        $errors = [];
        $rows = [];
        $seen = [];
        $line = 1; // baris pertama = header

        while (($data = fgetcsv($handle, null, $delimiter)) !== false) {
            $line++;

            // Lewati baris kosong.
            if (count($data) === 1 && trim((string) $data[0]) === '') {
                continue;
            }

            $get = fn (string $col): string => trim((string) ($data[$map[$col] ?? ''] ?? ''));
            $sku = $get('sku');
            $name = $get('nama');
            $kategori = $get('kategori');

            $rowErrors = [];

            if ($sku === '') {
                $rowErrors[] = 'SKU kosong';
            } elseif (isset($seen[strtoupper($sku)])) {
                $rowErrors[] = 'SKU "'.$sku.'" dobel di dalam file';
            } elseif (Product::where('sku', $sku)->exists()) {
                $rowErrors[] = 'SKU "'.$sku.'" sudah ada di database';
            }

            if ($name === '') {
                $rowErrors[] = 'Nama kosong';
            }

            // products.category_id NOT NULL — kategori wajib (sesuai form produk).
            if ($kategori === '') {
                $rowErrors[] = 'Kategori kosong';
            }

            $numbers = [];
            foreach (['harga beli' => 'cost_price', 'harga jual' => 'sell_price', 'stok' => 'stock', 'stok minimum' => 'min_stock'] as $col => $field) {
                $raw = $get($col);
                $raw = $raw === '' ? '0' : $raw;

                if (! preg_match('/^\d+(\.\d+)?$/', $raw)) {
                    $rowErrors[] = 'Nilai "'.$col.'" bukan angka yang valid ("'.$raw.'")';

                    continue;
                }

                $numbers[$field] = $field === 'stock' || $field === 'min_stock' ? (int) $raw : (float) $raw;
            }

            if ($rowErrors !== []) {
                $errors[] = 'Baris '.$line.': '.implode('; ', $rowErrors);

                continue;
            }

            $seen[strtoupper($sku)] = true;
            $rows[] = [
                'sku' => $sku,
                'name' => $name,
                'kategori' => $get('kategori'),
                'supplier' => $get('supplier'),
                'barcode' => $get('barcode') ?: null,
                'unit' => $get('satuan') ?: 'pcs',
                'status' => strtolower($get('status')),
                ...$numbers,
            ];
        }

        fclose($handle);

        if ($rows === []) {
            $response = back()->with('error', 'Tidak ada baris valid untuk diimpor.');

            return $errors !== [] ? $response->withErrors(['import' => $errors]) : $response;
        }

        DB::transaction(function () use ($rows, $request) {
            foreach ($rows as $row) {
                // Kategori wajib (sudah divalidasi per baris) — buat bila belum ada.
                $category = Category::firstOrCreate(['name' => $row['kategori']]);
                $supplier = $row['supplier'] !== ''
                    ? Supplier::firstOrCreate(['name' => $row['supplier']])
                    : null;

                $initialStock = $row['stock'];
                $row['stock'] = 0;

                $product = Product::create([
                    'category_id' => $category?->id,
                    'supplier_id' => $supplier?->id,
                    'sku' => $row['sku'],
                    'barcode' => $row['barcode'],
                    'name' => $row['name'],
                    'unit' => $row['unit'],
                    'cost_price' => $row['cost_price'],
                    'sell_price' => $row['sell_price'],
                    'stock' => 0,
                    'min_stock' => $row['min_stock'],
                    'is_active' => $row['status'] !== 'nonaktif',
                ]);

                // Stok awal wajib lewat StockService -> jejak di stock_movements.
                if ($initialStock > 0) {
                    $this->stockService->recordIn($product, $initialStock, 'Import CSV', null, $request->user());
                }
            }
        });

        $response = redirect()->route('admin.produk.index')->with(
            'success',
            count($rows).' produk berhasil diimpor.',
        );

        return $errors !== [] ? $response->withErrors(['import' => $errors]) : $response;
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
