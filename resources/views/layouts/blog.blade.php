<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title') - Notivra Insight</title>

        @vite(['resources/css/app.css', 'resources/js/blog.js'])
    </head>

    <body class="bg-slate-50 text-slate-900 antialiased">
        <div>
            <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100">
                <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
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

                        <aside class="lg:col-span-4 space-y-6">
                            <div class="min-h-32">
                                <div id="search-root">
                                    <blog-search initial-value="{{ request('search') }}"></blog-search>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                                <h3 class="font-bold text-slate-800 mb-4">Kategori Populer</h3>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li><a href="#" class="hover:text-blue-600 transition flex justify-between"><span>Akademik</span> <span class="bg-slate-100 px-2 rounded text-xs py-0.5">12</span></a></li>
                                    <li><a href="#" class="hover:text-blue-600 transition flex justify-between"><span>Tips Menulis</span> <span class="bg-slate-100 px-2 rounded text-xs py-0.5">8</span></a></li>
                                    <li><a href="#" class="hover:text-blue-600 transition flex justify-between"><span>Teknologi AI</span> <span class="bg-slate-100 px-2 rounded text-xs py-0.5">5</span></a></li>
                                </ul>
                            </div>

                            <div class="bg-blue-600 p-6 rounded-2xl shadow-lg shadow-blue-200 text-white">
                                <h3 class="font-bold mb-2">Langganan Insight</h3>
                                <p class="text-xs text-blue-100 mb-4">Dapatkan update akademik terbaru langsung di email Anda.</p>
                                <button class="w-full bg-white text-blue-600 font-semibold py-2 rounded-xl text-sm hover:bg-blue-50 transition">Gabung Sekarang</button>
                            </div>
                        </aside>

                    </div>
                </div>
            </main>

            <footer class="py-12 border-t border-slate-200 mt-20 text-center text-sm text-slate-400">
                &copy; 2026 Notivra Academic Support.
            </footer>
        </div>
    </body>
</html>
