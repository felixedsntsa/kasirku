<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockAdjustRequest;
use App\Http\Requests\StoreStockInRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;

class StockMovementController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    /**
     * Riwayat pergerakan stok (filter produk & tipe, pagination).
     */
    public function index()
    {
        $movements = StockMovement::with(['product:id,name,unit', 'user:id,name'])
            ->when(request('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when(request('type'), fn ($q, $type) => $q->where('type', $type))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('admin.stock.index', compact('movements', 'products'));
    }

    /**
     * Form stok masuk (restock).
     */
    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'unit', 'stock']);

        return view('admin.stock.create', compact('products'));
    }

    /**
     * Proses stok masuk — semua lewat StockService (toko AGENTS.md).
     */
    public function store(StoreStockInRequest $request)
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        $this->stockService->recordIn(
            $product,
            (int) $data['qty'],
            $data['note'] ?? null,
            null,
            $request->user(),
        );

        return redirect()->route('admin.stok.index')->with('success', sprintf(
            'Stok "%s" bertambah %d → sekarang %d %s.',
            $product->name,
            $data['qty'],
            $product->fresh()->stock,
            $product->unit,
        ));
    }

    /**
     * Form stok opname (hitung fisik -> set stok).
     */
    public function opname()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'unit', 'stock']);

        return view('admin.stock.opname', compact('products'));
    }

    /**
     * Proses opname — set stok ke nilai fisik via StockService::adjust().
     */
    public function storeOpname(StoreStockAdjustRequest $request)
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        $this->stockService->adjust(
            $product,
            (int) $data['qty'],
            $data['note'] ?? 'Stok opname fisik',
            $request->user(),
        );

        return redirect()->route('admin.stok.index')->with('success', sprintf(
            'Opname "%s": %d → %d %s.',
            $product->name,
            $data['old_stock'],
            $product->fresh()->stock,
            $product->unit,
        ));
    }
}
