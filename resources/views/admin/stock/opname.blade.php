@extends('layouts.admin')

@section('title', 'Stok Opname — KasirKu')
@section('heading', 'Stok Opname')

@section('content')
    <p class="page-subtitle mb-6">
        Sesuaikan stok sistem dengan hasil hitung fisik. Stok akan <strong>diset</strong> ke angka yang Anda masukkan.
    </p>

    <div class="card max-w-xl">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.stok.opname.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="product_id" class="label">Produk</label>
                    <select id="product_id" name="product_id" required
                            class="input @error('product_id') input-error @enderror"
                            onchange="document.getElementById('old_stock').value = this.selectedOptions[0]?.dataset.stock ?? ''">
                        <option value="">— Pilih produk —</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-stock="{{ $product->stock }}"
                                    @selected(old('product_id') == $product->id)>
                                {{ $product->name }} (sistem: {{ $product->stock }} {{ $product->unit }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="qty" class="label">Stok Fisik Hasil Hitung</label>
                    <input id="qty" type="number" name="qty" value="{{ old('qty') }}" min="0" step="1" required
                           placeholder="mis. 45" class="input @error('qty') input-error @enderror" />
                    @error('qty') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Hanya untuk pesan sukses (sebelum & sesudah opname). --}}
                <input type="hidden" name="old_stock" id="old_stock" value="{{ old('old_stock') }}">

                <div>
                    <label for="note" class="label">Keterangan <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input id="note" type="text" name="note" value="{{ old('note', 'Stok opname fisik') }}" maxlength="255"
                           class="input @error('note') input-error @enderror" />
                    @error('note') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="adjust" class="size-4" />
                        Simpan Opname
                    </button>
                    <a href="{{ route('admin.stok.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
