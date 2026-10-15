<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Laporan penjualan (rentang tanggal) + produk terlaris + stok menipis.
     */
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        // Ringkasan: agregat dalam 1 query (hindari N+1 / loop query).
        $summary = Sale::query()
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->where('status', Sale::STATUS_PAID)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total), 0) as revenue, COALESCE(SUM(discount), 0) as discounts')
            ->first();

        $itemsSold = SaleItem::query()
            ->whereHas('sale', fn ($q) => $q
                ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
                ->where('status', Sale::STATUS_PAID))
            ->sum('qty');

        // Detail transaksi terbaru (eager load kasir — hindari N+1).
        $sales = Sale::with('user:id,name')
            ->withCount('items')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Produk terlaris top 10 (group by, sum qty).
        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$from, $to->copy()->endOfDay()])
            ->where('sales.status', Sale::STATUS_PAID)
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->groupBy('sale_items.product_id', 'products.name', 'products.unit')
            ->selectRaw('sale_items.product_id, products.name, products.unit, SUM(sale_items.qty) as qty, SUM(sale_items.subtotal) as revenue')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // Stok menipis (stock <= min_stock).
        $lowStock = Product::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'name', 'unit', 'stock', 'min_stock']);

        return view('admin.reports.sales', compact(
            'summary', 'itemsSold', 'sales', 'topProducts', 'lowStock', 'from', 'to',
        ));
    }

    /**
     * Export CSV laporan penjualan (fputcsv native PHP — tanpa package).
     */
    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $filename = sprintf('laporan-penjualan_%s_%s.csv', $from->format('Ymd'), $to->format('Ymd'));

        $sales = Sale::with('user:id,name')
            ->withCount('items')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Invoice', 'Tanggal', 'Kasir', 'Item', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Bayar', 'Kembalian', 'Metode', 'Status',
            ], ';');

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_no,
                    $sale->created_at->format('d/m/Y H:i'),
                    $sale->user->name ?? '-',
                    $sale->items_count,
                    $sale->subtotal,
                    $sale->discount,
                    $sale->tax,
                    $sale->total,
                    $sale->paid,
                    $sale->change,
                    $sale->paymentMethodLabel(),
                    $sale->status,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Rentang tanggal dari query string (?from=YYYY-MM-DD&to=YYYY-MM-DD), default bulan berjalan.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))
            : now();

        if ($to->lessThan($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->startOfDay()];
    }
}
