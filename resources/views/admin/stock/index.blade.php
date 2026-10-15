@extends('layouts.admin')

@section('title', 'Riwayat Stok — KasirKu')
@section('heading', 'Riwayat Stok')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="page-subtitle">Semua pergerakan stok: masuk, keluar, dan opname.</p>
        <div class="flex gap-3">
            <a href="{{ route('admin.stok.create') }}" class="btn btn-primary">
                <x-icon name="arrow-down" class="size-4" />
                Stok Masuk
            </a>
            <a href="{{ route('admin.stok.opname') }}" class="btn btn-secondary">
                <x-icon name="adjust" class="size-4" />
                Stok Opname
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.stok.index') }}" class="card mb-6 p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="product_id" class="label">Produk</label>
                <select id="product_id" name="product_id" class="input">
                    <option value="">— Semua produk —</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="type" class="label">Tipe</label>
                <select id="type" name="type" class="input">
                    <option value="">— Semua tipe —</option>
                    <option value="in" @selected(request('type') === 'in')>Masuk</option>
                    <option value="out" @selected(request('type') === 'out')>Keluar</option>
                    <option value="adjust" @selected(request('type') === 'adjust')>Opname</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                @if (request()->hasAny(['product_id', 'type']))
                    <a href="{{ route('admin.stok.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Produk</th>
                        <th>Tipe</th>
                        <th class="text-right">Qty</th>
                        <th>Keterangan</th>
                        <th>Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap text-slate-500">
                                {{ $movement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="font-medium text-slate-900">
                                {{ $movement->product->name ?? '(dihapus)' }}
                                <span class="text-xs font-normal text-slate-400">{{ $movement->product->unit ?? '' }}</span>
                            </td>
                            <td>
                                @if ($movement->type === 'in')
                                    <span class="badge badge-green">Masuk</span>
                                @elseif ($movement->type === 'out')
                                    <span class="badge badge-red">Keluar</span>
                                @else
                                    <span class="badge badge-amber">Opname</span>
                                @endif
                            </td>
                            <td class="text-right font-mono font-semibold {{ $movement->type === 'out' ? 'text-rose-600' : ($movement->type === 'in' ? 'text-emerald-600' : 'text-slate-700') }}">
                                @if ($movement->type === 'out')−@elseif ($movement->type === 'in')+@endif{{ $movement->qty }}
                            </td>
                            <td class="max-w-xs truncate text-slate-500">{{ $movement->note ?? '—' }}</td>
                            <td class="text-slate-500">{{ $movement->user->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada pergerakan stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $movements->links() }}
    </div>
@endsection
