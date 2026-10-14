@if($active)
    <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-600 text-white text-xs font-bold shadow-md shadow-blue-100">
        {{ $page }}
    </span>
@else
    <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 text-xs font-medium transition">
        {{ $page }}
    </a>
@endif
