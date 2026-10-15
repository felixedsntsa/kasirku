@extends('layouts.admin')

@section('title', 'Stok Masuk — KasirKu')
@section('heading', 'Stok Masuk')

@section('content')
    <p class="page-subtitle mb-6">Catat restock/pembelian barang. Perubahan stok tercatat otomatis di riwayat.</p>

    <div class="card max-w-xl">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.stok.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="product_id" class="label">Produk</label>
                    <select id="product_id" name="product_id" required class="input @error('product_id') input-error @enderror">
                        <option value="">— Pilih produk —</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->name }} (stok: {{ $product->stock }} {{ $product->unit }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="qty" class="label">Jumlah Masuk</label>
                    <input id="qty" type="number" name="qty" value="{{ old('qty') }}" min="1" step="1" required
                           placeholder="mis. 50" class="input @error('qty') input-error @enderror" />
                    @error('qty') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="note" class="label">Keterangan <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input id="note" type="text" name="note" value="{{ old('note') }}" maxlength="255"
                           placeholder="mis. Restock dari supplier ABC" class="input @error('note') input-error @enderror" />
                    @error('note') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="arrow-down" class="size-4" />
                        Simpan Stok Masuk
                    </button>
                    <a href="{{ route('admin.stok.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
