@extends('layouts.admin')

@section('title', 'Riwayat Transaksi — KasirKu')
@section('heading', 'Riwayat Transaksi')

@section('content')
    <p class="page-subtitle mb-6">Daftar transaksi terbaru. Klik invoice untuk melihat / mencetak ulang struk.</p>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th class="text-right">Item</th>
                        <th class="text-right">Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th class="text-right">Struk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="font-mono text-xs font-semibold text-slate-900">{{ $sale->invoice_no }}</td>
                            <td class="whitespace-nowrap text-slate-500">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-slate-600">{{ $sale->user->name ?? '-' }}</td>
                            <td class="text-right">{{ $sale->items_count }}</td>
                            <td class="text-right font-semibold text-slate-900">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                            <td><span class="badge badge-slate">{{ $sale->paymentMethodLabel() }}</span></td>
                            <td>
                                @if ($sale->isPaid())
                                    <span class="badge badge-green">Lunas</span>
                                @else
                                    <span class="badge badge-red">Void</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('kasir.struk', $sale) }}" class="font-medium text-accent-600 hover:underline">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $sales->links() }}
    </div>
@endsection
