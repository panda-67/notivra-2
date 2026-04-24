<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title') - Notivra Insight</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @vite(['resources/css/app.css', 'resources/js/blog.js'])
    </head>

    <body class="bg-slate-50 text-slate-900 antialiased">
        <div>
            <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex justify-between items-center">
                    <a href="/" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-lg shadow-blue-200">N</div>
                        <span class="text-xl font-bold tracking-tight uppercase">Notivra <span class="text-blue-600">Insight</span></span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition">Dashboard →</a>
                </div>
            </nav>

            <main class="py-10">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

                        <div class="lg:col-span-8">
                            @yield('content')
                        </div>

                        <aside class="lg:col-span-4">
                            @include('partials.sidebar')
                       </aside>

                    </div>
                    @include('partials.cta-hire')
                </div>
            </main>

            @include('partials.footer')
       </div>
    </body>
</html>
