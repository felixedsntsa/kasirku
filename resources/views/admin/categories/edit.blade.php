@extends('layouts.admin')

@section('title', 'Edit Kategori — KasirKu')
@section('heading', 'Edit Kategori')

@section('content')
    <div class="mx-auto max-w-md">
    <p class="page-subtitle mb-6">Ubah nama kategori yang dipakai di form produk dan laporan.</p>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.kategori.update', $kategori) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="label">Nama <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $kategori->name) }}" required autofocus
                           class="input @error('name') input-error @enderror" />
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.kategori.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
