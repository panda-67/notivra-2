<div class="space-y-6">
    <div class="min-h-34">
        <div id="search-root" v-cloak>
            <blog-search initial-value="{{ request('search') }}"></blog-search>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Kategori Populer</h3>
        <ul class="space-y-2 text-sm text-slate-600">
            <li>
                <a href="{{ route('blog.index', ['search' => '']) }}"
                   class="hover:text-blue-600 transition flex justify-between group">
                   <span class="capitalize group-hover:translate-x-1 transition-transform">
                       {{ 'All' }}
                   </span>
                </a>
            </li>

            @forelse($categories as $name => $count)
                <li>
                    <a href="{{ route('blog.index', ['search' => $name]) }}"
                       class="hover:text-blue-600 transition flex justify-between group">
                        <span class="capitalize group-hover:translate-x-1 transition-transform">
                            {{ $name }}
                        </span>
                        <span class="bg-slate-100 px-2 rounded text-xs py-0.5">
                            {{ $count }}
                        </span>
                    </a>
                </li>
            @empty
                <li class="text-slate-400 italic text-xs">Belum ada kategori</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-blue-600 p-6 rounded-2xl shadow-lg shadow-blue-200 text-white">
        <h3 class="font-bold mb-2">Langganan Insight</h3>
        <p class="text-xs text-blue-100 mb-4">Dapatkan update akademik terbaru langsung di email Anda.</p>
        <button class="w-full bg-white text-blue-600 font-semibold py-2 rounded-xl text-sm hover:bg-blue-50 transition">
            Gabung Sekarang
        </button>
    </div>
</div>
