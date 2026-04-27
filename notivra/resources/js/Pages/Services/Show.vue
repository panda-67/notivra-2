<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useLanguage } from '@/Composables/useLanguage';
import { BriefcaseIcon, ChevronLeftIcon } from '@heroicons/vue/24/outline';

const { locale } = useLanguage();
const estimate = locale === 'id' ? 'Estimasi pengerjaan: 7-14 hari kerja' : 'Estimated delivery: 7 - 14 working days';

const props = defineProps({
    service: Object
});

</script>

<template>
    <AppLayout :title="service.title[locale]" margin="wide">
        <div class="py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <Link :href="route('services.index')"
                    class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors duration-200">
                    <ChevronLeftIcon class="w-4 h-4 mr-1" />
                    {{ locale === 'id' ? 'Kembali ke Layanan' : 'Back to Services' }}
                </Link>

                <div class="mt-8">
                    <div class="flex items-center space-x-4">
                        <div class="text-4xl sm:text-5xl bg-indigo-50 p-4 rounded-2xl">
                            {{ service.icon }}
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                                {{ service.title[locale] }}
                            </h1>
                            <div class="h-1 w-20 bg-indigo-600 mt-2 rounded-full"></div>
                        </div>
                    </div>

                    <div class="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-12">
                        <div class="lg:col-span-2">
                            <article class="prose prose-indigo max-w-none">
                                <p class="text-lg leading-relaxed text-slate-600">
                                    {{ service.description[locale] || 'No detailed information available.' }}
                                </p>
                                <div v-if="service.features" class="mt-10 border-t border-slate-100 pt-8">
                                    <h3 class="text-xl font-bold text-slate-900 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ locale === 'id' ? 'Fitur Layanan' : 'Service Features' }}
                                    </h3>

                                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="feature in service.features[locale]" :key="feature"
                                            class="flex items-center p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div
                                                class="shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span class="text-slate-700 font-medium text-sm sm:text-base">
                                                {{ feature }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div class="lg:col-span-1">
                            <div class="sticky top-8 bg-slate-50 border border-slate-200 rounded-2xl p-6 shadow-sm">
                                <h3 class="text-lg font-bold text-slate-900 mb-2">
                                    {{ locale === 'id' ? 'Tertarik dengan layanan ini?' : 'Ready to start?' }}
                                </h3>
                                <p class="text-sm text-slate-500 mb-6">
                                    {{ locale === 'id' ? 'Konsultasikan proyek Anda dengan tim ahli kami sekarang.' :
                                        'Discuss your project with our expert team today.' }}
                                </p>

                                <Link :href="route('hire.apply')"
                                    class="w-full inline-flex justify-center items-center px-6 py-3 border
                                    border-transparent text-base font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700
                                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-md shadow-indigo-200 dark:shadow-none">
                                    <BriefcaseIcon class="w-5 h-5 mr-2" />
                                    {{ locale === 'id' ? 'Mulai Proyek' : 'Hire Us' }}
                                </Link>

                                <p class="mt-4 text-center text-xs text-slate-400">
                                    {{ estimate }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
