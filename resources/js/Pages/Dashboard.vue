<script setup>
import AppLayout from '../Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const isAuth = computed(() => page.props.auth.user !== null);
const activeTab = ref('projects'); // default ke projects

const props = defineProps({
    drafts: { type: Array, default: () => [] },
    projects: { type: Array, required: true, default: () => [] }
});

</script>

<template>
    <AppLayout title="Wawasan Riset">
        <template #header>
            <div v-if="!isAuth" class="relative overflow-hidden bg-white py-12 lg:py-24 border-b border-slate-100">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="grid grid-cols-1 gap-y-16 lg:grid-cols-2 lg:gap-x-16 items-center">
                        <div>
                            <span
                                class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 uppercase tracking-wider mb-6">
                                Wawasan Riset Nusantara
                            </span>
                            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl leading-tight">
                                Hubungkan Ide, <br />
                                <span class="text-indigo-600">Wujudkan Riset.</span>
                            </h1>
                            <p class="mt-6 text-lg leading-8 text-slate-600">
                                Notivra hadir sebagai jembatan kolaborasi riset di Indonesia. Temukan mitra ahli,
                                kelola draf penelitian, dan publikasikan wawasan akademik Anda dalam satu ekosistem
                                terpadu.
                            </p>
                            <div class="mt-10 flex items-center gap-x-6">
                                <Link :href="route('hire.apply')"
                                    class="rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                                    Mulai Meneliti
                                </Link>
                                <a href="/" class="text-sm font-semibold leading-6 text-slate-900 group">
                                    Eksplorasi Insight <span
                                        class="inline-block transition-transform group-hover:translate-x-1">→</span>
                                </a>
                            </div>
                        </div>
                        <div class="relative">
                            <div
                                class="aspect-16/10 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center shadow-inner">
                                <svg class="h-12 w-12 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <p class="text-slate-400 font-medium text-sm">Notivra Research Framework</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else>
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 font-display">Ruang Kerja Riset</h1>
                            <p class="text-sm text-slate-500 mt-1">Kelola progres kolaborasi dan draf publikasi Anda.
                            </p>
                        </div>
                        <Link :href="route('hire.apply')"
                            class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                            + Ajukan Kolaborasi Baru
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div v-if="isAuth" class="py-12 bg-slate-50/50 min-h-[60vh]">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="flex items-center justify-between border-b border-slate-200">
                    <div class="flex space-x-6">
                        <button @click="activeTab = 'projects'"
                            :class="[activeTab === 'projects' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300']"
                            class="pb-4 px-2 border-b-2 font-bold text-sm transition-all flex items-center gap-2">
                            Project Riset
                            <span
                                :class="activeTab === 'projects' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                                class="px-2 py-0.5 rounded-full text-[10px]">
                                {{ projects.length }}
                            </span>
                        </button>
                        <button @click="activeTab = 'drafts'"
                            :class="[activeTab === 'drafts' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300']"
                            class="pb-4 px-2 border-b-2 font-bold text-sm transition-all flex items-center gap-2">
                            Draf Kolaborasi
                            <span
                                :class="activeTab === 'drafts' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                                class="px-2 py-0.5 rounded-full text-[10px]">
                                {{ drafts.length }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200 overflow-hidden transition-all">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50/50">
                                <tr class="text-slate-500 border-b border-slate-100">
                                    <th class="px-6 py-4 font-semibold uppercase text-[11px] tracking-wider">Topik /
                                        Proyek</th>
                                    <th class="px-6 py-4 font-semibold uppercase text-[11px] tracking-wider">Status</th>
                                    <th class="px-6 py-4 font-semibold uppercase text-[11px] tracking-wider">
                                        {{ activeTab === 'projects' ? 'Tanggal Mulai' : 'Update Terakhir' }}
                                    </th>
                                    <th class="px-6 py-4 font-semibold uppercase text-[11px] tracking-wider text-right">
                                        Tindakan
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-50">
                                <template v-if="activeTab === 'projects'">
                                    <tr v-for="project in projects" :key="project.id"
                                        class="group hover:bg-indigo-50/30 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ project.topic }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex capitalize items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                {{ project.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 tabular-nums">{{ project.human_date }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <Link :href="route('projects.edit', project.id)"
                                                class="text-indigo-600 hover:text-indigo-800 font-bold text-xs">
                                                Edit →
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="projects.length === 0">
                                        <td colspan="4" class="py-20 text-center text-slate-400">Belum ada project
                                            berjalan.</td>
                                    </tr>
                                </template>

                                <template v-else>
                                    <tr v-for="draft in drafts" :key="draft.id"
                                        class="group hover:bg-amber-50/30 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ draft.data.topic }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex capitalize items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                                                {{ draft.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 tabular-nums">{{ draft.human_date }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div v-if="draft.status === 'draft'">
                                                <Link :href="route('hire.apply', { d: draft.id })"
                                                    class="text-indigo-600 hover:text-indigo-800 font-bold text-xs">
                                                    Lanjutkan →
                                                </Link>
                                            </div>
                                            <div v-else class="text-slate-500 font-bold text-xs">Sudah Final</div>
                                        </td>
                                    </tr>
                                    <tr v-if="drafts.length === 0">
                                        <td colspan="4" class="py-20 text-center text-slate-400">Tidak ada draf
                                            tersimpan.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="py-24 text-center">
            <div class="max-w-md mx-auto px-6">
                <h3 class="text-slate-900 font-bold text-xl">Siap berkontribusi bagi Nusantara?</h3>
                <p class="text-slate-500 text-sm mt-2">Masuk ke akun Anda untuk mengakses fitur kolaborasi riset dan
                    publikasi insight.</p>
                <Link :href="route('login')" as="button"
                    class="mt-6 bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold text-sm hover:cursor-pointer">
                    Masuk Sekarang</Link>
            </div>
        </div>
    </AppLayout>
</template>
