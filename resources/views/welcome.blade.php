{{--
    Landing page KasirKu — hero gradient + kartu fitur.
--}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KasirKu — POS &amp; Inventaris</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-surface font-sans text-slate-900 antialiased">

    {{-- Navbar --}}
    <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <div class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white shadow-lg shadow-brand-900/30">
                    KK
                </div>
                <span class="text-base font-bold tracking-tight">KasirKu</span>
            </a>
            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('kasir.home') }}" class="btn btn-primary">
                        Buka Aplikasi
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(99,102,241,0.12),transparent_60%)]"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-24 text-center sm:px-6 sm:py-32">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-1.5 text-xs font-semibold text-brand-700">
                <span class="size-1.5 rounded-full bg-brand-500"></span>
                POS &amp; Inventaris — hand-roll Laravel
            </span>

            <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-extrabold tracking-tight sm:text-6xl">
                Kasir cepat,<br>
                <span class="bg-gradient-to-r from-brand-600 to-indigo-400 bg-clip-text text-transparent">stok tercatat rapi.</span>
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-500">
                Transaksi kasir yang cepat, setiap pergerakan stok teraudit, laporan akurat —
                dibangun manual dengan fitur bawaan Laravel tanpa dependency berat.
            </p>

            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                @guest
                    <a href="{{ route('register') }}" class="btn btn-primary px-8 py-3.5 text-base">
                        Mulai Sekarang
                        <x-icon name="check" class="size-4" />
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary px-8 py-3.5 text-base">Login</a>
                @else
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('kasir.home') }}"
                       class="btn btn-primary px-8 py-3.5 text-base">
                        Buka Aplikasi
                        <x-icon name="check" class="size-4" />
                    </a>
                @endauth
            </div>

            <p class="mt-6 text-xs text-slate-400">
                Akun demo: <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono">admin@demo.com</code> /
                <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono">kasir@demo.com</code> — password: <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono">password</code>
            </p>
        </div>
    </section>

    {{-- Fitur --}}
    <section class="mx-auto max-w-6xl px-4 pb-24 sm:px-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="card p-7 transition-shadow hover:shadow-md">
                <div class="flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <x-icon name="cart" class="size-6" />
                </div>
                <h2 class="mt-5 text-base font-bold text-slate-900">Transaksi Cepat</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Cari produk instan, keranjang realtime, uang pas sekali klik, struk siap cetak thermal.
                </p>
            </div>

            <div class="card p-7 transition-shadow hover:shadow-md">
                <div class="flex size-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-icon name="archive" class="size-6" />
                </div>
                <h2 class="mt-5 text-base font-bold text-slate-900">Stok Ter-audit</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Setiap pergerakan stok tercatat (masuk, keluar, opname) dengan jejak audit — stok tidak pernah minus.
                </p>
            </div>

            <div class="card p-7 transition-shadow hover:shadow-md">
                <div class="flex size-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-icon name="chart" class="size-6" />
                </div>
                <h2 class="mt-5 text-base font-bold text-slate-900">Laporan Akurat</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Omzet harian, grafik 30 hari, produk terlaris, dan peringatan stok menipis dalam satu dashboard.
                </p>
            </div>
        </div>

        {{-- Keputusan teknis --}}
        <div class="card mt-10 overflow-hidden">
            <div class="grid grid-cols-1 divide-y divide-slate-100 md:grid-cols-3 md:divide-x md:divide-y-0">
                <div class="p-7">
                    <p class="text-xs font-bold uppercase tracking-widest text-brand-600">01</p>
                    <h3 class="mt-2 text-sm font-bold text-slate-900">Auth hand-roll</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Login/register ditulis manual dengan <code class="font-mono text-xs">Auth::attempt()</code> + Hash — tanpa Breeze/Fortify.
                    </p>
                </div>
                <div class="p-7">
                    <p class="text-xs font-bold uppercase tracking-widest text-brand-600">02</p>
                    <h3 class="mt-2 text-sm font-bold text-slate-900">Stok auditable</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Semua perubahan stok lewat satu tabel <code class="font-mono text-xs">stock_movements</code> — kolom <code class="font-mono text-xs">products.stock</code> hanya cache.
                    </p>
                </div>
                <div class="p-7">
                    <p class="text-xs font-bold uppercase tracking-widest text-brand-600">03</p>
                    <h3 class="mt-2 text-sm font-bold text-slate-900">Transaksi atomik</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Checkout dibungkus DB transaction + <code class="font-mono text-xs">lockForUpdate()</code> — dua kasir bersamaan tetap aman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-slate-200 bg-white py-10">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 sm:px-6 md:flex-row">
            <p class="text-sm text-slate-400">© {{ now()->year }} KasirKu — portofolio POS &amp; Inventaris berbasis Laravel.</p>
            <p class="text-xs text-slate-400">Minim package · Auth &amp; otorisasi hand-roll · Stok auditable</p>
        </div>
    </footer>
</body>
</html>
