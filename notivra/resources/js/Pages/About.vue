<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ShieldCheckIcon,
    BeakerIcon,
    DocumentChartBarIcon,
    ClipboardDocumentCheckIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const locale = computed(() => page.props.locale);

const steps = [
    {
        title: { id: 'Submit Masalah Riset', en: 'Submit Your Research Problem' },
        desc: {
            id: 'Bagikan pertanyaan riset, data, atau draf Anda untuk penilaian awal.',
            en: 'Share your research question, data, or draft for initial assessment.'
        }
    },
    {
        title: { id: 'Audit Analitis', en: 'Analytical Audit' },
        desc: {
            id: 'Kami mengevaluasi tujuan, struktur data, dan batasan metodologis Anda.',
            en: 'We evaluate your objectives, data structure, and methodological constraints.'
        }
    },
    {
        title: { id: 'Rencana Metodologis', en: 'Methodological Plan' },
        desc: {
            id: 'Kami mengusulkan strategi analitis yang jelas dan output yang diharapkan.',
            en: 'We propose a clear analytical strategy and expected outputs.'
        }
    },
    {
        title: { id: 'Eksekusi & Dokumentasi', en: 'Execution & Documentation' },
        desc: {
            id: 'Kami melakukan analisis dan mendokumentasikan setiap langkah demi transparansi.',
            en: 'We perform the analysis and document every step for transparency.'
        }
    },
    {
        title: { id: 'Hasil yang Reproducable', en: 'Reproducible Results' },
        desc: {
            id: 'Anda menerima hasil, kode, dan visualisasi yang siap untuk penggunaan akademik.',
            en: 'You receive results, code, and visuals ready for academic use.'
        }
    }
];

const values = [
    {
        icon: ShieldCheckIcon,
        title: { id: 'Kredibilitas', en: 'Credibility' },
        desc: { id: 'Hasil yang dapat dipertahankan di depan dewan penguji atau reviewer jurnal.', en: 'Results that stand up before exam boards or journal reviewers.' }
    },
    {
        icon: BeakerIcon,
        title: { id: 'Ketajaman Analisis', en: 'Analytical Sharpness' },
        desc: { id: 'Bukan sekadar olah data, tapi interpretasi mendalam berbasis sains.', en: 'Not just data processing, but deep science-based interpretation.' }
    },
    {
        icon: DocumentChartBarIcon,
        title: { id: 'Siap Publikasi', en: 'Publication Ready' },
        desc: { id: 'Visualisasi dan narasi yang memenuhi standar jurnal bereputasi.', en: 'Visuals and narratives that meet reputable journal standards.' }
    }
];

const tagline = locale === 'id'
    ? 'Notivra menawarkan dukungan penulisan, analisis data, dan bantuan publikasi yang dirancang khusus untuk mahasiswa pascasarjana, dosen, dan institusi akademik.'
    : 'Notivra offers writing, data analysis, and publication support tailored to postgraduate students, lecturers, and academic institutions.'

const result = locale === 'id'
    ? 'Dari taksonomi biologi hingga pemodelan statistik kesehatan kompleks, portofolio kami mencakup berbagai disiplin ilmu yang telah dipublikasikan di jurnal bereputasi.'
    : 'From biological taxonomy to complex health statistical modeling, our portfolio spans various disciplines published in reputable journals.'

</script>

<template>
    <AppLayout :title="locale === 'id' ? 'Tentang Kami' : 'About Us'" margin="wide">
        <template #header>
            <div class="py-20">
                <div class="max-w-7xl mx-auto px-6 text-center">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-6">
                        Clarity. Credibility. <span class="text-indigo-600">Delivered.</span>
                    </h1>
                    <p class="text-xl text-slate-600 leading-relaxed">
                        {{ tagline }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-20 bg-slate-50">
            <div class="max-w-4xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-slate-900">
                        {{ locale === 'id' ? 'Bagaimana Kami Bekerja' : 'How It Works' }}
                    </h2>
                    <p class="mt-4 text-slate-600 italic">
                        {{ locale === 'id'
                            ? 'Proses sistematis untuk memastikan integritas riset Anda.'
                            : 'A systematic process to ensure the integrity of your research.'
                        }}
                    </p>
                </div>

                <div class="relative">
                    <div class="hidden md:block absolute left-5 top-0 bottom-0 w-0.5 bg-indigo-100"></div>

                    <div class="space-y-12">
                        <div v-for="(step, index) in steps" :key="index" class="relative flex items-start group">
                            <div
                                class="shrink-0 w-10 h-10 bg-white border-2 border-indigo-600 text-indigo-600 rounded-full flex items-center justify-center font-bold z-10 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                                {{ index + 1 }}
                            </div>
                            <div class="ml-6">
                                <h3 class="text-xl font-bold text-slate-900">{{ step.title[locale] }}</h3>
                                <p class="mt-2 text-slate-600 leading-relaxed">{{ step.desc[locale] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-20 bg-white">
            <div class="max-w-5xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div v-for="value in values" :key="value.title.en" class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 mb-6">
                            <component :is="value.icon" class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">{{ value.title[locale] }}</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ value.desc[locale] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-20 bg-slate-900 text-white overflow-hidden relative">
            <div class="max-w-4xl mx-auto px-6 relative z-10">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="md:w-2/3">
                        <h2 class="text-3xl font-bold mb-6">
                            {{ locale === 'id' ? 'Hasil Riset yang Nyata' : 'Proven Research Outputs' }}
                        </h2>
                        <p class="text-slate-400 text-lg mb-8">
                            {{ result }}
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <span
                                class="px-4 py-2 bg-slate-800 rounded-lg text-sm border border-slate-700">Taxonomy</span>
                            <span class="px-4 py-2 bg-slate-800 rounded-lg text-sm border border-slate-500">
                                GLMM Statistics</span>
                            <span class="px-4 py-2 bg-slate-800 rounded-lg text-sm border border-slate-700">
                                CFA Analysis</span>
                            <Link :href="route('gallery.index')" class="inline-flex items-center px-3 py-1.5 bg-slate-800 border border-slate-400 text-white font-bold
                            rounded-xl hover:bg-slate-600 transition-all shadow-md shadow-slate-600">
                                {{ locale === 'id' ? 'Kunjungi Galeri' : 'Our Gallery' }}
                                <ArrowRightIcon class="w-4 h-4 ml-2" />
                            </Link>
                        </div>
                    </div>
                    <div class="mt-12 md:mt-0 md:w-1/3 flex justify-center">
                        <ClipboardDocumentCheckIcon class="w-32 h-32 text-indigo-500 opacity-20" />
                    </div>
                </div>
            </div>
        </div>

        <div class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-3xl font-extrabold text-slate-900 mb-6">
                    {{ locale === 'id' ? 'Siap Meningkatkan Kualitas Riset Anda?' : 'Ready to Elevate Your Research?' }}
                </h2>
                <Link :href="route('hire.apply')"
                    class="inline-flex items-center px-8 py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                    {{ locale === 'id' ? 'Mulai Konsultasi' : 'Start Consultation' }}
                    <ArrowRightIcon class="w-5 h-5 ml-2" />
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
