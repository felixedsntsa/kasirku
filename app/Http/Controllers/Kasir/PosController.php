<?php

namespace App\Http\Controllers\Kasir;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function __construct(private readonly SaleService $saleService) {}

    /**
     * Halaman POS (kasir).
     */
    public function index()
    {
        return view('kasir.pos');
    }

    /**
     * pencarian produk JSON untuk POS (by nama / SKU / barcode, hanya produk aktif).
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'sku', 'barcode', 'name', 'unit', 'sell_price', 'stock']);

        return response()->json($products);
    }

    /**
     * Proses checkout — delegasi ke SaleService (atomik + lockForUpdate).
     */
    public function checkout(CheckoutRequest $request)
    {
        $data = $request->validated();

        try {
            $sale = $this->saleService->store(
                $data['items'],
                [
                    'paid' => $data['paid'],
                    'payment_method' => $data['payment_method'],
                    'discount' => $data['discount'] ?? 0,
                    'tax' => $data['tax'] ?? 0,
                    'customer_name' => $data['customer_name'] ?? null,
                ],
                $request->user(),
            );
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('kasir.struk', $sale)
            ->with('success', 'Transaksi '.$sale->invoice_no.' tersimpan.');
    }

    /**
     * Halaman struk (siap cetak, 58mm/80mm).
     */
    public function receipt(Sale $sale)
    {
        $sale->load(['items.product:id,name,unit', 'user:id,name']);

        return view('kasir.struk', compact('sale'));
    }

    /**
     * Riwayat transaksi (kasir melihat miliknya; admin melihat semua).
     */
    public function history(Request $request)
    {
        $sales = Sale::with('user:id,name')
            ->unless($request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->withCount('items')
            ->latest('id')
            ->paginate(15);

        return view('kasir.riwayat', compact('sales'));
    }

    /**
     * Void transaksi dari riwayat — stok dikembalikan lewat SaleService (atomik).
     * Kasir hanya boleh membatalkan transaksinya sendiri; admin boleh semua.
     */
    public function void(Request $request, Sale $sale)
    {
        $user = $request->user();

        if (! $user->isAdmin() && $sale->user_id !== $user->id) {
            abort(403, 'Hanya bisa membatalkan transaksi sendiri.');
        }

        try {
            $this->saleService->void($sale, $user);
        } catch (ValidationException $e) {
            return back()->with('error', implode(' ', $e->errors()));
        }

        return redirect()->route('kasir.riwayat')
            ->with('success', 'Transaksi '.$sale->invoice_no.' di-void. Stok telah dikembalikan.');
    }
}
