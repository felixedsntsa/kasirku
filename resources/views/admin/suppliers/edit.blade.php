@extends('layouts.admin')

@section('title', 'Edit Supplier — KasirKu')
@section('heading', 'Edit Supplier')

@section('content')
    <div class="mx-auto max-w-md">
    <p class="page-subtitle mb-6">Perbarui data pemasok barang.</p>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.supplier.update', $supplier) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="label">Nama <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $supplier->name) }}" required autofocus
                           class="input @error('name') input-error @enderror" />
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="label">Telepon</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                           class="input @error('phone') input-error @enderror" />
                    @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address" class="label">Alamat</label>
                    <input id="address" type="text" name="address" value="{{ old('address', $supplier->address) }}"
                           class="input @error('address') input-error @enderror" />
                    @error('address') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.supplier.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
