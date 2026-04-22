@extends('layouts.blog')

@section('title', 'Wawasan Akademik')

@section('content')
<div class="px-4 sm:px-0">
    <header class="mb-4 md:mb-6">
        <h1 class="text-2xl md:text-4xl font-black text-slate-900 mb-3 md:mb-4">Wawasan Riset Nusantara</h1>
        <p class="text-base md:text-lg text-slate-600">Panduan dan update terbaru untuk mendukung perjalanan akademik Anda.</p>
    </header>

    @if(request('search'))
        <div class="mb-4 text-sm text-slate-500 bg-slate-100 p-3 rounded-lg border border-slate-200">
            Menampilkan hasil pencarian untuk: <span class="font-bold text-blue-600">"{{ request('search') }}"</span>
            <a href="{{ route('blog.index') }}" class="ml-2 text-xs text-red-500 underline block mt-1 sm:inline sm:mt-0">Clear</a>
        </div>
    @endif

    <div class="grid gap-y-4 md:gap-y-6">
        @foreach($blogs as $blog)
            <article class="group relative bg-white w-full p-5 md:p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
                <div class="flex flex-col gap-3">
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($blog['meta']['tags'], 0, 3) as $tag)
                                <span class="capitalize bg-blue-50 text-blue-600 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>

                        @if(count($blog['meta']['tags']) > 3)
                            <span class="text-slate-400 text-xs font-medium">
                                +{{ count($blog['meta']['tags']) - 3 }}
                            </span>
                        @endif

                        <span class="hidden xs:inline text-slate-300">•</span>
                        <span class="text-slate-500 font-normal text-xs md:text-sm">{{ $blog['meta']['date'] ?? '' }}</span>
                    </div>

                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 group-hover:text-blue-600 transition leading-tight">
                        <a href="{{ route('blog.show', $blog['slug']) }}">
                            <span class="absolute inset-0"></span>
                            {{ $blog['meta']['title'] }}
                        </a>
                    </h2>

                    <span class="text-xs font-bold text-blue-500 uppercase">
                        {{ $blog['meta']['source'] }}
                    </span>

                    <p class="text-slate-600 line-clamp-2 text-sm md:text-base">
                        {{ $blog['meta']['description'] ?? 'Klik untuk membaca lebih lanjut...' }}
                    </p>

                    <div class="mt-2 md:mt-4 flex items-center gap-2 text-sm font-bold text-blue-600">
                        Baca Selengkapnya
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-10 mb-10 flex flex-col sm:flex-row gap-4 justify-between items-center border-t border-slate-100 pt-8">
        <div class="flex items-center gap-4 order-2 sm:order-1">
            @if($blogs->onFirstPage())
                <span class="text-slate-300 cursor-not-allowed text-sm font-bold">← Prev</span>
            @else
                <a href="{{ $blogs->previousPageUrl() }}" class="text-blue-600 hover:text-blue-700 text-sm font-bold">← Prev</a>
            @endif

            <span class="text-xs md:text-sm text-slate-500 font-medium">
                {{ $blogs->currentPage() }} / {{ $blogs->lastPage() }}
            </span>

            @if($blogs->hasMorePages())
                <a href="{{ $blogs->nextPageUrl() }}" class="text-blue-600 hover:text-blue-700 text-sm font-bold">Next →</a>
            @else
                <span class="text-slate-300 cursor-not-allowed text-sm font-bold">Next →</span>
            @endif
        </div>

        <p class="text-xs text-slate-400 order-1 sm:order-2">
            Showing {{ $blogs->count() }} articles
        </p>
    </div>
</div>
@endsection
