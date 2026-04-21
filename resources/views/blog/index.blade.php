@extends('layouts.blog')

@section('title', 'Wawasan Akademik')

@section('content')
<div>
    <header class="mb-16">
        <h1 class="text-4xl font-black text-slate-900 mb-4">Wawasan Riset Nusantara</h1>
        <p class="text-lg text-slate-600">Panduan dan update terbaru untuk mendukung perjalanan akademik Anda.</p>
    </header>

    <div class="grid gap-y-4">
        @foreach($blogs as $blog)
            <article class="group relative bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 text-sm text-blue-600 font-medium">
                        @foreach($blog['meta']['tags'] as $tag)
                            <span class="capitalize bg-blue-50 text-blue-600 px-3 py-1 rounded-full">
                                {{ $tag }}
                            </span>
                        @endforeach
                        <span class="text-slate-300">•</span>
                        <span class="text-slate-500 font-normal">{{ $blog['meta']['date'] ?? '' }}</span>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-800 group-hover:text-blue-600 transition">
                        <a href="{{ route('blog.show', $blog['slug']) }}">
                            <span class="absolute inset-0"></span>
                            {{ $blog['meta']['title'] }}
                        </a>
                    </h2>

                    <p class="text-slate-600 line-clamp-2">
                        {{ $blog['meta']['description'] ?? 'Klik untuk membaca lebih lanjut...' }}
                    </p>

                    <div class="mt-4 flex items-center gap-2 text-sm font-bold text-blue-600">
                        Baca Selengkapnya
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
