@extends('layouts.admin')

@section('title', 'Dashboard — KasirKu')
@section('heading', 'Dashboard')

@section('content')
    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="flex size-11 items-center justify-center rounded-md bg-accent-50 text-accent-600">
                    <x-icon name="cash" class="size-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Omzet Hari Ini</p>
                    <p class="truncate text-xl font-bold tabular-nums text-slate-900">
                        Rp {{ number_format($todayStats->revenue, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="flex size-11 items-center justify-center rounded-md bg-slate-100 text-slate-500">
                    <x-icon name="receipt" class="size-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Transaksi Hari Ini</p>
                    <p class="text-xl font-bold tabular-nums text-slate-900">{{ $todayStats->transactions }}</p>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="flex size-11 items-center justify-center rounded-md bg-slate-100 text-slate-500">
                    <x-icon name="chart" class="size-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Omzet Bulan Ini</p>
                    <p class="truncate text-xl font-bold tabular-nums text-slate-900">
                        Rp {{ number_format($monthStats->revenue, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.stok.index') }}" class="card p-5">
            <div class="flex items-center gap-4">
                <div class="flex size-11 items-center justify-center rounded-md {{ $lowStockCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500' }}">
                    <x-icon name="alert" class="size-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Stok Menipis</p>
                    <p class="text-xl font-bold tabular-nums {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                        {{ $lowStockCount }} produk
                    </p>
                </div>
            </div>
        </a>
    </div>

    {{-- Grafik --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="card-header flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Penjualan 30 Hari Terakhir</h2>
                    <p class="text-xs text-ink-500">Omzet harian (status lunas)</p>
                </div>
                <span class="badge badge-accent">30 hari</span>
            </div>
            <div class="p-4">
                <canvas id="salesChart" height="120"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">5 Produk Terlaris</h2>
                <p class="text-xs text-ink-500">Berdasarkan jumlah terjual</p>
            </div>
            <div id="topProducts" class="space-y-4 p-5">
                <p class="empty-state py-6">Memuat…</p>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Transaksi terbaru --}}
        <div class="card xl:col-span-2">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Transaksi Terbaru</h2>
                <a href="{{ route('kasir.riwayat') }}" class="text-xs font-medium text-accent-600 hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentSales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('kasir.struk', $sale) }}" class="font-mono text-xs font-semibold text-accent-600 hover:underline">
                                        {{ $sale->invoice_no }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap text-slate-500">{{ $sale->created_at->format('d/m H:i') }}</td>
                                <td class="text-slate-600">{{ $sale->user->name ?? '-' }}</td>
                                <td class="text-right font-semibold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">Belum ada transaksi. Mulai dari <a href="{{ route('kasir.home') }}" class="text-accent-600 hover:underline">halaman kasir</a>.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Stok menipis --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Peringatan Stok</h2>
                <p class="text-xs text-ink-500">Stok di bawah atau sama dengan minimum</p>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($lowStock as $product)
                    <li class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $product->name }}</p>
                            <p class="text-xs text-ink-500">min. {{ $product->min_stock }} {{ $product->unit }}</p>
                        </div>
                        <span class="badge {{ $product->stock == 0 ? 'badge-red' : 'badge-amber' }}">
                            {{ $product->stock }} {{ $product->unit }}
                        </span>
                    </li>
                @empty
                    <li class="empty-state">Semua stok aman.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            fetch(@json(route('admin.dashboard.chart')))
                .then(res => res.json())
                .then(data => {
                    // Grafik garis omzet harian.
                    new Chart(document.getElementById('salesChart'), {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Omzet',
                                data: data.revenue,
                                borderColor: '#0f766e',
                                backgroundColor: (ctx) => {
                                    const { ctx: c, chartArea } = ctx.chart;
                                    if (!chartArea) return 'rgba(15,118,110,.1)';
                                    const grad = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                    grad.addColorStop(0, 'rgba(15,118,110,.25)');
                                    grad.addColorStop(1, 'rgba(15,118,110,0)');
                                    return grad;
                                },
                                fill: true,
                                tension: 0.35,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                borderWidth: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y),
                                    },
                                },
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { maxTicksLimit: 10, color: '#6b7280', font: { size: 11 } } },
                                y: {
                                    grid: { color: '#e5e7eb' },
                                    border: { display: false },
                                    ticks: {
                                        color: '#6b7280',
                                        font: { size: 11 },
                                        callback: (v) => v >= 1000000 ? (v / 1000000) + ' jt' : (v >= 1000 ? (v / 1000) + ' rb' : v),
                                    },
                                },
                            },
                        },
                    });

                    // Daftar top produk.
                    const el = document.getElementById('topProducts');
                    if (!data.topProducts.length) {
                        el.innerHTML = '<p class="empty-state py-6">Belum ada data penjualan.</p>';
                        return;
                    }
                    const max = Math.max(...data.topProducts.map(p => p.qty));
                    el.innerHTML = data.topProducts.map((p, i) => `
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">${i + 1}. ${p.name}</span>
                                <span class="font-semibold tabular-nums text-slate-900">${p.qty} terjual</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-sm bg-slate-100">
                                <div class="h-full bg-accent-600" style="width: ${max ? (p.qty / max) * 100 : 0}%"></div>
                            </div>
                        </div>
                    `).join('');
                });
        </script>
    @endpush
@endsection
