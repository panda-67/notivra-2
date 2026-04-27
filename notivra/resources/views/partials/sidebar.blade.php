<div class="space-y-6">
    @php
        $locale = App::getLocale();
    @endphp
    <div class="min-h-34">
        <div id="search-root" v-cloak>
            <blog-search initial-value="{{ request('search') }}" locale="{{ $locale }}"></blog-search>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">
            @if ($locale === 'id')
                Kategori Populer
            @else
               Populer Categories
            @endif
        </h3>
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
                <li class="text-slate-400 italic text-xs">
                    @if ($locale === 'id')
                        Belum ada kategori
                    @else
                       No categories yet
                    @endif
                </li>
            @endforelse
        </ul>
    </div>

    <div id="newsletter-root">
        <newsletter-card></newsletter-card>
    </div>
</div>
