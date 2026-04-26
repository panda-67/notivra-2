<script setup>
import { Link } from '@inertiajs/vue3';
import { useLanguage } from '@/Composables/useLanguage';
import DefaultLayout from '@/Layouts/AppLayout.vue';

const { locale } = useLanguage();
const props = defineProps({
    output: {
        type: Array,
        required: true
    }
})

const formatDate = (dateStr) => {
    if (!dateStr) return ''
    const options = { day: 'numeric', month: 'long', year: 'numeric' }
    return new Date(dateStr).toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-GB', options)
}
</script>

<template>
    <DefaultLayout :title="output ? output.title[locale] : 'Loading...'" margin="wide">
        <div class="relative isolate min-h-screen bg-white pb-24">
            <div class="absolute inset-0 -z-10 bg-[url('/images/noise.webp')] opacity-20"></div>
            <div class="absolute inset-x-0 top-0 -z-10 h-96 bg-linear-to-b from-sky-100/50 to-transparent"></div>

            <header class="pb-12 bg-slate-50 border-b border-slate-200">
                <div class="mx-auto max-w-4xl px-6 lg:px-8">
                    <Link :href="route('gallery.index')"
                        class="inline-flex items-center text-sm font-medium text-indigo-600 mb-8 hover:text-indigo-800 transition-colors">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        {{ locale === 'id' ? 'Kembali' : 'Back' }}
                    </Link>

                    <div class="flex items-center gap-x-3 mb-6">
                        <span
                            class="bg-indigo-100 px-3 py-1 text-[10px] font-bold text-indigo-700 rounded-full uppercase tracking-widest">
                            {{ output.type }}
                        </span>
                        <span class="text-slate-300">|</span>
                        <time class="text-sm text-slate-500 font-medium">{{ formatDate(output.date) }}</time>
                    </div>

                    <h1 class="text-3xl font-extrabold text-slate-900 leading-tight sm:text-4xl"
                        v-html="output.title[locale]"></h1>
                </div>
            </header>

            <div class="mx-auto max-w-4xl px-6 lg:px-8 mt-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">

                    <div class="lg:col-span-8">
                        <section>
                            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-4 italic">
                                {{ locale === 'id' ? 'Ringkasan Riset' : 'Research Abstract' }}
                            </h2>
                            <div class="text-lg leading-relaxed text-slate-700 italic font-light"
                                v-html="output.description[locale]"></div>
                        </section>

                        <section v-if="output.figures && output.figures.length" class="mt-16 space-y-10">
                            <div v-for="(fig, i) in output.figures" :key="i"
                                class="rounded-2xl border border-slate-200 overflow-hidden shadow-sm bg-slate-50">
                                <img :src="fig" :alt="'Visualization ' + (i + 1)" class="w-full h-auto object-cover" />
                                <div
                                    class="p-3 bg-white text-center text-[10px] text-slate-400 uppercase tracking-widest border-t border-slate-100">
                                    Data Visualization — {{ i + 1 }}
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="lg:col-span-4">
                        <div class="sticky top-8 space-y-10">
                            <div v-if="output.methods && output.methods.length">
                                <h3 class="text-sm font-bold mb-4 text-slate-900 border-b pb-2 tracking-tight">
                                    {{ locale === 'id' ? 'Metodologi' : 'Methods' }}
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="m in output.methods" :key="m"
                                        class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-md text-[11px] font-medium border border-slate-200">
                                        {{ m }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <a v-if="output.link" :href="output.link" target="_blank"
                                    class="flex items-center justify-center gap-2 bg-indigo-600 text-white py-3 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">
                                    <span>{{ locale === 'id' ? 'Buka Publikasi' : 'Open Publication' }}</span>
                                </a>

                                <a v-if="output.code" :href="output.code.url" target="_blank"
                                    class="flex items-center justify-center gap-2 border border-slate-300 text-slate-700 py-3 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all">
                                    <span>Source Code</span>
                                    <span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded uppercase">{{
                                        output.code.platform }}</span>
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <div v-if="!output" class="py-32 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-50 text-red-500 mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900">Data Tidak Ditemukan</h2>
                <p class="text-slate-500 mt-2">Slug riset tidak valid atau telah dihapus.</p>
                <Link :href="route('gallery.index')"
                    class="text-indigo-600 font-semibold mt-6 inline-block hover:underline">
                    &larr; Kembali ke Galeri
                </Link>
            </div>
        </div>
    </DefaultLayout>
</template>

<style scoped>
/* Styling khusus untuk tag ilmiah di dalam v-html */
:deep(i) {
    font-style: italic;
    color: #4f46e5;
    font-weight: 500;
}
</style>
