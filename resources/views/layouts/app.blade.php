{{--
    Layout auth (login/register) — split screen: panel info kiri + form kanan.
    Aset via @vite (Tailwind 4 + Instrument Sans).
--}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KasirKu')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-canvas font-sans text-slate-900 antialiased">
    <div class="flex min-h-full">

        {{-- Panel info (kiri) --}}
        <div class="relative hidden w-1/2 overflow-hidden bg-sidebar lg:block">
            <div class="relative flex h-full flex-col justify-between p-8 text-white">
                <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-md bg-accent-600 text-sm font-bold text-white">
                        KK
                    </div>
                    <span class="text-lg font-bold">KasirKu</span>
                </a>

                <div class="max-w-md">
                    <h2 class="text-2xl font-semibold leading-tight">
                        Kasir &amp; inventaris<br>
                        <span class="text-accent-400">satu atap.</span>
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-400">
                        Transaksi cepat, stok teraudit di setiap pergerakan, laporan akurat.
                        Dibangun manual dengan fitur bawaan Laravel — minim dependency.
                    </p>
                    <ul class="mt-8 space-y-3 text-sm text-slate-300">
                        <li class="flex items-center gap-3">
                            <span class="flex size-6 items-center justify-center rounded-md bg-accent-600/20 text-accent-400">
                                <x-icon name="check" class="size-4" />
                            </span>
                            Transaksi atomik — stok tidak akan pernah minus
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex size-6 items-center justify-center rounded-md bg-accent-600/20 text-accent-400">
                                <x-icon name="check" class="size-4" />
                            </span>
                            Setiap pergerakan stok tercatat &amp; bisa diaudit
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex size-6 items-center justify-center rounded-md bg-accent-600/20 text-accent-400">
                                <x-icon name="check" class="size-4" />
                            </span>
                            Struk siap cetak thermal 58mm / 80mm
                        </li>
                    </ul>
                </div>

                <p class="text-xs text-slate-400">Portofolio POS &amp; Inventaris berbasis Laravel.</p>
            </div>
        </div>

        {{-- Form (kanan) --}}
        <div class="flex w-full items-center justify-center px-4 py-8 lg:w-1/2">
            <main class="w-full max-w-sm">
                {{-- Logo mobile --}}
                <a href="{{ route('welcome') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                    <div class="flex size-10 items-center justify-center rounded-md bg-accent-600 text-sm font-bold text-white">
                        KK
                    </div>
                    <span class="text-lg font-bold">KasirKu</span>
                </a>

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
