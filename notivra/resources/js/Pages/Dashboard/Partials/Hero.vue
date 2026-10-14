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
                <div class="aspect-16/10 flex flex-col items-center justify-center">
                    <div className="flex justify-center md:justify-end">
                        <img src="/images/illustration.webp" alt="Illustration"
                            className="max-w-md md:max-w-lg w-125 h-80 animate-float" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
