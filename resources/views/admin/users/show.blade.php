@extends('layouts.admin')

@section('title', 'Detail Kasir — KasirKu')
@section('heading', 'Detail Kasir')

@section('content')
    <div class="mx-auto max-w-3xl">
        <p class="page-subtitle mb-6">Ringkasan akun dan aktivitas transaksi kasir ini.</p>

        {{-- Info akun --}}
        <div class="card">
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-md bg-accent-50 text-xl font-bold uppercase text-accent-600">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-lg font-bold text-slate-900">{{ $user->name }}</p>
                        <p class="text-sm text-ink-500">{{ $user->email }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-accent capitalize">{{ $user->role }}</span>
                        <span class="badge badge-slate">Bergabung {{ $user->created_at?->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="mt-5 flex items-center gap-3 border-t border-line pt-4">
                    <a href="{{ route('admin.kasir.edit', $user) }}" class="btn btn-primary">Edit Akun</a>
                    <a href="{{ route('admin.kasir.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>

        {{-- Statistik (hanya transaksi lunas; void tidak dihitung) --}}
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Transaksi Lunas</p>
                <p class="mt-1 text-2xl font-bold tabular-nums text-ink-900">{{ $stats->transactions }}</p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Total Penjualan</p>
                <p class="mt-1 text-2xl font-bold tabular-nums text-ink-900">
                    Rp {{ number_format($stats->revenue, 0, ',', '.') }}
                </p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Rata-rata / Transaksi</p>
                <p class="mt-1 text-2xl font-bold tabular-nums text-ink-900">
                    Rp {{ number_format($stats->transactions > 0 ? $stats->revenue / $stats->transactions : 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- 10 transaksi terakhir --}}
        <div class="card mt-6 overflow-hidden">
            <div class="border-b border-line px-6 py-4">
                <p class="text-sm font-semibold text-slate-900">10 Transaksi Terakhir</p>
            </div>
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Waktu</th>
                            <th class="text-right">Item</th>
                            <th class="text-right">Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="font-mono text-xs font-semibold text-slate-900">
                                    <a href="{{ route('kasir.struk', $sale) }}" class="hover:text-accent-600 hover:underline">
                                        {{ $sale->invoice_no }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap text-slate-500">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-right">{{ $sale->items_count }}</td>
                                <td class="text-right font-semibold text-slate-900">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if ($sale->status === \App\Models\Sale::STATUS_PAID)
                                        <span class="badge badge-green">Lunas</span>
                                    @else
                                        <span class="badge badge-red">Void</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">Kasir ini belum memiliki transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
