<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Editor - Configuration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100 h-screen overflow-hidden">

    <header class="bg-black">
        <nav class="max-w-5xl mx-auto py-4">
            <ul class=" flex items-center gap-2">
                <li><a class="text-white black:text-black text-xl" href="{{ route('documents.index') }}">Pdf Config manager</a></li>
                <li><a class="text-white black:text-black underline pl-8" href="{{ route('factures.index') }}">Factures</a></li>
            </ul>
        </nav>
    </header>
    <main class="overflow-y-scroll">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
