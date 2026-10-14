@extends('layouts.admin')

@section('title', 'Tambah Produk — KasirKu')

@section('content')
    <h1 class="text-xl font-semibold mb-6">Tambah Produk</h1>

    <div class="max-w-2xl rounded-xl bg-white shadow p-6">
        <form method="POST" action="{{ route('admin.produk.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                <input id="sku" type="text" name="sku" value="{{ old('sku') }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select id="category_id" name="category_id" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Pilih kategori —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier (opsional)</label>
                <select id="supplier_id" name="supplier_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Tanpa supplier —</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">Barcode (opsional)</label>
                <input id="barcode" type="text" name="barcode" value="{{ old('barcode') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                <input id="unit" type="text" name="unit" value="{{ old('unit', 'pcs') }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Beli (Rp)</label>
                <input id="cost_price" type="number" name="cost_price" value="{{ old('cost_price', 0) }}" min="0" step="0.01" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="sell_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (Rp)</label>
                <input id="sell_price" type="number" name="sell_price" value="{{ old('sell_price', 0) }}" min="0" step="0.01" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                <input id="stock" type="number" name="stock" value="{{ old('stock', 0) }}" min="0" step="1" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-1">Stok Minimum</label>
                <input id="min_stock" type="number" name="min_stock" value="{{ old('min_stock', 0) }}" min="0" step="1" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300">
                    Aktif (bisa dijual)
                </label>
            </div>

            <div class="sm:col-span-2 flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-indigo-600 hover:bg-indigo-700 px-4 py-2 text-sm font-medium text-white">
                    Simpan
                </button>
                <a href="{{ route('admin.produk.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
@endsection
