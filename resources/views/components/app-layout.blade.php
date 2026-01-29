<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Editor - Configuration</title>

    <!-- 1. Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Dans la balise <head> -->
    <!-- 1. Charger la bibliothèque principale -->
    <script src="https://unpkg.com"></script>

    <!-- 2. Configurer le Worker immédiatement après -->
    <script>
        window.addEventListener('load', function() {
            if (window.pdfjsLib) {
                // C'EST CE LIEN DONT TU AS BESOIN :
                window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://unpkg.com';
                console.log('PDF.js Worker chargé depuis le CDN');
            }
        });
    </script>

    <!-- 3. Charger le reste -->
    <script src="https://cdn.jsdelivr.net"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/5.4.149/pdf.min.mjs"></script>
    <style>
        [x-cloak] { display: none !important; }
        .draggable { touch-action: none; user-select: none; }
    </style>
</head>
<body class="antialiased bg-gray-100 h-screen overflow-hidden">

    {{ $slot }}

    @stack('scripts')
</body>
</html>
