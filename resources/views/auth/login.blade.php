@extends('layouts.app')

@section('title', 'Login — KasirKu')

@section('content')
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Selamat datang kembali</h1>
    <p class="mt-1.5 text-sm text-slate-500">Masuk ke akun Anda untuk melanjutkan.</p>

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

    <form method="POST" action="{{ route('login.attempt') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="you@contoh.com" class="input @error('email') input-error @enderror" />
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" type="password" name="password" required placeholder="••••••••"
                   class="input @error('password') input-error @enderror" />
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1"
                   class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
            Ingat saya
        </label>

        <button type="submit" class="btn btn-primary w-full py-3">Login</button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:underline">Daftar sekarang</a>
    </p>
@endsection
