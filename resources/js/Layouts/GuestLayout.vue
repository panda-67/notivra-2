<script setup>
import { Link, Head } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
});

</script>

<template>

    <Head :title="title || $page.props.title || 'Welcome'" />

    <div
        class="min-h-screen relative flex flex-col items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 overflow-x-hidden">

        <div class="absolute top-16 md:left-60 w-full mb-10 flex justify-center md:justify-start">
            <Link href="/" class="shrink-0 flex items-center gap-2 group">
                <div
                    class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-blue-200 shadow-lg group-hover:scale-105 transition-transform">
                    N
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-900 uppercase">Notivra</span>
            </Link>
        </div>

        <div class="w-full max-w-7xl">
            <slot />
        </div>

    </div>
</template>
