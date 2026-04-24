<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeTab = ref('projects');

const props = defineProps({
    projects: Array,
    drafts: Array
});

</script>

<template>
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

</template>
