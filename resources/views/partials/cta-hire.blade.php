<div class="not-prose my-10 overflow-hidden rounded-2xl border border-blue-100 bg-linear-to-r from-blue-50 to-indigo-50 shadow-sm">
     @php
        $locale = App::getLocale();
    @endphp
    <div class="p-6 md:p-8 flex flex-col md:flex-row items-center gap-6">
        <div class="flex-1 text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                </span>
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">
                    @if ($locale === 'id')
                        Dukungan Sistem Akademik
                    @else
                        Academic Support System
                    @endif
                </span>
            </div>
            <h4 class="text-xl md:text-2xl font-extrabold text-slate-800 mb-2">
                @if ($locale === 'id')
                    Kesulitan dengan riset atau tugas akademik Anda?
                @else
                    Having trouble with your research or academic task?
                @endif
            </h4>
            <p class="text-slate-600 leading-relaxed">
                @if ($locale === 'id')
                    Tim ahli Notivra siap mendampingi Anda memberikan solusi bimbingan dan dukungan akademik yang komprehensif.
                @else
                   Notivra experts ready to escourt and assisting you with best solution and comprehensive academic support.
                @endif
            </p>
        </div>
        <div class="shrink-0">
            <a href="/hire/apply" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 text-white px-7 py-3.5 rounded-xl font-bold hover:bg-blue-700 transition-all transform hover:scale-105 shadow-blue-200 shadow-lg">
                @if ($locale === 'id')
                    <span>Konsultasi Sekarang</span>
                @else
                   <span>Consultation Now</span>
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
</div>

