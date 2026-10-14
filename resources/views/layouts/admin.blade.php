{{--
    Layout area admin — navbar + flash message.
--}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin — KasirKu')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gray-100 font-sans text-gray-900">
    <nav class="bg-white shadow">
        <div class="mx-auto max-w-6xl px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-indigo-600">KasirKu</a>
                <a href="{{ route('admin.kategori.index') }}"
                   class="text-sm {{ request()->routeIs('admin.kategori.*') ? 'font-semibold text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Kategori
                </a>
                <a href="{{ route('admin.supplier.index') }}"
                   class="text-sm {{ request()->routeIs('admin.supplier.*') ? 'font-semibold text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Supplier
                </a>
                <a href="{{ route('admin.produk.index') }}"
                   class="text-sm {{ request()->routeIs('admin.produk.*') ? 'font-semibold text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Produk
                </a>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
