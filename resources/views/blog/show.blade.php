@extends('layouts.blog')

@section('title', $meta['title'])

@section('content')
<article>
    <header class="mb-10">
        <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 mb-6">
            ← Kembali ke Blog
        </a>

        <div class="flex items-center gap-3 text-sm text-slate-500 mb-4">
            @foreach($meta['tags'] as $tag)
                <span class="capitalize bg-blue-50 text-blue-600 px-3 py-1 rounded-full font-semibold">
                    {{ $tag }}
                </span>
            @endforeach
            <span>•</span>
            <span>{{ $meta['date'] ?? '' }}</span>
        </div>

        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight">
            {{ $meta['title'] }}
        </h1>

        @if(isset($meta['author']))
            <div class="mt-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-200"></div>
                <div class="text-sm">
                    <p class="font-bold text-slate-800">{{ $meta['author'] }}</p>
                    <p class="text-slate-500">Kontributor Notivra</p>
                </div>
            </div>
        @endif
    </header>

    <div class="prose prose-slate prose-lg max-w-none
                prose-headings:text-slate-900 prose-headings:font-bold
                prose-a:text-blue-600 hover:prose-a:text-blue-500
                prose-img:rounded-2xl prose-img:shadow-lg">
        {!! $html !!}
    </div>

    <footer class="mt-16 pt-8 border-t border-slate-100">
        <div class="bg-slate-50 p-8 rounded-2xl text-center">
            <h3 class="font-bold text-lg text-slate-800">Suka dengan tulisan ini?</h3>
            <p class="text-slate-600 mb-6">Bagikan ke teman sejawat atau berlangganan newsletter kami.</p>
            <div class="flex justify-center gap-4">
                <button class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold shadow-md shadow-blue-200 hover:bg-blue-700 transition">Bagikan Artikel</button>
            </div>
        </div>
    </footer>
</article>
@endsection
