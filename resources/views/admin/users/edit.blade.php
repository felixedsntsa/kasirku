@extends('layouts.admin')

@section('title', 'Edit Kasir — KasirKu')
@section('heading', 'Edit Kasir')

@section('content')
    <div class="mx-auto max-w-xl">
    <p class="page-subtitle mb-6">Perbarui data akun kasir. Kosongkan password bila tidak ingin mengganti.</p>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.kasir.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="label">Nama <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus
                           class="input @error('name') input-error @enderror" />
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">Email <span class="text-danger">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="input @error('email') input-error @enderror" />
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="border-t border-line pt-5">
                    <p class="text-sm font-semibold text-slate-900">Ganti Password</p>
                    <p class="mt-1 text-xs text-ink-500">Kosongkan bila password tetap.</p>
                </div>

                <div>
                    <label for="password" class="label">Password Baru</label>
                    <input id="password" type="password" name="password" autocomplete="new-password"
                           placeholder="min. 8 karakter" class="input @error('password') input-error @enderror" />
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">Ulangi Password Baru</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           autocomplete="new-password" class="input" />
                </div>

                <div class="flex items-center gap-3 border-t border-line pt-4">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.kasir.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
