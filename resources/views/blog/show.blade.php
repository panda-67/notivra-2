@extends('layouts.blog')

@section('title', $post['meta']['title'])

@section('content')
<article>
    <header class="mb-10">
        <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 mb-6">
            ← Kembali ke Blog
        </a>

        <div class="sm:flex items-center gap-x-3 text-sm text-slate-500 mb-4">
            <div class="hidden sm:block">
                @foreach($post['meta']['tags'] as $tag)
                    <span class="capitalize bg-blue-50 text-blue-600 px-3 py-1 rounded-full font-semibold">
                        {{ $tag }}
                    </span>
                @endforeach
            </div>
            <div class="sm:hidden flex flex-wrap items-center">
                <div class="flex flex-wrap gap-2">
                    @foreach(array_slice($post['meta']['tags'], 0, 3) as $tag)
                        <span class="capitalize bg-blue-50 text-blue-600 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>

                @if(count($post['meta']['tags']) > 3)
                    <span class="text-slate-400 text-xs font-medium">
                        +{{ count($post['meta']['tags']) - 3 }}
                    </span>
                @endif
            </div>

            <span class="hidden sm:block">•</span>
            <span class="ml-2.5 sm:ml-0">{{ $post['meta']['date'] ?? '' }}</span>
        </div>

        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight">
            {{ $post['meta']['title'] }}
        </h1>

            <span class="text-xs font-bold text-blue-500 uppercase">
                {{ $post['meta']['source'] }}
            </span>

        @if(isset($post['meta']['author']))
            <div class="mt-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-200"></div>
                <div class="text-sm">
                    <p class="font-bold text-slate-800">{{ $post['meta']['author'] }}</p>
                    <p class="text-slate-500">Kontributor Notivra</p>
                </div>
            </div>
        @endif
    </header>

    <div class="prose prose-slate prose-lg max-w-none prose-headings:text-slate-900 prose-headings:font-bold
                prose-a:text-blue-600 hover:prose-a:text-blue-500 prose-img:rounded-2xl prose-img:shadow-lg">
        @php
            $content = $post['html'];
            $paragraphs = explode('</p>', $content);
            $insertAfter = 6;
        @endphp
        @foreach($paragraphs as $index => $paragraph)
            {!! $paragraph . '</p>' !!}

            @if($index + 1 == $insertAfter && count($paragraphs) > $insertAfter)
                @include('partials.cta-hire')
            @endif
        @endforeach
    </div>

    <hr class="my-12 border-slate-100">

    <section>
        <h3 class="text-2xl font-bold text-slate-800 mb-6">Artikel Terkait</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($related as $item)
                <article class="group bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
                    <div class="flex flex-col h-full">
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-2">
                            {{ $item['meta']['source'] }}
                        </span>

                        <h4 class="font-bold text-slate-800 group-hover:text-blue-600 transition mb-2">
                            <a href="{{ route('blog.show', $item['slug']) }}">
                                {{ $item['meta']['title'] }}
                            </a>
                        </h4>

                        <p class="text-xs text-slate-500 line-clamp-2 mb-4">
                            {{ $item['meta']['description'] }}
                        </p>

                        <div class="mt-auto pt-4 border-t border-slate-50 flex justify-between items-center text-[10px] text-slate-400">
                            <span>{{ $item['meta']['date'] }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-slate-400 italic text-sm">Tidak ada artikel terkait saat ini.</p>
            @endforelse
        </div>
    </section>

    <div id="share-root">
        <share-footer></share-footer>
    </div>

</article>
@endsection
