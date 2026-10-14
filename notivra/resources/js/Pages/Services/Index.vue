<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useLanguage } from '@/Composables/useLanguage';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';

const { locale } = useLanguage();
const tagline = locale === 'id' ? 'Tim kami siap membantu merealisasikan ide besarmu.' : 'Our team is ready to help realize your big ideas.';
defineProps({
    services: Array
});
</script>

<template>
    <AppLayout title="Services" margin="wide">
        <template #header>
            <div class="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 font-display">
                            {{ locale === 'id' ? 'Layanan Kami' : 'Our Services' }}
                        </h1>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ locale === 'id' ? 'Solusi teknologi terintegrasi untuk mempercepat riset dan bisnismu.' :
                                'Integrated technology solutions to accelerate your research and business.' }}
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto px-6">
            <div class="grid gap-6">
                <div v-for="service in services" :key="service.id"
                    class="group relative p-6 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all duration-200">
                    <div class="flex items-start space-x-5">
                        <div
                            class="shrink-0 text-3xl bg-slate-50 group-hover:bg-indigo-50 p-4 rounded-xl transition-colors duration-200">
                            {{ service.icon }}
                        </div>

                        <div class="flex-1">
                            <h2
                                class="text-xl font-bold text-slate-900 group-hover:text-indigo-600 transition-colors duration-200">
                                {{ service.title[locale] }}
                            </h2>
                            <p class="mt-2 text-slate-600 leading-relaxed line-clamp-2">
                                {{ service.description[locale] }}
                            </p>

                            <Link :href="route('services.show', service.id)"
                                class="inline-flex items-center mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                {{ locale === 'id' ? 'Lihat Detail' : 'View Details' }}
                                <ArrowRightIcon
                                    class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 text-center p-10 bg-indigo-700 rounded-3xl shadow-xl shadow-indigo-100">
                <h2 class="text-2xl font-bold text-white">
                    {{ locale === 'id' ? 'Butuh solusi kustom?' : 'Need a custom solution?' }}
                </h2>
                <p class="text-indigo-100 mt-2">
                    {{ tagline }}
                </p>
                <Link :href="route('hire.apply')"
                    class="inline-block mt-6 px-8 py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-indigo-50 transition-colors">
                    {{ locale === 'id' ? 'Hubungi Kami' : 'Get in Touch' }}
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
