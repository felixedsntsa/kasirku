@extends('layouts.admin')

@section('title', 'Profil Saya — KasirKu')
@section('heading', 'Profil Saya')

@section('content')
    <div class="mx-auto max-w-xl">
    <p class="page-subtitle mb-6">Perbarui nama, email, atau password akun Anda.</p>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="label">Nama <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                           autofocus class="input @error('name') input-error @enderror" />
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">Email <span class="text-danger">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                           class="input @error('email') input-error @enderror" />
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-ink-500">Role:</span>
                    <span class="badge badge-accent capitalize">{{ auth()->user()->role }}</span>
                </div>

                <div class="border-t border-line pt-5">
                    <p class="text-sm font-semibold text-slate-900">Ganti Password</p>
                    <p class="mt-1 text-xs text-ink-500">
                        Kosongkan ketiga kolom bila tidak ingin mengganti password. Bila mengganti, isi password saat ini.
                    </p>
                </div>

                <div>
                    <label for="current_password" class="label">Password Saat Ini</label>
                    <input id="current_password" type="password" name="current_password"
                           autocomplete="current-password" class="input @error('current_password') input-error @enderror" />
                    @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
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
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('kasir.home') }}"
                       class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
