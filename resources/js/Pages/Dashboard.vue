<script setup lang="ts">
import AppLayout from '../Layouts/AppLayout.vue';

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="relative overflow-hidden bg-white py-12 lg:py-20">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="grid grid-cols-1 gap-y-16 lg:grid-cols-2 lg:gap-x-12 items-center">

                        <div>
                            <span
                                class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10 mb-6">
                                Versi 2.0 Sudah Rilis
                            </span>
                            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl">
                                Kelola Notivra SaaS <span class="text-indigo-600">Lebih Cepat.</span>
                            </h1>
                            <p class="mt-6 text-lg leading-8 text-slate-600">
                                Tingkatkan efisiensi operasional bisnis Anda dengan dashboard terintegrasi. Pantau
                                performa, kelola tim, dan skalakan sistem dalam satu langkah mudah.
                            </p>
                            <div class="mt-10 flex items-center gap-x-6">
                                <a href="#"
                                    class="rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
                                    Mulai Sekarang
                                </a>
                                <a href="#" class="text-sm font-semibold leading-6 text-slate-900 group">
                                    Pelajari Selengkapnya <span
                                        class="inline-block transition-transform group-hover:translate-x-1"
                                        aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="relative">
                    <div
                        class="aspect-[4/3] rounded-2xl bg-slate-100 object-cover flex items-center justify-center border border-slate-200 overflow-hidden">
                        <div class="text-center p-8">
                            <div class="flex justify-center gap-2 mb-4">
                                <div class="h-3 w-3 rounded-full bg-red-400"></div>
                                <div class="h-3 w-3 rounded-full bg-yellow-400"></div>
                                <div class="h-3 w-3 rounded-full bg-green-400"></div>
                            </div>
                            <p class="text-slate-400 font-mono text-sm">[ Dashboard Preview Notivra ]</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
