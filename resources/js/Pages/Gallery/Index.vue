<script setup lang="js">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLanguage } from '@/Composables/useLanguage';

const filter = ref('all')
const { locale } = useLanguage();

const props = defineProps({
    outputs: Array,
});

const sortedOutputs = [...props.outputs].sort(
    (a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()
)

const filteredOutputs = computed(() => {
    if (filter.value === 'all') return sortedOutputs
    return sortedOutputs.filter(item => item.type === filter.value)
})

const typeLabels = computed(() => ({
    all: locale.value === 'id' ? 'Semua' : 'All',
    paper: locale.value === 'id' ? 'Publikasi' : 'Papers',
    statistics: locale.value === 'id' ? 'Statistik' : 'Statistics',
    report: locale.value === 'id' ? 'Laporan' : 'Reports',
    dataset: locale.value === 'id' ? 'Dataset' : 'Datasets',
}))

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-GB', {
        month: 'long',
        year: 'numeric'
    })
}
</script>

<template>
    <AppLayout :title="locale === 'id' ? 'Galeri Riset' : 'Research Gallery'" margin="wide">
        <div class="relative isolate min-h-screen bg-slate-50 pt-16 pb-24 -mt-10">
            <div class="absolute inset-0 -z-10 bg-[url('/images/noise.webp')] opacity-20"></div>
            <div class="absolute inset-x-0 top-0 -z-10 h-96 bg-linear-to-b from-sky-100/50 to-transparent"></div>

            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center mb-16">
                    <h2 class="text-base font-semibold leading-7 text-indigo-600 uppercase tracking-widest">Gallery</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        {{ locale === 'id' ? 'Wawasan Riset Nusantara' : 'Nusantara Research Insight' }}
                    </p>
                    <div class="mt-4 flex justify-center">
                        <span class="h-1 w-12 rounded-full bg-indigo-600"></span>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center gap-2 mb-12">
                    <button v-for="(label, key) in typeLabels" :key="key" @click="filter = key" :class="[
                        'px-5 py-2 rounded-full text-sm font-medium transition-all duration-200 border',
                        filter === key
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-100'
                            : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300'
                    ]">
                        {{ label }}
                    </button>
                </div>

                <TransitionGroup tag="div" name="list"
                    class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="item in filteredOutputs" :key="item.id"
                        class="group relative flex flex-col items-start bg-white rounded-3xl p-3 border border-slate-200/60 shadow-sm hover:shadow-xl transition-all duration-300">
                        <div class="relative aspect-16/10 w-full overflow-hidden rounded-2xl bg-slate-100">
                            <img :src="item.figures[0] || '/images/placeholder.jpg'" :alt="item.title[locale]"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
                            <div class="absolute top-3 left-3">
                                <span
                                    class="inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider backdrop-blur-md bg-white/90 shadow-sm text-slate-700 border border-white/50">
                                    {{ item.type }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4 flex-1 flex flex-col w-full">
                            <div class="flex items-center gap-x-2 text-xs mb-3 text-slate-500 font-medium">
                                <span>{{ formatDate(item.date) }}</span>
                            </div>

                            <h3
                                class="text-lg font-bold leading-6 text-slate-900 group-hover:text-indigo-600 transition-colors duration-300">
                                <Link :href="route('gallery.show', item.slug)">
                                    <span class="absolute inset-0" />
                                    <span v-html="item.title[locale]"></span>
                                </Link>
                            </h3>

                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600 font-light">
                                <span v-html="item.description[locale]"></span>
                            </p>

                            <div class="mt-auto pt-6 flex flex-wrap gap-1.5">
                                <span v-for="(method, i) in item.methods.slice(0, 3)" :key="i"
                                    class="text-[10px] uppercase tracking-tighter text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-100">
                                    #{{ method }}
                                </span>
                            </div>
                        </div>
                    </article>
                </TransitionGroup>

                <div v-if="filteredOutputs.length === 0" class="py-24 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="text-slate-500 font-medium">Tidak ada hasil ditemukan.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(30px);
}
</style>
