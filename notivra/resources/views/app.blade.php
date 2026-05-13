<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Notivra') }}</title>
        @if(Request::is('services/*'))
            <title>Layanan - {{ config('app.name') }}</title>
            <meta name="description" content="Notivra hadir sebagai jembatan kolaborasi riset di Indonesia. Temukan mitra ahli, kelola draf penelitian, dan publikasikan wawasan akademik Anda dalam satu ekosistem terpadu.">
        @elseif(Request::is('gallery/*'))
            <title>Galeri - {{ config('app.name') }}</title>
        @endif
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-7HH51EJGZJ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-7HH51EJGZJ');
    </script>

    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
