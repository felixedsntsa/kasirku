@extends('layouts.admin')

@section('title', 'Edit Kategori — KasirKu')
@section('heading', 'Edit Kategori')

@section('content')
    <div class="card max-w-md">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.kategori.update', $kategori) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="label">Nama</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $kategori->name) }}" required autofocus
                           class="input @error('name') input-error @enderror" />
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.kategori.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
