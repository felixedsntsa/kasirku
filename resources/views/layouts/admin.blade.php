{{--
    Layout shell area aplikasi (admin & kasir) — sidebar gelap + area konten terang.
    Responsif: sidebar jadi overlay di layar kecil (toggle Alpine).
    Aset via @vite (Tailwind 4 + Instrument Sans). Dev: npm run dev; produksi: npm run build.
--}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KasirKu')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('head')
</head>
<body class="h-full bg-canvas font-sans text-slate-900 antialiased">
    <div x-data="{ open: false }" class="min-h-full">

        {{-- Overlay mobile --}}
        <div x-show="open" x-transition.opacity @click="open = false"
             class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-sidebar text-white transition-transform duration-200 lg:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full'">

            {{-- Brand --}}
            <div class="flex h-16 items-center gap-3 border-b border-white/5 px-5">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('kasir.home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('favicon.svg') }}" alt="KasirKu" width="36" height="36" class="size-9" />
                    <div>
                        <p class="text-sm font-bold leading-tight">KasirKu</p>
                        <p class="text-[11px] leading-tight text-slate-400">POS &amp; Inventaris</p>
                    </div>
                </a>
                <button @click="open = false" class="ml-auto rounded-md p-1 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden">
                    <x-icon name="x" class="size-5" />
                </button>
            </div>

            {{-- Navigasi --}}
            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
                @php
                    $user = auth()->user();
                    $isAdmin = $user?->isAdmin() ?? false;

                    $menus = [];

                    if ($isAdmin) {
                        $menus[] = ['label' => 'Utama', 'items' => [
                            ['icon' => 'home', 'label' => 'Dashboard', 'route' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
                            ['icon' => 'cart', 'label' => 'Kasir (POS)', 'route' => route('kasir.home'), 'active' => request()->routeIs('kasir.*')],
                        ]];

                        $menus[] = ['label' => 'Master Data', 'items' => [
                            ['icon' => 'box', 'label' => 'Produk', 'route' => route('admin.produk.index'), 'active' => request()->routeIs('admin.produk.*')],
                            ['icon' => 'tag', 'label' => 'Kategori', 'route' => route('admin.kategori.index'), 'active' => request()->routeIs('admin.kategori.*')],
                            ['icon' => 'truck', 'label' => 'Supplier', 'route' => route('admin.supplier.index'), 'active' => request()->routeIs('admin.supplier.*')],
                        ]];

                        $menus[] = ['label' => 'Pengguna', 'items' => [
                            ['icon' => 'users', 'label' => 'Kelola Kasir', 'route' => route('admin.kasir.index'), 'active' => request()->routeIs('admin.kasir.*')],
                        ]];

                        $menus[] = ['label' => 'Persediaan', 'items' => [
                            ['icon' => 'archive', 'label' => 'Riwayat Stok', 'route' => route('admin.stok.index'), 'active' => request()->routeIs('admin.stok.index')],
                            ['icon' => 'arrow-down', 'label' => 'Stok Masuk', 'route' => route('admin.stok.create'), 'active' => request()->routeIs('admin.stok.create')],
                            ['icon' => 'adjust', 'label' => 'Stok Opname', 'route' => route('admin.stok.opname'), 'active' => request()->routeIs('admin.stok.opname')],
                        ]];

                        $menus[] = ['label' => 'Analitik', 'items' => [
                            ['icon' => 'chart', 'label' => 'Laporan', 'route' => route('admin.laporan.index'), 'active' => request()->routeIs('admin.laporan.*')],
                        ]];
                    } else {
                        $menus[] = ['label' => 'Transaksi', 'items' => [
                            ['icon' => 'cart', 'label' => 'Kasir (POS)', 'route' => route('kasir.home'), 'active' => request()->routeIs('kasir.home')],
                            ['icon' => 'receipt', 'label' => 'Riwayat Transaksi', 'route' => route('kasir.riwayat'), 'active' => request()->routeIs('kasir.riwayat')],
                        ]];
                    }

                    // Berlaku untuk semua role.
                    $menus[] = ['label' => 'Akun', 'items' => [
                        ['icon' => 'user', 'label' => 'Profil Saya', 'route' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
                    ]];
                @endphp

                @foreach ($menus as $menu)
                    <div>
                        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                            {{ $menu['label'] }}
                        </p>
                        <div class="space-y-1">
                            @foreach ($menu['items'] as $item)
                                <a href="{{ $item['route'] }}"
                                   @class(['nav-link', 'nav-link-active' => $item['active']])>
                                    <x-icon :name="$item['icon']" class="size-5 shrink-0 opacity-80" />
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            {{-- User + logout --}}
            <div class="border-t border-white/5 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-accent-600 text-xs font-bold uppercase">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[11px] capitalize text-slate-400">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout"
                                class="rounded-md p-2 text-slate-400 transition-colors hover:bg-white/10 hover:text-white">
                            <x-icon name="logout" class="size-5" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Area konten --}}
        <div class="lg:pl-64">
            {{-- Topbar --}}
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
                <button @click="open = true" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                    <x-icon name="menu" class="size-5" />
                </button>
                <h1 class="page-title text-lg">@yield('heading', 'KasirKu')</h1>
                <div class="ml-auto flex items-center gap-3">
                    <span class="hidden text-xs font-medium text-ink-500 sm:block">{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="alert-success mb-6" role="status">
                        <x-icon name="check" class="mt-0.5 size-5 shrink-0" />
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert-error mb-6" role="alert">
                        <x-icon name="alert" class="mt-0.5 size-5 shrink-0" />
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert-error mb-6" role="alert">
                        <x-icon name="alert" class="mt-0.5 size-5 shrink-0" />
                        <div>
                            <p class="font-semibold">Periksa kembali input Anda:</p>
                            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
