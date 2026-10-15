<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard admin: kartu ringkasan + daftar stok menipis + transaksi terbaru.
     */
    public function index()
    {
        $today = now()->startOfDay();

        $todayStats = Sale::query()
            ->where('created_at', '>=', $today)
            ->where('status', Sale::STATUS_PAID)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total), 0) as revenue')
            ->first();

        $monthStats = Sale::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', Sale::STATUS_PAID)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total), 0) as revenue')
            ->first();

        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();

        $recentSales = Sale::with('user:id,name')->withCount('items')
            ->latest('id')->limit(8)->get();

        $lowStock = Product::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')->limit(6)->get(['id', 'name', 'unit', 'stock', 'min_stock']);

        return view('admin.dashboard', compact(
            'todayStats', 'monthStats', 'lowStockCount', 'recentSales', 'lowStock',
        ));
    }

    /**
     * Data JSON untuk grafik dashboard (omzet harian 30 hari + top produk).
     */
    public function chart(Request $request): JsonResponse
    {
        $days = min(max((int) $request->query('days', 30), 7), 90);
        $start = now()->subDays($days - 1)->startOfDay();

        // Omzet harian — group by date, kosongkan hari tanpa transaksi jadi 0.
        // groupByRaw + map manual (bukan pluck alias) supaya kompatibel MySQL & SQLite (test).
        $rows = Sale::query()
            ->where('created_at', '>=', $start)
            ->where('status', Sale::STATUS_PAID)
            ->groupByRaw('DATE(created_at)')
            ->selectRaw('DATE(created_at) as date_label, COUNT(*) as transactions, COALESCE(SUM(total), 0) as revenue')
            ->get()
            ->keyBy('date_label');

        $labels = $revenues = $transactions = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $row = $rows->get($date->format('Y-m-d'));

            $labels[] = $date->format('d/m');
            $revenues[] = (float) ($row->revenue ?? 0);
            $transactions[] = (int) ($row->transactions ?? 0);
        }

        // Top produk 30 hari.
        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.created_at', '>=', $start)
            ->where('sales.status', Sale::STATUS_PAID)
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->groupBy('products.name')
            ->selectRaw('products.name, SUM(sale_items.qty) as qty')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return response()->json([
            'labels' => $labels,
            'revenue' => $revenues,
            'transactions' => $transactions,
            'topProducts' => $topProducts->map(fn ($p) => [
                'name' => $p->name,
                'qty' => (int) $p->qty,
            ]),
        ]);
    }
}
