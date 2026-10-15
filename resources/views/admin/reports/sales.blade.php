@extends('layouts.admin')

@section('title', 'Laporan — KasirKu')
@section('heading', 'Laporan Penjualan')

@section('content')
    {{-- Filter tanggal + export --}}
    <form method="GET" action="{{ route('admin.laporan.index') }}" class="card mb-6 p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label for="from" class="label">Dari Tanggal</label>
                <input id="from" type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="input" />
            </div>
            <div>
                <label for="to" class="label">Sampai Tanggal</label>
                <input id="to" type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}" class="input" />
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-ghost">Reset</a>
            </div>
            <div class="flex items-end justify-end">
                <a href="{{ route('admin.laporan.export', request()->query()) }}" class="btn btn-secondary">
                    <x-icon name="arrow-down" class="size-4" />
                    Export CSV
                </a>
            </div>
        </div>
    </form>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Omzet</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">Rp {{ number_format($summary->revenue, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Jumlah Transaksi</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary->transactions) }}</p>
            <p class="mt-1 text-xs text-slate-400">
                rata-rata Rp {{ number_format($summary->transactions > 0 ? $summary->revenue / $summary->transactions : 0, 0, ',', '.') }} / transaksi
            </p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Item Terjual</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($itemsSold) }}</p>
            <p class="mt-1 text-xs text-slate-400">total unit pada rentang ini</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Detail transaksi --}}
        <div class="card xl:col-span-2 overflow-hidden">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Detail Transaksi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th class="text-right">Item</th>
                            <th class="text-right">Diskon</th>
                            <th class="text-right">Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('kasir.struk', $sale) }}" class="font-mono text-xs font-semibold text-brand-600 hover:underline">
                                        {{ $sale->invoice_no }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap text-slate-500">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-slate-600">{{ $sale->user->name ?? '-' }}</td>
                                <td class="text-right">{{ $sale->items_count }}</td>
                                <td class="text-right text-slate-500">
                                    @if ((float) $sale->discount > 0)
                                        Rp {{ number_format($sale->discount, 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-right font-semibold text-slate-900">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                <td>
                                    @if ($sale->isPaid())
                                        <span class="badge badge-green">Lunas</span>
                                    @else
                                        <span class="badge badge-red">Void</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">Tidak ada transaksi pada rentang tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $sales->links() }}
            </div>
        </div>

        <div class="space-y-6">
            {{-- Produk terlaris --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-semibold text-slate-900">Produk Terlaris</h2>
                    <p class="text-xs text-slate-400">Top 10 pada rentang ini</p>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($topProducts as $index => $product)
                        <li class="flex items-center justify-between px-5 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">
                                    {{ $index + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $product->name }}</p>
                                    <p class="text-xs text-slate-400">Rp {{ number_format($product->revenue, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <span class="badge badge-brand">{{ $product->qty }} {{ $product->unit }}</span>
                        </li>
                    @empty
                        <li class="empty-state py-8">Belum ada penjualan.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Stok menipis --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-semibold text-slate-900">Stok Menipis</h2>
                    <p class="text-xs text-slate-400">Segera lakukan restock</p>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($lowStock as $product)
                        <li class="flex items-center justify-between px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $product->name }}</p>
                                <p class="text-xs text-slate-400">min. {{ $product->min_stock }} {{ $product->unit }}</p>
                            </div>
                            <span class="badge {{ $product->stock == 0 ? 'badge-red' : 'badge-amber' }}">
                                {{ $product->stock }} {{ $product->unit }}
                            </span>
                        </li>
                    @empty
                        <li class="empty-state py-8">Semua stok aman. 🎉</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
