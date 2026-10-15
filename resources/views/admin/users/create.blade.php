@extends('layouts.admin')

@section('title', 'Tambah Kasir — KasirKu')
@section('heading', 'Tambah Kasir')

@section('content')
    <div class="mx-auto max-w-xl">
    <p class="page-subtitle mb-6">Buat akun agar kasir baru bisa login ke halaman POS.</p>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.kasir.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="label">Nama <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="mis. Budi Santoso" class="input @error('name') input-error @enderror" />
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">Email <span class="text-danger">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                           placeholder="mis. budi@gmail.com" class="input @error('email') input-error @enderror" />
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="label">Password <span class="text-danger">*</span></label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           placeholder="min. 8 karakter" class="input @error('password') input-error @enderror" />
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">Ulangi Password <span class="text-danger">*</span></label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           autocomplete="new-password" class="input" />
                </div>

                <div class="flex items-center gap-3 border-t border-line pt-4">
                    <button type="submit" class="btn btn-primary">Simpan Kasir</button>
                    <a href="{{ route('admin.kasir.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
