<script setup>
import { Link } from '@inertiajs/vue3';
import { useLanguage } from '@/Composables/useLanguage';

const { locale } = useLanguage();
</script>

<template>
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-y-16 lg:grid-cols-2 lg:gap-x-16 items-center">
            <div>
                <span
                    class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 uppercase tracking-wider mb-6">
                    {{ locale === 'id' ? ' Wawasan Riset Nusantara ' : 'Nusantara Research Insight' }}
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl leading-tight">
                    {{ locale === 'id' ? 'Hubungkan Ide' : 'Connecting Ideas' }}, <br />
                    <span class="text-indigo-600">{{ locale === 'id' ? 'Wujudkan Riset.' : 'Shaping Research.' }}</span>
                </h1>
                <p v-if="locale === 'id'" class="mt-6 text-lg leading-8 text-slate-600">
                    Notivra hadir sebagai jembatan kolaborasi riset di Indonesia. Temukan mitra ahli, kelola draf
                    penelitian, dan publikasikan wawasan akademik Anda dalam satu ekosistem terpadu.
                </p>
                <p v-else class="mt-6 text-lg leading-8 text-slate-600">
                    Notivra serves as a bridge for research collaboration in Indonesia. Find expert partners, manage
                    research drafts, and publish your academic insights within a single integrated ecosystem.
                </p>

                <div class="mt-10 flex items-center gap-x-6">
                    <Link :href="route('hire.apply')"
                        class="rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-all">
                        {{ locale === 'id' ? ' Mulai Meneliti ' : 'Start Research' }}
                    </Link>
                    <a href="/" class="text-sm font-semibold leading-6 text-slate-900 group">
                        {{ locale === 'id' ? ' Eksplorasi Insight ' : 'Insight Exploration' }}
                        <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
                    </a>
                </div>
            </div>
            <div class="relative">
                <div
                    class="aspect-16/10 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center shadow-inner">
                    <svg class="h-12 w-12 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <p class="text-slate-400 font-medium text-sm">Notivra Research Framework</p>
                </div>
            </div>
        </div>
    </div>
</template>
