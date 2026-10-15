@extends('layouts.admin')

@section('title', 'Tambah Produk — KasirKu')
@section('heading', 'Tambah Produk')

@section('content')
    <div class="mx-auto max-w-3xl">
    <p class="page-subtitle mb-6">Produk baru — stok awal otomatis tercatat sebagai stok masuk pertama.</p>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.produk.store') }}" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                @csrf

                <div>
                    <label for="name" class="label">Nama <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="mis. Kopi Susu Instan" class="input @error('name') input-error @enderror" />
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sku" class="label">SKU <span class="text-danger">*</span></label>
                    <input id="sku" type="text" name="sku" value="{{ old('sku') }}" required
                           placeholder="mis. KOP-001" class="input font-mono @error('sku') input-error @enderror" />
                    @error('sku') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="category_id" class="label">Kategori <span class="text-danger">*</span></label>
                    <select id="category_id" name="category_id" required
                            class="input @error('category_id') input-error @enderror">
                        <option value="">— Pilih kategori —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
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
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="barcode" class="label">Barcode <span class="font-normal text-ink-500">(opsional)</span></label>
                    <input id="barcode" type="text" name="barcode" value="{{ old('barcode') }}"
                           class="input font-mono @error('barcode') input-error @enderror" />
                    @error('barcode') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="unit" class="label">Satuan <span class="text-danger">*</span></label>
                    <input id="unit" type="text" name="unit" value="{{ old('unit', 'pcs') }}" required
                           placeholder="pcs / kg / box" class="input @error('unit') input-error @enderror" />
                    @error('unit') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="cost_price" class="label">Harga Beli (Rp) <span class="text-danger">*</span></label>
                    <input id="cost_price" type="number" name="cost_price" value="{{ old('cost_price', 0) }}" min="0" step="1" required
                           class="input @error('cost_price') input-error @enderror" />
                    @error('cost_price') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sell_price" class="label">Harga Jual (Rp) <span class="text-danger">*</span></label>
                    <input id="sell_price" type="number" name="sell_price" value="{{ old('sell_price', 0) }}" min="0" step="1" required
                           class="input @error('sell_price') input-error @enderror" />
                    @error('sell_price') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="stock" class="label">Stok Awal <span class="text-danger">*</span></label>
                    <input id="stock" type="number" name="stock" value="{{ old('stock', 0) }}" min="0" step="1" required
                           class="input @error('stock') input-error @enderror" />
                    <p class="mt-1.5 text-xs text-ink-500">Tercatat otomatis sebagai stok masuk pertama.</p>
                    @error('stock') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="min_stock" class="label">Stok Minimum <span class="text-danger">*</span></label>
                    <input id="min_stock" type="number" name="min_stock" value="{{ old('min_stock', 0) }}" min="0" step="1" required
                           class="input @error('min_stock') input-error @enderror" />
                    <p class="mt-1.5 text-xs text-ink-500">Peringatan muncul saat stok di bawah angka ini.</p>
                    @error('min_stock') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50/60 px-4 py-3">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                               class="size-4 rounded border-slate-300 text-accent-600 focus:ring-accent-600" />
                        <span>
                            <span class="block text-sm font-medium text-slate-900">Aktif (bisa dijual)</span>
                            <span class="block text-xs text-slate-500">Produk nonaktif disembunyikan dari halaman kasir.</span>
                        </span>
                    </label>
                </div>

                <div class="sm:col-span-2 flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                    <a href="{{ route('admin.produk.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
