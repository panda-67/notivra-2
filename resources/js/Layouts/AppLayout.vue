<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import Navbar from '../Components/Navbar.vue';
import { computed, ref, watch } from 'vue';
import Footer from '../Components/Footer.vue';

const props = defineProps({
    title: String,
});


const page = usePage();
const show = ref(false);
const isAuth = computed(() => page.props.auth.user !== null);

watch(() => page.props.flash, (msg) => {
    if (msg) {
        show.value = true;
        // Sembunyikan otomatis setelah 3 detik
        setTimeout(() => show.value = false, 4000);
    }
}, { immediate: true });
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC]">

        <Head :title="title ? title : ($page.props.title || 'Welcome')" />

        <Navbar />

        <header v-if="$slots.header" class="bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="py-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                    <slot />
                </div>
            </div>
        </main>

        <Footer v-if="!isAuth" />
        <footer v-else class="py-6 text-center text-slate-400 text-xs">
            &copy; 2026 Notivra. All rights reserved.
        </footer>
    </div>

    <div class="relative">
        <transition enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="show && (page.props.flash.success || page.props.flash.error)"
                class="fixed top-5 right-5 z-100 bg-white border border-slate-200 shadow-2xl rounded-2xl p-4 flex items-center gap-3 min-w-[320px]">

                <div :class="[
                    'w-10 h-10 rounded-full flex items-center justify-center shrink-0',
                    page.props.flash.error ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'
                ]">
                    <svg v-if="page.props.flash.error" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div>
                    <p class="text-sm font-bold text-slate-900">
                        {{ page.props.flash.error ? 'Terjadi Kesalahan' : 'Berhasil!' }}
                    </p>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ page.props.flash.error || page.props.flash.success }}
                    </p>
                </div>

                <button @click="show = false" class="ml-auto text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                    </svg>
                </button>
            </div>
        </transition>
    </div>
</template>
