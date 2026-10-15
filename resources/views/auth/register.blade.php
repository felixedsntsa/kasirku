@extends('layouts.app')

@section('title', 'Register — KasirKu')

@section('content')
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Buat akun baru</h1>
    <p class="mt-1.5 text-sm text-slate-500">Daftar gratis dan mulai transaksi hari ini.</p>

    @if ($errors->any())
        <div class="alert-error mt-6" role="alert">
            <x-icon name="alert" class="mt-0.5 size-5 shrink-0" />
            <ul class="list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.attempt') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="label">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   placeholder="mis. Budi Santoso" class="input @error('name') input-error @enderror" />
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   placeholder="you@contoh.com" class="input @error('email') input-error @enderror" />
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" type="password" name="password" required placeholder="Minimal 8 karakter"
                   class="input @error('password') input-error @enderror" />
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Ulangi password"
                   class="input @error('password_confirmation') input-error @enderror" />
            @error('password_confirmation') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full py-3">Daftar</button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Login</a>
    </p>
@endsection
