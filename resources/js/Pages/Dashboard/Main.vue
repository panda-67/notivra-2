<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Hero from './Partials/Hero.vue';
import Content from './Partials/Content.vue';

const page = usePage();
const isAuth = computed(() => page.props.auth.user !== null);

const props = defineProps({
    drafts: { type: Array, default: () => [] },
    projects: { type: Array, required: true, default: () => [] }
});

</script>

<template>
    <AppLayout title="Wawasan Riset">
        <template #header>
            <div v-if="!isAuth" class="relative overflow-hidden bg-white py-12 lg:py-24 border-b border-slate-100">
                <Hero />
            </div>

            <div v-else>
                <div class="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
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
            <Content :projects="props.projects" :drafts="props.drafts" />
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
