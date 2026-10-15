{{--
    Landing page KasirKu — hero padat + kartu fitur (netral + satu aksen, docs/DESIGN.md §3.1).
--}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KasirKu — POS &amp; Inventaris</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-canvas font-sans text-slate-900 antialiased">

    {{-- Navbar --}}
    <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <div class="flex size-9 items-center justify-center rounded-md bg-accent-600 text-xs font-bold text-white">
                    KK
                </div>
                <span class="text-base font-bold tracking-tight">KasirKu</span>
            </a>
            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('kasir.home') }}" class="btn btn-secondary">
                        Buka Aplikasi
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative">
        <div class="relative mx-auto max-w-6xl px-4 py-8 text-center sm:px-6">
            <span class="inline-flex items-center gap-2 rounded-full border border-accent-600/20 bg-accent-50 px-4 py-1.5 text-xs font-semibold text-accent-700">
                <span class="size-1.5 rounded-full bg-accent-600"></span>
                POS &amp; Inventaris — hand-roll Laravel
            </span>

            <h1 class="mx-auto mt-6 max-w-3xl text-2xl font-semibold tracking-tight">
                Kasir cepat,<br>
                <span class="text-accent-600">stok tercatat rapi.</span>
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-slate-500">
                Transaksi kasir yang cepat, setiap pergerakan stok teraudit, laporan akurat —
                dibangun manual dengan fitur bawaan Laravel tanpa dependency berat.
            </p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                @guest
                    <a href="{{ route('register') }}" class="btn btn-primary px-6 py-3 text-base">
                        Mulai Sekarang
                        <x-icon name="check" class="size-4" />
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary px-6 py-3 text-base">Login</a>
                @else
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('kasir.home') }}"
                       class="btn btn-primary px-6 py-3 text-base">
                        Buka Aplikasi
                        <x-icon name="check" class="size-4" />
                    </a>
                @endauth
            </div>

            <p class="mt-6 text-xs text-ink-500">
                Akun demo: <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono">admin@gmail.com</code> (password: <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono">admin123</code>) /
                <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono">kasirku@gmail.com</code> (password: <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono">kasirku123</code>)
            </p>
        </div>
    </section>

    {{-- Fitur --}}
    <section class="mx-auto max-w-6xl px-4 pb-8 sm:px-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="card p-6">
                <div class="flex size-12 items-center justify-center rounded-md bg-accent-50 text-accent-600">
                    <x-icon name="cart" class="size-6" />
                </div>
                <h2 class="mt-5 text-base font-semibold text-slate-900">Transaksi Cepat</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Cari produk instan, keranjang realtime, uang pas sekali klik, struk siap cetak thermal.
                </p>
            </div>

            <div class="card p-6">
                <div class="flex size-12 items-center justify-center rounded-md bg-accent-50 text-accent-600">
                    <x-icon name="archive" class="size-6" />
                </div>
                <h2 class="mt-5 text-base font-semibold text-slate-900">Stok Ter-audit</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Setiap pergerakan stok tercatat (masuk, keluar, opname) dengan jejak audit — stok tidak pernah minus.
                </p>
            </div>

            <div class="card p-6">
                <div class="flex size-12 items-center justify-center rounded-md bg-accent-50 text-accent-600">
                    <x-icon name="chart" class="size-6" />
                </div>
                <h2 class="mt-5 text-base font-semibold text-slate-900">Laporan Akurat</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Omzet harian, grafik 30 hari, produk terlaris, dan peringatan stok menipis dalam satu dashboard.
                </p>
            </div>
        </div>

        {{-- Keputusan teknis --}}
        <div class="card mt-8 overflow-hidden">
            <div class="grid grid-cols-1 divide-y divide-slate-100 md:grid-cols-3 md:divide-x md:divide-y-0">
                <div class="p-6">
                    <p class="text-xs font-semibold uppercase tracking-widest text-accent-600">01</p>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Auth hand-roll</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Login/register ditulis manual dengan <code class="font-mono text-xs">Auth::attempt()</code> + Hash — tanpa Breeze/Fortify.
                    </p>
                </div>
                <div class="p-6">
                    <p class="text-xs font-semibold uppercase tracking-widest text-accent-600">02</p>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Stok auditable</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Semua perubahan stok lewat satu tabel <code class="font-mono text-xs">stock_movements</code> — kolom <code class="font-mono text-xs">products.stock</code> hanya cache.
                    </p>
                </div>
                <div class="p-6">
                    <p class="text-xs font-semibold uppercase tracking-widest text-accent-600">03</p>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Transaksi atomik</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Checkout dibungkus DB transaction + <code class="font-mono text-xs">lockForUpdate()</code> — dua kasir bersamaan tetap aman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-slate-200 bg-white py-8">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 sm:px-6 md:flex-row">
            <p class="text-sm text-ink-500">© {{ now()->year }} KasirKu — portofolio POS &amp; Inventaris berbasis Laravel.</p>
            <p class="text-xs text-ink-500">Minim package · Auth &amp; otorisasi hand-roll · Stok auditable</p>
        </div>
    </footer>
</body>
</html>
