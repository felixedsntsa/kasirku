@extends('layouts.admin')

@section('title', 'Edit Produk — KasirKu')
@section('heading', 'Edit Produk')

@section('content')
    <div class="mx-auto max-w-3xl">
    <p class="page-subtitle mb-6">Perbarui data produk. Stok hanya bisa diubah lewat menu Stok.</p>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.produk.update', $produk) }}" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="label">Nama <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $produk->name) }}" required autofocus
                           class="input @error('name') input-error @enderror" />
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sku" class="label">SKU <span class="text-danger">*</span></label>
                    <input id="sku" type="text" name="sku" value="{{ old('sku', $produk->sku) }}" required
                           class="input font-mono @error('sku') input-error @enderror" />
                    @error('sku') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="category_id" class="label">Kategori <span class="text-danger">*</span></label>
                    <select id="category_id" name="category_id" required
                            class="input @error('category_id') input-error @enderror">
                        <option value="">— Pilih kategori —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $produk->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="supplier_id" class="label">Supplier <span class="font-normal text-ink-500">(opsional)</span></label>
                    <select id="supplier_id" name="supplier_id"
                            class="input @error('supplier_id') input-error @enderror">
                        <option value="">— Tanpa supplier —</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $produk->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="barcode" class="label">Barcode <span class="font-normal text-ink-500">(opsional)</span></label>
                    <input id="barcode" type="text" name="barcode" value="{{ old('barcode', $produk->barcode) }}"
                           class="input font-mono @error('barcode') input-error @enderror" />
                    @error('barcode') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="unit" class="label">Satuan <span class="text-danger">*</span></label>
                    <input id="unit" type="text" name="unit" value="{{ old('unit', $produk->unit) }}" required
                           class="input @error('unit') input-error @enderror" />
                    @error('unit') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="cost_price" class="label">Harga Beli (Rp) <span class="text-danger">*</span></label>
                    <input id="cost_price" type="number" name="cost_price" value="{{ old('cost_price', $produk->cost_price) }}" min="0" step="1" required
                           class="input @error('cost_price') input-error @enderror" />
                    @error('cost_price') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sell_price" class="label">Harga Jual (Rp) <span class="text-danger">*</span></label>
                    <input id="sell_price" type="number" name="sell_price" value="{{ old('sell_price', $produk->sell_price) }}" min="0" step="1" required
                           class="input @error('sell_price') input-error @enderror" />
                    @error('sell_price') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Stok tidak diedit manual — kelola lewat Stok Masuk / Opname. Info saja. --}}
                <div>
                    <span class="label">Stok Saat Ini</span>
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        <span class="text-sm font-semibold text-slate-700">{{ $produk->stock }} {{ $produk->unit }}</span>
                        <a href="{{ route('admin.stok.index', ['product_id' => $produk->id]) }}"
                           class="text-xs font-medium text-accent-600 hover:underline">Riwayat stok</a>
                    </div>
                </div>

                <div>
                    <label for="min_stock" class="label">Stok Minimum <span class="text-danger">*</span></label>
                    <input id="min_stock" type="number" name="min_stock" value="{{ old('min_stock', $produk->min_stock) }}" min="0" step="1" required
                           class="input @error('min_stock') input-error @enderror" />
                    @error('min_stock') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50/60 px-4 py-3">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $produk->is_active))
                               class="size-4 rounded border-slate-300 text-accent-600 focus:ring-accent-600" />
                        <span>
                            <span class="block text-sm font-medium text-slate-900">Aktif (bisa dijual)</span>
                            <span class="block text-xs text-slate-500">Produk nonaktif disembunyikan dari halaman kasir.</span>
                        </span>
                    </label>
                </div>

                <div class="sm:col-span-2 flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.produk.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
