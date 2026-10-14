{{--
    Layout dasar KasirKu.
    Aset via @vite (Tailwind 4 + Instrument Sans). Mode dev: npm run dev; produksi: npm run build.
--}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KasirKu')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center bg-gray-100 font-sans">
    <main class="w-full max-w-md px-4">
        @yield('content')
    </main>
</body>
</html>
