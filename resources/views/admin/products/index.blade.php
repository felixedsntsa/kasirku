@extends('layouts.admin')

@section('title', 'Produk — KasirKu')
@section('heading', 'Produk')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="page-subtitle">Katalog produk yang dijual di toko.</p>
        <a href="{{ route('admin.produk.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="size-4" />
            Tambah Produk
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th class="text-right">Harga Jual</th>
                        <th class="text-right">Stok</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                        <x-icon name="box" class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900">{{ $product->name }}</p>
                                        <p class="truncate font-mono text-xs text-slate-400">{{ $product->sku }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-600">{{ $product->category->name ?? '—' }}</td>
                            <td class="text-slate-600">{{ $product->supplier->name ?? '—' }}</td>
                            <td class="text-right font-semibold text-slate-900">
                                Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                @if ($product->stock <= 0)
                                    <span class="badge badge-red">{{ $product->stock }} {{ $product->unit }}</span>
                                @elseif ($product->stock <= $product->min_stock)
                                    <span class="badge badge-amber">{{ $product->stock }} {{ $product->unit }}</span>
                                @else
                                    <span class="text-slate-700">{{ $product->stock }} {{ $product->unit }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($product->is_active)
                                    <span class="badge badge-green">Aktif</span>
                                @else
                                    <span class="badge badge-slate">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.produk.edit', $product) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.produk.destroy', $product) }}"
                                          onsubmit="return confirm('Hapus produk {{ $product->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:text-rose-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
@endsection
